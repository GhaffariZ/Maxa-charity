Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const httpServerIntegration = require('./httpServerIntegration.js');
const httpServerSpansIntegration = require('./httpServerSpansIntegration.js');
const SentryHttpInstrumentation = require('./SentryHttpInstrumentation.js');

const INTEGRATION_NAME = "Http";
Object.assign(SentryHttpInstrumentation.instrumentHttpOutgoingRequests, {
  id: `${INTEGRATION_NAME}.sentry`
});
const httpIntegration = core.defineIntegration((options = {}) => {
  const spans = options.spans ?? false;
  const disableIncomingRequestSpans = options.disableIncomingRequestSpans ?? false;
  const enabledServerSpans = spans && !disableIncomingRequestSpans;
  const serverOptions = {
    sessions: options.trackIncomingRequestsAsSessions,
    sessionFlushingDelayMS: options.sessionFlushingDelayMS,
    ignoreRequestBody: options.ignoreIncomingRequestBody,
    maxRequestBodySize: options.maxIncomingRequestBodySize
  };
  const serverSpansOptions = {
    ignoreIncomingRequests: options.ignoreIncomingRequests,
    ignoreStaticAssets: options.ignoreStaticAssets,
    ignoreStatusCodes: options.dropSpansForIncomingRequestStatusCodes
  };
  const httpInstrumentationOptions = {
    breadcrumbs: options.breadcrumbs,
    propagateTraceInOutgoingRequests: options.tracePropagation ?? true,
    ignoreOutgoingRequests: options.ignoreOutgoingRequests,
    spans
  };
  const server = httpServerIntegration.httpServerIntegration(serverOptions);
  const serverSpans = httpServerSpansIntegration.httpServerSpansIntegration(serverSpansOptions);
  return {
    name: INTEGRATION_NAME,
    setup(client) {
      if (enabledServerSpans) {
        serverSpans.setup(client);
      }
    },
    setupOnce() {
      server.setupOnce();
      SentryHttpInstrumentation.instrumentHttpOutgoingRequests(httpInstrumentationOptions);
    },
    processEvent(event) {
      return serverSpans.processEvent(event);
    }
  };
});

exports.httpIntegration = httpIntegration;
//# sourceMappingURL=index.js.map
