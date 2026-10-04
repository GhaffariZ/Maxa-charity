import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { SDK_VERSION, startInactiveSpan, SPAN_KIND, getActiveSpan, withActiveSpan, SPAN_STATUS_ERROR } from '@sentry/core';
import { InstrumentationNodeModuleFile } from '../../InstrumentationNodeModuleFile.js';
import { SpanNames } from './enums/SpanNames.js';
import { shouldSkipInstrumentation, getSemanticAttributesFromConnection, patchClientConnectCallback, isObjectWithTextString, handleConfigQuery, patchCallback, getErrorMessage, getSemanticAttributesFromPoolConnection, patchCallbackPGPool } from './utils.js';

const PACKAGE_NAME = "@sentry/instrumentation-pg";
function extractModuleExports(module) {
  return module[Symbol.toStringTag] === "Module" ? module.default : module;
}
function bindCallbackToSpan(parentSpan, callback) {
  return function(...args) {
    return withActiveSpan(parentSpan, () => callback.apply(this, args));
  };
}
class PgInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    const SUPPORTED_PG_VERSIONS = [">=8.0.3 <9"];
    const SUPPORTED_PG_POOL_VERSIONS = [">=2.0.0 <4"];
    const modulePgNativeClient = new InstrumentationNodeModuleFile(
      "pg/lib/native/client.js",
      SUPPORTED_PG_VERSIONS,
      this._patchPgClient.bind(this),
      this._unpatchPgClient.bind(this)
    );
    const modulePgClient = new InstrumentationNodeModuleFile(
      "pg/lib/client.js",
      SUPPORTED_PG_VERSIONS,
      this._patchPgClient.bind(this),
      this._unpatchPgClient.bind(this)
    );
    const modulePG = new InstrumentationNodeModuleDefinition(
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
    const modulePGPool = new InstrumentationNodeModuleDefinition(
      "pg-pool",
      SUPPORTED_PG_POOL_VERSIONS,
      (module) => {
        const moduleExports = extractModuleExports(module);
        if (isWrapped(moduleExports.prototype.connect)) {
          this._unwrap(moduleExports.prototype, "connect");
        }
        this._wrap(moduleExports.prototype, "connect", this._getPoolConnectPatch());
        return moduleExports;
      },
      (module) => {
        const moduleExports = extractModuleExports(module);
        if (isWrapped(moduleExports.prototype.connect)) {
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
    if (isWrapped(moduleExports.prototype.query)) {
      this._unwrap(moduleExports.prototype, "query");
    }
    if (isWrapped(moduleExports.prototype.connect)) {
      this._unwrap(moduleExports.prototype, "connect");
    }
    this._wrap(moduleExports.prototype, "query", this._getClientQueryPatch());
    this._wrap(moduleExports.prototype, "connect", this._getClientConnectPatch());
    return module;
  }
  _unpatchPgClient(module) {
    const moduleExports = extractModuleExports(module);
    if (isWrapped(moduleExports.prototype.query)) {
      this._unwrap(moduleExports.prototype, "query");
    }
    if (isWrapped(moduleExports.prototype.connect)) {
      this._unwrap(moduleExports.prototype, "connect");
    }
    return module;
  }
  _getClientConnectPatch() {
    const plugin = this;
    return (original) => {
      return function connect(callback) {
        if (shouldSkipInstrumentation() || plugin.getConfig().ignoreConnectSpans) {
          return original.call(this, callback);
        }
        const span = startInactiveSpan({
          name: SpanNames.CONNECT,
          kind: SPAN_KIND.CLIENT,
          attributes: getSemanticAttributesFromConnection(this)
        });
        let cb = callback;
        if (cb) {
          const parentSpan = getActiveSpan();
          cb = patchClientConnectCallback(span, cb);
          if (parentSpan) {
            cb = bindCallbackToSpan(parentSpan, cb);
          }
        }
        const connectResult = withActiveSpan(span, () => {
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
        if (shouldSkipInstrumentation()) {
          return original.apply(this, args);
        }
        const arg0 = args[0];
        const firstArgIsString = typeof arg0 === "string";
        const firstArgIsQueryObjectWithText = isObjectWithTextString(arg0);
        const queryConfig = firstArgIsString ? {
          text: arg0,
          values: Array.isArray(args[1]) ? args[1] : void 0
        } : firstArgIsQueryObjectWithText ? {
          ...arg0,
          name: arg0.name,
          text: arg0.text,
          values: arg0.values ?? (Array.isArray(args[1]) ? args[1] : void 0)
        } : void 0;
        const span = handleConfigQuery.call(this, queryConfig);
        if (args.length > 0) {
          const parentSpan = getActiveSpan();
          if (typeof args[args.length - 1] === "function") {
            args[args.length - 1] = patchCallback(span, args[args.length - 1]);
            if (parentSpan) {
              args[args.length - 1] = bindCallbackToSpan(parentSpan, args[args.length - 1]);
            }
          } else if (typeof queryConfig?.callback === "function") {
            let callback = patchCallback(span, queryConfig.callback);
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
          span.setStatus({ code: SPAN_STATUS_ERROR, message: getErrorMessage(e) });
          span.end();
          throw e;
        }
        if (result instanceof Promise) {
          return result.then((result2) => {
            span.end();
            return result2;
          }).catch((error) => {
            span.setStatus({ code: SPAN_STATUS_ERROR, message: getErrorMessage(error) });
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
        if (shouldSkipInstrumentation() || plugin.getConfig().ignoreConnectSpans) {
          return originalConnect.call(this, callback);
        }
        const span = startInactiveSpan({
          name: SpanNames.POOL_CONNECT,
          kind: SPAN_KIND.CLIENT,
          attributes: getSemanticAttributesFromPoolConnection(this.options)
        });
        let cb = callback;
        if (cb) {
          const parentSpan = getActiveSpan();
          cb = patchCallbackPGPool(span, cb);
          if (parentSpan) {
            cb = bindCallbackToSpan(parentSpan, cb);
          }
        }
        const connectResult = withActiveSpan(span, () => {
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
    span.setStatus({ code: SPAN_STATUS_ERROR, message: getErrorMessage(error) });
    span.end();
    return Promise.reject(error);
  });
}

export { PgInstrumentation };
//# sourceMappingURL=instrumentation.js.map
