import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { SDK_VERSION, getActiveSpan, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SPAN_STATUS_ERROR } from '@sentry/core';
import { DB_STATEMENT, NET_PEER_PORT, NET_PEER_NAME, DB_SYSTEM } from '@sentry/conventions/attributes';
import { defaultDbStatementSerializer } from '@sentry/server-utils';
import { DB_SYSTEM_VALUE_REDIS, ATTR_DB_CONNECTION_STRING } from './semconv.js';

const PACKAGE_NAME = "@sentry/instrumentation-ioredis";
const ORIGIN = "auto.db.otel.redis";
const SUPPORTED_VERSIONS = [">=2.0.0 <5.11.0"];
function endSpan(span, err) {
  if (err) {
    span.setStatus({ code: SPAN_STATUS_ERROR, message: err.message });
  }
  span.end();
}
class IORedisInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    return [
      new InstrumentationNodeModuleDefinition(
        "ioredis",
        SUPPORTED_VERSIONS,
        (module) => {
          const moduleExports = module[Symbol.toStringTag] === "Module" && module.default ? module.default : module;
          if (isWrapped(moduleExports.prototype.sendCommand)) {
            this._unwrap(moduleExports.prototype, "sendCommand");
          }
          this._wrap(moduleExports.prototype, "sendCommand", this._patchSendCommand());
          if (isWrapped(moduleExports.prototype.connect)) {
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
        if (args.length < 1 || typeof cmd !== "object" || !getActiveSpan()) {
          return original.apply(this, args);
        }
        const { host, port } = this.options;
        const attributes = {
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_SYSTEM]: DB_SYSTEM_VALUE_REDIS,
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_STATEMENT]: defaultDbStatementSerializer(cmd.name, cmd.args),
          [ATTR_DB_CONNECTION_STRING]: `redis://${host}:${port}`,
          // oxlint-disable-next-line typescript/no-deprecated
          [NET_PEER_NAME]: host,
          // oxlint-disable-next-line typescript/no-deprecated
          [NET_PEER_PORT]: port,
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        };
        const span = startInactiveSpan({ name: cmd.name, kind: SPAN_KIND.CLIENT, attributes });
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
        if (!getActiveSpan()) {
          return original.apply(this, args);
        }
        const { host, port } = this.options;
        const attributes = {
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_SYSTEM]: DB_SYSTEM_VALUE_REDIS,
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_STATEMENT]: "connect",
          [ATTR_DB_CONNECTION_STRING]: `redis://${host}:${port}`,
          // oxlint-disable-next-line typescript/no-deprecated
          [NET_PEER_NAME]: host,
          // oxlint-disable-next-line typescript/no-deprecated
          [NET_PEER_PORT]: port,
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        };
        const span = startInactiveSpan({ name: "connect", kind: SPAN_KIND.CLIENT, attributes });
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

export { IORedisInstrumentation };
//# sourceMappingURL=ioredis-instrumentation.js.map
