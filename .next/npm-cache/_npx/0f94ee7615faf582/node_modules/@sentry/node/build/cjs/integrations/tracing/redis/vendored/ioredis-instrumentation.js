Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const serverUtils = require('@sentry/server-utils');
const semconv = require('./semconv.js');

const PACKAGE_NAME = "@sentry/instrumentation-ioredis";
const ORIGIN = "auto.db.otel.redis";
const SUPPORTED_VERSIONS = [">=2.0.0 <5.11.0"];
function endSpan(span, err) {
  if (err) {
    span.setStatus({ code: core.SPAN_STATUS_ERROR, message: err.message });
  }
  span.end();
}
class IORedisInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    return [
      new instrumentation.InstrumentationNodeModuleDefinition(
        "ioredis",
        SUPPORTED_VERSIONS,
        (module) => {
          const moduleExports = module[Symbol.toStringTag] === "Module" && module.default ? module.default : module;
          if (instrumentation.isWrapped(moduleExports.prototype.sendCommand)) {
            this._unwrap(moduleExports.prototype, "sendCommand");
          }
          this._wrap(moduleExports.prototype, "sendCommand", this._patchSendCommand());
          if (instrumentation.isWrapped(moduleExports.prototype.connect)) {
            this._unwrap(moduleExports.prototype, "connect");
          }
          this._wrap(moduleExports.prototype, "connect", this._patchConnection());
          return module;
        },
        (module) => {
          if (module === void 0) return;
          const moduleExports = module[Symbol.toStringTag] === "Module" && module.default ? module.default : module;
          this._unwrap(moduleExports.prototype, "sendCommand");
          this._unwrap(moduleExports.prototype, "connect");
        }
      )
    ];
  }
  _patchSendCommand() {
    const instrumentation = this;
    return (original) => {
      return function(...args) {
        const cmd = args[0];
        if (args.length < 1 || typeof cmd !== "object" || !core.getActiveSpan()) {
          return original.apply(this, args);
        }
        const { host, port } = this.options;
        const attributes$1 = {
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_REDIS,
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_STATEMENT]: serverUtils.defaultDbStatementSerializer(cmd.name, cmd.args),
          [semconv.ATTR_DB_CONNECTION_STRING]: `redis://${host}:${port}`,
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.NET_PEER_NAME]: host,
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.NET_PEER_PORT]: port,
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        };
        const span = core.startInactiveSpan({ name: cmd.name, kind: core.SPAN_KIND.CLIENT, attributes: attributes$1 });
        try {
          const result = original.apply(this, args);
          const origResolve = cmd.resolve;
          cmd.resolve = function(response) {
            instrumentation._callResponseHook(span, cmd, response);
            endSpan(span, null);
            origResolve(response);
          };
          const origReject = cmd.reject;
          cmd.reject = function(err) {
            endSpan(span, err);
            origReject(err);
          };
          return result;
        } catch (error) {
          endSpan(span, error);
          throw error;
        }
      };
    };
  }
  _patchConnection() {
    return (original) => {
      return function(...args) {
        if (!core.getActiveSpan()) {
          return original.apply(this, args);
        }
        const { host, port } = this.options;
        const attributes$1 = {
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_REDIS,
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_STATEMENT]: "connect",
          [semconv.ATTR_DB_CONNECTION_STRING]: `redis://${host}:${port}`,
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.NET_PEER_NAME]: host,
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.NET_PEER_PORT]: port,
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        };
        const span = core.startInactiveSpan({ name: "connect", kind: core.SPAN_KIND.CLIENT, attributes: attributes$1 });
        try {
          const result = original.apply(this, args);
          if (result instanceof Promise) {
            return result.then(
              (value) => {
                endSpan(span, null);
                return value;
              },
              (error) => {
                endSpan(span, error);
                return Promise.reject(error);
              }
            );
          }
          endSpan(span, null);
          return result;
        } catch (error) {
          endSpan(span, error);
          throw error;
        }
      };
    };
  }
  _callResponseHook(span, cmd, response) {
    const { responseHook } = this.getConfig();
    if (!responseHook) {
      return;
    }
    try {
      responseHook(span, cmd.name, cmd.args, response);
    } catch {
    }
  }
}

exports.IORedisInstrumentation = IORedisInstrumentation;
//# sourceMappingURL=ioredis-instrumentation.js.map
