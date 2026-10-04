Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const InstrumentationNodeModuleFile = require('../../InstrumentationNodeModuleFile.js');
const SpanNames = require('./enums/SpanNames.js');
const utils = require('./utils.js');

const PACKAGE_NAME = "@sentry/instrumentation-pg";
function extractModuleExports(module) {
  return module[Symbol.toStringTag] === "Module" ? module.default : module;
}
function bindCallbackToSpan(parentSpan, callback) {
  return function(...args) {
    return core.withActiveSpan(parentSpan, () => callback.apply(this, args));
  };
}
class PgInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    const SUPPORTED_PG_VERSIONS = [">=8.0.3 <9"];
    const SUPPORTED_PG_POOL_VERSIONS = [">=2.0.0 <4"];
    const modulePgNativeClient = new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      "pg/lib/native/client.js",
      SUPPORTED_PG_VERSIONS,
      this._patchPgClient.bind(this),
      this._unpatchPgClient.bind(this)
    );
    const modulePgClient = new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      "pg/lib/client.js",
      SUPPORTED_PG_VERSIONS,
      this._patchPgClient.bind(this),
      this._unpatchPgClient.bind(this)
    );
    const modulePG = new instrumentation.InstrumentationNodeModuleDefinition(
      "pg",
      SUPPORTED_PG_VERSIONS,
      (module) => {
        const moduleExports = extractModuleExports(module);
        this._patchPgClient(moduleExports.Client);
        return module;
      },
      (module) => {
        const moduleExports = extractModuleExports(module);
        this._unpatchPgClient(moduleExports.Client);
        return module;
      },
      [modulePgClient, modulePgNativeClient]
    );
    const modulePGPool = new instrumentation.InstrumentationNodeModuleDefinition(
      "pg-pool",
      SUPPORTED_PG_POOL_VERSIONS,
      (module) => {
        const moduleExports = extractModuleExports(module);
        if (instrumentation.isWrapped(moduleExports.prototype.connect)) {
          this._unwrap(moduleExports.prototype, "connect");
        }
        this._wrap(moduleExports.prototype, "connect", this._getPoolConnectPatch());
        return moduleExports;
      },
      (module) => {
        const moduleExports = extractModuleExports(module);
        if (instrumentation.isWrapped(moduleExports.prototype.connect)) {
          this._unwrap(moduleExports.prototype, "connect");
        }
      }
    );
    return [modulePG, modulePGPool];
  }
  _patchPgClient(module) {
    if (!module) {
      return;
    }
    const moduleExports = extractModuleExports(module);
    if (instrumentation.isWrapped(moduleExports.prototype.query)) {
      this._unwrap(moduleExports.prototype, "query");
    }
    if (instrumentation.isWrapped(moduleExports.prototype.connect)) {
      this._unwrap(moduleExports.prototype, "connect");
    }
    this._wrap(moduleExports.prototype, "query", this._getClientQueryPatch());
    this._wrap(moduleExports.prototype, "connect", this._getClientConnectPatch());
    return module;
  }
  _unpatchPgClient(module) {
    const moduleExports = extractModuleExports(module);
    if (instrumentation.isWrapped(moduleExports.prototype.query)) {
      this._unwrap(moduleExports.prototype, "query");
    }
    if (instrumentation.isWrapped(moduleExports.prototype.connect)) {
      this._unwrap(moduleExports.prototype, "connect");
    }
    return module;
  }
  _getClientConnectPatch() {
    const plugin = this;
    return (original) => {
      return function connect(callback) {
        if (utils.shouldSkipInstrumentation() || plugin.getConfig().ignoreConnectSpans) {
          return original.call(this, callback);
        }
        const span = core.startInactiveSpan({
          name: SpanNames.SpanNames.CONNECT,
          kind: core.SPAN_KIND.CLIENT,
          attributes: utils.getSemanticAttributesFromConnection(this)
        });
        let cb = callback;
        if (cb) {
          const parentSpan = core.getActiveSpan();
          cb = utils.patchClientConnectCallback(span, cb);
          if (parentSpan) {
            cb = bindCallbackToSpan(parentSpan, cb);
          }
        }
        const connectResult = core.withActiveSpan(span, () => {
          return original.call(this, cb);
        });
        return handleConnectResult(span, connectResult);
      };
    };
  }
  _getClientQueryPatch() {
    return (original) => {
      this._diag.debug("Patching pg.Client.prototype.query");
      return function query(...args) {
        if (utils.shouldSkipInstrumentation()) {
          return original.apply(this, args);
        }
        const arg0 = args[0];
        const firstArgIsString = typeof arg0 === "string";
        const firstArgIsQueryObjectWithText = utils.isObjectWithTextString(arg0);
        const queryConfig = firstArgIsString ? {
          text: arg0,
          values: Array.isArray(args[1]) ? args[1] : void 0
        } : firstArgIsQueryObjectWithText ? {
          ...arg0,
          name: arg0.name,
          text: arg0.text,
          values: arg0.values ?? (Array.isArray(args[1]) ? args[1] : void 0)
        } : void 0;
        const span = utils.handleConfigQuery.call(this, queryConfig);
        if (args.length > 0) {
          const parentSpan = core.getActiveSpan();
          if (typeof args[args.length - 1] === "function") {
            args[args.length - 1] = utils.patchCallback(span, args[args.length - 1]);
            if (parentSpan) {
              args[args.length - 1] = bindCallbackToSpan(parentSpan, args[args.length - 1]);
            }
          } else if (typeof queryConfig?.callback === "function") {
            let callback = utils.patchCallback(span, queryConfig.callback);
            if (parentSpan) {
              callback = bindCallbackToSpan(parentSpan, callback);
            }
            args[0].callback = callback;
          }
        }
        let result;
        try {
          result = original.apply(this, args);
        } catch (e) {
          span.setStatus({ code: core.SPAN_STATUS_ERROR, message: utils.getErrorMessage(e) });
          span.end();
          throw e;
        }
        if (result instanceof Promise) {
          return result.then((result2) => {
            span.end();
            return result2;
          }).catch((error) => {
            span.setStatus({ code: core.SPAN_STATUS_ERROR, message: utils.getErrorMessage(error) });
            span.end();
            return Promise.reject(error);
          });
        }
        return result;
      };
    };
  }
  _getPoolConnectPatch() {
    const plugin = this;
    return (originalConnect) => {
      return function connect(callback) {
        if (utils.shouldSkipInstrumentation() || plugin.getConfig().ignoreConnectSpans) {
          return originalConnect.call(this, callback);
        }
        const span = core.startInactiveSpan({
          name: SpanNames.SpanNames.POOL_CONNECT,
          kind: core.SPAN_KIND.CLIENT,
          attributes: utils.getSemanticAttributesFromPoolConnection(this.options)
        });
        let cb = callback;
        if (cb) {
          const parentSpan = core.getActiveSpan();
          cb = utils.patchCallbackPGPool(span, cb);
          if (parentSpan) {
            cb = bindCallbackToSpan(parentSpan, cb);
          }
        }
        const connectResult = core.withActiveSpan(span, () => {
          return originalConnect.call(this, cb);
        });
        return handleConnectResult(span, connectResult);
      };
    };
  }
}
function handleConnectResult(span, connectResult) {
  if (!(connectResult instanceof Promise)) {
    return connectResult;
  }
  const connectResultPromise = connectResult;
  return connectResultPromise.then((result) => {
    span.end();
    return result;
  }).catch((error) => {
    span.setStatus({ code: core.SPAN_STATUS_ERROR, message: utils.getErrorMessage(error) });
    span.end();
    return Promise.reject(error);
  });
}

exports.PgInstrumentation = PgInstrumentation;
//# sourceMappingURL=instrumentation.js.map
