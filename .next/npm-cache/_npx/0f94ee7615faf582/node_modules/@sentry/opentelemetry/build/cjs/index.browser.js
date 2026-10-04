Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const asyncContextStrategy = require('./asyncContextStrategy-BR-l9zjD.js');

class SentryAsyncLocalStorageContextManager {
  constructor() {
    core.consoleSandbox(() => {
      console.error("SentryAsyncLocalStorageContextManager is not supported in the browser");
    });
  }
}

exports.getClient = core.getClient;
exports.getDynamicSamplingContextFromSpan = core.getDynamicSamplingContextFromSpan;
exports.shouldPropagateTraceForUrl = core.shouldPropagateTraceForUrl;
exports.withStreamedSpan = core.withStreamedSpan;
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
exports.setOpenTelemetryContextAsyncContextStrategy = asyncContextStrategy.setOpenTelemetryContextAsyncContextStrategy;
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
exports.SentryAsyncLocalStorageContextManager = SentryAsyncLocalStorageContextManager;
//# sourceMappingURL=index.browser.js.map
