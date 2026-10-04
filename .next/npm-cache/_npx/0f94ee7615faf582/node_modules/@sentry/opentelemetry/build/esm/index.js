import { s as setIsSetup, H as buildContextWithSentryScopes, I as SENTRY_SCOPES_CONTEXT_KEY, q as setOpenTelemetryContextAsyncContextStrategy, J as SENTRY_TRACE_STATE_CHILD_IGNORED } from './asyncContextStrategy-BQfM_LMv.js';
export { S as SEMANTIC_ATTRIBUTE_SENTRY_GRAPHQL_OPERATION, a as SentryPropagator, b as SentrySampler, c as SentrySpanProcessor, d as SentryTracerProvider, e as applyOtelSpanData, f as backfillStreamedSpanDataFromOtel, g as continueTrace, h as enhanceDscWithOpenTelemetryRootSpanName, i as getActiveSpan, j as getRequestSpanData, k as getScopesFromContext, l as getSentryResource, m as getSpanKind, n as getTraceContextForScope, o as isSentryRequestSpan, p as openTelemetrySetupCheck, r as setupEventContextTrace, t as spanHasAttributes, u as spanHasEvents, v as spanHasKind, w as spanHasName, x as spanHasParentId, y as spanHasStatus, z as startInactiveSpan, A as startSpan, B as startSpanManual, C as suppressTracing, D as withActiveSpan, E as wrapClientClass, F as wrapContextManagerClass, G as wrapSamplingDecision } from './asyncContextStrategy-BQfM_LMv.js';
import { getAsyncContextStrategy, getMainCarrier, spanIsIgnored, getRootSpan } from '@sentry/core';
export { getClient, getDynamicSamplingContextFromSpan, shouldPropagateTraceForUrl, withStreamedSpan } from '@sentry/core';
import * as api from '@opentelemetry/api';
import { ROOT_CONTEXT } from '@opentelemetry/api';
import { AsyncLocalStorage } from 'node:async_hooks';
import { EventEmitter } from 'node:events';

const ADD_LISTENER_METHODS = ["addListener", "on", "once", "prependListener", "prependOnceListener"];
class SentryAsyncLocalStorageContextManager {
  constructor() {
    this._kOtListeners = /* @__PURE__ */ Symbol("OtListeners");
    this._wrapped = false;
    setIsSetup("SentryContextManager");
    this._asyncLocalStorage = getAsyncContextStrategy(getMainCarrier()).getTracingChannelBinding?.()?.asyncLocalStorage ?? new AsyncLocalStorage();
  }
  active() {
    return this._asyncLocalStorage.getStore() ?? ROOT_CONTEXT;
  }
  with(context, fn, thisArg, ...args) {
    const ctx2 = buildContextWithSentryScopes(context, this.active());
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
    if (target instanceof EventEmitter) {
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
      contextSymbol: SENTRY_SCOPES_CONTEXT_KEY
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
  setOpenTelemetryContextAsyncContextStrategy({
    getTracingChannelBinding: !options?.skipOpenTelemetrySetup ? getDefaultAsyncLocalStorageFactory() : getCustomAsyncLocalStorageFactory()
  });
}
function getDefaultAsyncLocalStorageFactory() {
  const defaultAsyncLocalStorage = new AsyncLocalStorage();
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
  const isIgnoredChild = spanIsIgnored(span) && getRootSpan(span) !== span || span.spanContext().traceState?.get(SENTRY_TRACE_STATE_CHILD_IGNORED) === "1";
  return isIgnoredChild ? activeContext : api.trace.setSpan(activeContext, span);
}

export { SentryAsyncLocalStorageContextManager, setIsSetup, setNodeOpenTelemetryContextAsyncContextStrategy as setOpenTelemetryContextAsyncContextStrategy };
//# sourceMappingURL=index.js.map
