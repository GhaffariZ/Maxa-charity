import { consoleSandbox } from '@sentry/core';
export { getClient, getDynamicSamplingContextFromSpan, shouldPropagateTraceForUrl, withStreamedSpan } from '@sentry/core';
export { S as SEMANTIC_ATTRIBUTE_SENTRY_GRAPHQL_OPERATION, a as SentryPropagator, b as SentrySampler, c as SentrySpanProcessor, d as SentryTracerProvider, e as applyOtelSpanData, f as backfillStreamedSpanDataFromOtel, g as continueTrace, h as enhanceDscWithOpenTelemetryRootSpanName, i as getActiveSpan, j as getRequestSpanData, k as getScopesFromContext, l as getSentryResource, m as getSpanKind, n as getTraceContextForScope, o as isSentryRequestSpan, p as openTelemetrySetupCheck, s as setIsSetup, q as setOpenTelemetryContextAsyncContextStrategy, r as setupEventContextTrace, t as spanHasAttributes, u as spanHasEvents, v as spanHasKind, w as spanHasName, x as spanHasParentId, y as spanHasStatus, z as startInactiveSpan, A as startSpan, B as startSpanManual, C as suppressTracing, D as withActiveSpan, E as wrapClientClass, F as wrapContextManagerClass, G as wrapSamplingDecision } from './asyncContextStrategy-BQfM_LMv.js';

class SentryAsyncLocalStorageContextManager {
  constructor() {
    consoleSandbox(() => {
      console.error("SentryAsyncLocalStorageContextManager is not supported in the browser");
    });
  }
}

export { SentryAsyncLocalStorageContextManager };
//# sourceMappingURL=index.browser.js.map
