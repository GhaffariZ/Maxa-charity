import { defineIntegration } from '@sentry/core';
import { httpServerIntegration } from './httpServerIntegration.js';
import { httpServerSpansIntegration } from './httpServerSpansIntegration.js';
import { instrumentHttpOutgoingRequests } from './SentryHttpInstrumentation.js';

const INTEGRATION_NAME = "Http";
Object.assign(instrumentHttpOutgoingRequests, {
  id: `${INTEGRATION_NAME}.sentry`
});
const httpIntegration = defineIntegration((options = {}) => {
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
  const server = httpServerIntegration(serverOptions);
  const serverSpans = httpServerSpansIntegration(serverSpansOptions);
  return {
    name: INTEGRATION_NAME,
    setup(client) {
      if (enabledServerSpans) {
        serverSpans.setup(client);
      }
    },
    setupOnce() {
      server.setupOnce();
      instrumentHttpOutgoingRequests(httpInstrumentationOptions);
    },
    processEvent(event) {
      return serverSpans.processEvent(event);
    }
  };
});

export { httpIntegration };
//# sourceMappingURL=index.js.map
