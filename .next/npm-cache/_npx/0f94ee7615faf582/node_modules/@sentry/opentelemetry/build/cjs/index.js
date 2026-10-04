Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const asyncContextStrategy = require('./asyncContextStrategy-BR-l9zjD.js');
const core = require('@sentry/core');
const api = require('@opentelemetry/api');
const node_async_hooks = require('node:async_hooks');
const node_events = require('node:events');

const ADD_LISTENER_METHODS = ["addListener", "on", "once", "prependListener", "prependOnceListener"];
class SentryAsyncLocalStorageContextManager {
  constructor() {
    this._kOtListeners = /* @__PURE__ */ Symbol("OtListeners");
    this._wrapped = false;
    asyncContextStrategy.setIsSetup("SentryContextManager");
    this._asyncLocalStorage = core.getAsyncContextStrategy(core.getMainCarrier()).getTracingChannelBinding?.()?.asyncLocalStorage ?? new node_async_hooks.AsyncLocalStorage();
  }
  active() {
    return this._asyncLocalStorage.getStore() ?? api.ROOT_CONTEXT;
  }
  with(context, fn, thisArg, ...args) {
    const ctx2 = asyncContextStrategy.buildContextWithSentryScopes(context, this.active());
    const cb = thisArg == null ? fn : fn.bind(thisArg);
    return this._asyncLocalStorage.run(ctx2, cb, ...args);
  }
  enable() {
    return this;
  }
  disable() {
    this._asyncLocalStorage.disable();
    return this;
  }
  bind(context, target) {
    if (target instanceof node_events.EventEmitter) {
      return this._bindEventEmitter(context, target);
    }
    if (typeof target === "function") {
      return this._bindFunction(context, target);
    }
    return target;
  }
  /**
   * Gets underlying AsyncLocalStorage and symbol to allow lookup of scope.
   * This is Sentry-specific.
   */
  getAsyncLocalStorageLookup() {
    return {
      asyncLocalStorage: this._asyncLocalStorage,
      contextSymbol: asyncContextStrategy.SENTRY_SCOPES_CONTEXT_KEY
    };
  }
  _bindFunction(context, target) {
    const managerWith = this.with.bind(this);
    const contextWrapper = function(...args) {
      return managerWith(context, () => target.apply(this, args));
    };
    Object.defineProperty(contextWrapper, "length", {
      enumerable: false,
      configurable: true,
      writable: false,
      value: target.length
    });
    return contextWrapper;
  }
  _bindEventEmitter(context, ee) {
    if (this._getPatchMap(ee) !== void 0) {
      return ee;
    }
    this._createPatchMap(ee);
    for (const methodName of ADD_LISTENER_METHODS) {
      if (ee[methodName] === void 0) continue;
      ee[methodName] = this._patchAddListener(
        ee,
        ee[methodName],
        context
      );
    }
    if (typeof ee.removeListener === "function") {
      ee.removeListener = this._patchRemoveListener(ee, ee.removeListener);
    }
    if (typeof ee.off === "function") {
      ee.off = this._patchRemoveListener(ee, ee.off);
    }
    if (typeof ee.removeAllListeners === "function") {
      ee.removeAllListeners = this._patchRemoveAllListeners(
        ee,
        // oxlint-disable-next-line @typescript-eslint/unbound-method
        ee.removeAllListeners
      );
    }
    return ee;
  }
  _patchRemoveListener(ee, original) {
    const contextManager = this;
    return function(event, listener) {
      const events = contextManager._getPatchMap(ee)?.[event];
      if (events === void 0) {
        return original.call(this, event, listener);
      }
      const patchedListener = events.get(listener);
      return original.call(this, event, patchedListener || listener);
    };
  }
  _patchRemoveAllListeners(ee, original) {
    const contextManager = this;
    return function(event) {
      const map = contextManager._getPatchMap(ee);
      if (map !== void 0) {
        if (arguments.length === 0) {
          contextManager._createPatchMap(ee);
        } else if (event !== void 0 && map[event] !== void 0) {
          delete map[event];
        }
      }
      return original.apply(this, arguments);
    };
  }
  _patchAddListener(ee, original, context) {
    const contextManager = this;
    return function(event, listener) {
      if (contextManager._wrapped) {
        return original.call(this, event, listener);
      }
      let map = contextManager._getPatchMap(ee);
      if (map === void 0) {
        map = contextManager._createPatchMap(ee);
      }
      let listeners = map[event];
      if (listeners === void 0) {
        listeners = /* @__PURE__ */ new WeakMap();
        map[event] = listeners;
      }
      const patchedListener = contextManager.bind(context, listener);
      listeners.set(listener, patchedListener);
      contextManager._wrapped = true;
      try {
        return original.call(this, event, patchedListener);
      } finally {
        contextManager._wrapped = false;
      }
    };
  }
  _createPatchMap(ee) {
    const map = /* @__PURE__ */ Object.create(null);
    ee[this._kOtListeners] = map;
    return map;
  }
  _getPatchMap(ee) {
    return ee[this._kOtListeners];
  }
}

function setNodeOpenTelemetryContextAsyncContextStrategy(options) {
  asyncContextStrategy.setOpenTelemetryContextAsyncContextStrategy({
    getTracingChannelBinding: !options?.skipOpenTelemetrySetup ? getDefaultAsyncLocalStorageFactory() : getCustomAsyncLocalStorageFactory()
  });
}
function getDefaultAsyncLocalStorageFactory() {
  const defaultAsyncLocalStorage = new node_async_hooks.AsyncLocalStorage();
  return () => {
    return {
      asyncLocalStorage: defaultAsyncLocalStorage,
      getStoreWithActiveSpan
    };
  };
}
function getCustomAsyncLocalStorageFactory() {
  return () => {
    try {
      const contextManager = api.context._getContextManager();
      const asyncLocalStorage = contextManager?.getAsyncLocalStorageLookup().asyncLocalStorage;
      return asyncLocalStorage ? {
        asyncLocalStorage,
        getStoreWithActiveSpan
      } : void 0;
    } catch {
      return void 0;
    }
  };
}
function getStoreWithActiveSpan(span) {
  const activeContext = api.context.active();
  const isIgnoredChild = core.spanIsIgnored(span) && core.getRootSpan(span) !== span || span.spanContext().traceState?.get(asyncContextStrategy.SENTRY_TRACE_STATE_CHILD_IGNORED) === "1";
  return isIgnoredChild ? activeContext : api.trace.setSpan(activeContext, span);
}

exports.SEMANTIC_ATTRIBUTE_SENTRY_GRAPHQL_OPERATION = asyncContextStrategy.SEMANTIC_ATTRIBUTE_SENTRY_GRAPHQL_OPERATION;
exports.SentryPropagator = asyncContextStrategy.SentryPropagator;
exports.SentrySampler = asyncContextStrategy.SentrySampler;
exports.SentrySpanProcessor = asyncContextStrategy.SentrySpanProcessor;
exports.SentryTracerProvider = asyncContextStrategy.SentryTracerProvider;
exports.applyOtelSpanData = asyncContextStrategy.applyOtelSpanData;
exports.backfillStreamedSpanDataFromOtel = asyncContextStrategy.backfillStreamedSpanDataFromOtel;
exports.continueTrace = asyncContextStrategy.continueTrace;
exports.enhanceDscWithOpenTelemetryRootSpanName = asyncContextStrategy.enhanceDscWithOpenTelemetryRootSpanName;
exports.getActiveSpan = asyncContextStrategy.getActiveSpan;
exports.getRequestSpanData = asyncContextStrategy.getRequestSpanData;
exports.getScopesFromContext = asyncContextStrategy.getScopesFromContext;
exports.getSentryResource = asyncContextStrategy.getSentryResource;
exports.getSpanKind = asyncContextStrategy.getSpanKind;
exports.getTraceContextForScope = asyncContextStrategy.getTraceContextForScope;
exports.isSentryRequestSpan = asyncContextStrategy.isSentryRequestSpan;
exports.openTelemetrySetupCheck = asyncContextStrategy.openTelemetrySetupCheck;
exports.setIsSetup = asyncContextStrategy.setIsSetup;
exports.setupEventContextTrace = asyncContextStrategy.setupEventContextTrace;
exports.spanHasAttributes = asyncContextStrategy.spanHasAttributes;
exports.spanHasEvents = asyncContextStrategy.spanHasEvents;
exports.spanHasKind = asyncContextStrategy.spanHasKind;
exports.spanHasName = asyncContextStrategy.spanHasName;
exports.spanHasParentId = asyncContextStrategy.spanHasParentId;
exports.spanHasStatus = asyncContextStrategy.spanHasStatus;
exports.startInactiveSpan = asyncContextStrategy.startInactiveSpan;
exports.startSpan = asyncContextStrategy.startSpan;
exports.startSpanManual = asyncContextStrategy.startSpanManual;
exports.suppressTracing = asyncContextStrategy.suppressTracing;
exports.withActiveSpan = asyncContextStrategy.withActiveSpan;
exports.wrapClientClass = asyncContextStrategy.wrapClientClass;
exports.wrapContextManagerClass = asyncContextStrategy.wrapContextManagerClass;
exports.wrapSamplingDecision = asyncContextStrategy.wrapSamplingDecision;
exports.getClient = core.getClient;
exports.getDynamicSamplingContextFromSpan = core.getDynamicSamplingContextFromSpan;
exports.shouldPropagateTraceForUrl = core.shouldPropagateTraceForUrl;
exports.withStreamedSpan = core.withStreamedSpan;
exports.SentryAsyncLocalStorageContextManager = SentryAsyncLocalStorageContextManager;
exports.setOpenTelemetryContextAsyncContextStrategy = setNodeOpenTelemetryContextAsyncContextStrategy;
//# sourceMappingURL=index.js.map
