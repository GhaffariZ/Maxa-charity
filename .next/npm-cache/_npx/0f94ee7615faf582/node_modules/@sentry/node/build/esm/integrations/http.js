import { defineIntegration, hasSpansEnabled, getRequestUrlFromClientRequest, stripDataUrlContent } from '@sentry/core';
import { instrumentHttpOutgoingRequests, httpServerIntegration, httpServerSpansIntegration } from '@sentry/node-core';
import { URL_FULL } from '@sentry/conventions/attributes';

const INTEGRATION_NAME = "Http";
const instrumentSentryHttp = Object.assign(instrumentHttpOutgoingRequests, {
  id: `${INTEGRATION_NAME}.sentry`
});
const httpIntegration = defineIntegration((options = {}) => {
  const spans = options.spans ?? true;
  const disableIncomingRequestSpans = options.disableIncomingRequestSpans;
  const enableServerSpans = spans && !disableIncomingRequestSpans;
  const serverOptions = {
    sessions: options.trackIncomingRequestsAsSessions,
    sessionFlushingDelayMS: options.sessionFlushingDelayMS,
    ignoreRequestBody: options.ignoreIncomingRequestBody,
    maxRequestBodySize: options.maxIncomingRequestBodySize
  };
  const serverSpansOptions = {
    ignoreIncomingRequests: options.ignoreIncomingRequests,
    ignoreStaticAssets: options.ignoreStaticAssets,
    ignoreStatusCodes: options.dropSpansForIncomingRequestStatusCodes,
    instrumentation: options.instrumentation,
    onSpanCreated: options.incomingRequestSpanHook
  };
  const server = httpServerIntegration(serverOptions);
  const serverSpans = httpServerSpansIntegration(serverSpansOptions);
  return {
    name: INTEGRATION_NAME,
    setup(client) {
      const clientOptions = client.getOptions();
      if (enableServerSpans && hasSpansEnabled(clientOptions)) {
        serverSpans.setup(client);
      }
    },
    setupOnce() {
      server.setupOnce();
      const sentryHttpInstrumentationOptions = {
        breadcrumbs: options.breadcrumbs,
        spans,
        propagateTraceInOutgoingRequests: options.tracePropagation ?? true,
        createSpansForOutgoingRequests: spans,
        ignoreOutgoingRequests: options.ignoreOutgoingRequests,
        outgoingRequestHook: (span, request) => {
          const url = getRequestUrlFromClientRequest(request);
          if (url.startsWith("data:")) {
            const sanitizedUrl = stripDataUrlContent(url);
            span.setAttribute("http.url", sanitizedUrl);
            span.setAttribute(URL_FULL, sanitizedUrl);
            span.updateName(`${request.method || "GET"} ${sanitizedUrl}`);
          }
          options.instrumentation?.requestHook?.(span, request);
        },
        outgoingResponseHook: options.instrumentation?.responseHook,
        outgoingRequestApplyCustomAttributes: options.instrumentation?.applyCustomAttributesOnSpan
      };
      instrumentHttpOutgoingRequests(sentryHttpInstrumentationOptions);
    },
    processEvent(event) {
      return serverSpans.processEvent(event);
    }
  };
});

export { httpIntegration, instrumentSentryHttp };
//# sourceMappingURL=http.js.map
