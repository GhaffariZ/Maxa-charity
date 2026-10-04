import { AsyncLocalStorage } from 'node:async_hooks';
import { setAsyncContextStrategy, _INTERNAL_createTracingChannelBinding, isContinuingTrace, _INTERNAL_safeMathRandom, generateTraceId, SUPPRESS_TRACING_KEY, getDefaultIsolationScope, getDefaultCurrentScope } from '@sentry/core';

function setAsyncLocalStorageAsyncContextStrategy() {
  const asyncStorage = new AsyncLocalStorage();
  function getScopes() {
    const scopes = asyncStorage.getStore();
    if (scopes) {
      return scopes;
    }
    return {
      scope: getDefaultCurrentScope(),
      isolationScope: getDefaultIsolationScope()
    };
  }
  function withScope(callback) {
    const scope = getScopes().scope.clone();
    const isolationScope = getScopes().isolationScope;
    return asyncStorage.run({ scope, isolationScope }, () => {
      return callback(scope);
    });
  }
  function withSetScope(scope, callback) {
    const isolationScope = getScopes().isolationScope.clone();
    return asyncStorage.run({ scope, isolationScope }, () => {
      return callback(scope);
    });
  }
  function withIsolationScope(callback) {
    const scope = getScopes().scope.clone();
    const isolationScope = getScopes().isolationScope.clone();
    if (!isContinuingTrace(scope.getPropagationContext())) {
      scope.setPropagationContext({
        traceId: generateTraceId(),
        sampleRand: _INTERNAL_safeMathRandom()
      });
    }
    return asyncStorage.run({ scope, isolationScope }, () => {
      return callback(isolationScope);
    });
  }
  function withSetIsolationScope(isolationScope, callback) {
    const scope = getScopes().scope.clone();
    return asyncStorage.run({ scope, isolationScope }, () => {
      return callback(isolationScope);
    });
  }
  function suppressTracing(callback) {
    return withScope((scope) => {
      scope.setSDKProcessingMetadata({ [SUPPRESS_TRACING_KEY]: true });
      return callback();
    });
  }
  setAsyncContextStrategy({
    suppressTracing,
    withScope,
    withSetScope,
    withIsolationScope,
    withSetIsolationScope,
    getCurrentScope: () => getScopes().scope,
    getIsolationScope: () => getScopes().isolationScope,
    getTracingChannelBinding: () => _INTERNAL_createTracingChannelBinding(asyncStorage, getScopes)
  });
}

export { setAsyncLocalStorageAsyncContextStrategy };
//# sourceMappingURL=asyncLocalStorageStrategy.js.map
