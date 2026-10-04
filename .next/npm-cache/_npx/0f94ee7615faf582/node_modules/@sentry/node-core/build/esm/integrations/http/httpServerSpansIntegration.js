import { errorMonitor } from 'node:events';
import { context } from '@opentelemetry/api';
import { RPCType, setRPCMetadata } from '@opentelemetry/core';
import { NET_TRANSPORT, HTTP_FLAVOR, HTTP_SCHEME, HTTP_USER_AGENT, HTTP_CLIENT_IP, NET_HOST_NAME, HTTP_HOST, HTTP_TARGET, HTTP_METHOD, HTTP_URL, URL_PATH, URL_FULL, SENTRY_HTTP_PREFETCH, NET_HOST_IP, NET_HOST_PORT, NET_PEER_IP, NET_PEER_PORT, HTTP_STATUS_CODE, HTTP_ROUTE, HTTP_RESPONSE_STATUS_CODE } from '@sentry/conventions/attributes';
import { debug, parseStringToURLObject, stripUrlQueryAndFragment, startInactiveSpan, SPAN_KIND, httpHeadersToSpanAttributes, filterCollectedUrl, isURLObjectRelative, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SEMANTIC_ATTRIBUTE_SENTRY_OP, withActiveSpan, bindScopeToEmitter, getSpanStatusFromHttpCode, SPAN_STATUS_ERROR, isTracingSuppressed, getIsolationScope } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { addStartSpanCallback } from './httpServerIntegration.js';

const INTEGRATION_NAME = "Http.ServerSpans";
const _httpServerSpansIntegration = ((options = {}) => {
  const ignoreStaticAssets = options.ignoreStaticAssets ?? true;
  const ignoreIncomingRequests = options.ignoreIncomingRequests;
  const ignoreStatusCodes = options.ignoreStatusCodes ?? [
    [401, 404],
    // 300 and 304 are possibly valid status codes we do not want to filter
    [301, 303],
    [305, 399]
  ];
  const { onSpanCreated } = options;
  const { requestHook, responseHook, applyCustomAttributesOnSpan } = options.instrumentation ?? {};
  return {
    name: INTEGRATION_NAME,
    setup(client) {
      if (typeof __SENTRY_TRACING__ !== "undefined" && !__SENTRY_TRACING__) {
        return;
      }
      client.on("httpServerRequest", (_request, _response, normalizedRequest) => {
        const request = _request;
        const response = _response;
        const startSpan = (next) => {
          if (shouldIgnoreSpansForIncomingRequest(request, {
            ignoreStaticAssets,
            ignoreIncomingRequests
          })) {
            DEBUG_BUILD && debug.log(INTEGRATION_NAME, "Skipping span creation for incoming request", request.url);
            return next();
          }
          const fullUrl = normalizedRequest.url || request.url || "/";
          const urlObj = parseStringToURLObject(fullUrl);
          const headers = request.headers;
          const userAgent = headers["user-agent"];
          const ips = headers["x-forwarded-for"];
          const httpVersion = request.httpVersion;
          const host = headers.host;
          const hostname = host?.replace(/^(.*)(:[0-9]{1,5})/, "$1") || "localhost";
          const scheme = fullUrl.startsWith("https") ? "https" : "http";
          const method = normalizedRequest.method || request.method?.toUpperCase() || "GET";
          const httpTargetWithoutQueryFragment = urlObj ? urlObj.pathname : stripUrlQueryAndFragment(fullUrl);
          const bestEffortTransactionName = `${method} ${httpTargetWithoutQueryFragment}`;
          const span = startInactiveSpan({
            name: bestEffortTransactionName,
            kind: SPAN_KIND.SERVER,
            attributes: {
              // Sentry specific attributes
              [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "http.server",
              [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.http.otel.http",
              [SENTRY_HTTP_PREFETCH]: isKnownPrefetchRequest(request) || void 0,
              [URL_FULL]: urlObj && !isURLObjectRelative(urlObj) ? filterCollectedUrl(urlObj.href, client) : void 0,
              [URL_PATH]: urlObj?.pathname ?? httpTargetWithoutQueryFragment,
              // Old Semantic Conventions attributes - added for compatibility with what `@opentelemetry/instrumentation-http` output before
              /* eslint-disable typescript/no-deprecated */
              [HTTP_URL]: filterCollectedUrl(fullUrl, client),
              [HTTP_METHOD]: normalizedRequest.method,
              [HTTP_TARGET]: filterCollectedUrl(
                urlObj ? `${urlObj.pathname}${urlObj.search}` : httpTargetWithoutQueryFragment,
                client
              ),
              [HTTP_HOST]: host,
              [NET_HOST_NAME]: hostname,
              [HTTP_CLIENT_IP]: typeof ips === "string" ? ips.split(",")[0] : void 0,
              [HTTP_USER_AGENT]: userAgent,
              [HTTP_SCHEME]: scheme,
              [HTTP_FLAVOR]: httpVersion,
              [NET_TRANSPORT]: httpVersion?.toUpperCase() === "QUIC" ? "ip_udp" : "ip_tcp",
              /* eslint-enable typescript/no-deprecated */
              ...getRequestContentLengthAttribute(request),
              ...httpHeadersToSpanAttributes(normalizedRequest.headers || {}, client.getDataCollectionOptions())
            }
          });
          requestHook?.(span, request);
          responseHook?.(span, response);
          applyCustomAttributesOnSpan?.(span, request, response);
          onSpanCreated?.(span, request, response);
          const rpcMetadata = { type: RPCType.HTTP, span };
          return withActiveSpan(span, () => {
            return context.with(setRPCMetadata(context.active(), rpcMetadata), () => {
              bindScopeToEmitter(request);
              bindScopeToEmitter(response);
              let isEnded = false;
              function endSpan(status) {
                if (isEnded) {
                  return;
                }
                isEnded = true;
                const newAttributes = getIncomingRequestAttributesOnResponse(request, response, rpcMetadata);
                span.setAttributes(newAttributes);
                span.setStatus(status);
                span.end();
                const route = newAttributes["http.route"];
                if (route) {
                  getIsolationScope().setTransactionName(`${request.method?.toUpperCase() || "GET"} ${route}`);
                }
              }
              response.on("close", () => {
                endSpan(getSpanStatusFromHttpCode(response.statusCode));
              });
              response.on(errorMonitor, () => {
                const httpStatus = getSpanStatusFromHttpCode(response.statusCode);
                endSpan(httpStatus.code === SPAN_STATUS_ERROR ? httpStatus : { code: SPAN_STATUS_ERROR });
              });
              return next();
            });
          });
        };
        addStartSpanCallback(request, startSpan);
      });
    },
    processEvent(event) {
      if (event.type === "transaction") {
        const statusCode = event.contexts?.trace?.data?.["http.response.status_code"];
        if (typeof statusCode === "number") {
          if (shouldFilterStatusCode(statusCode, ignoreStatusCodes)) {
            DEBUG_BUILD && debug.log("Dropping transaction due to status code", statusCode);
            return null;
          }
          event.contexts = {
            ...event.contexts,
            response: {
              ...event.contexts?.response,
              status_code: statusCode
            }
          };
        }
      }
      return event;
    },
    afterAllSetup(client) {
      if (!DEBUG_BUILD) {
        return;
      }
      if (client.getIntegrationByName("Http")) {
        debug.warn(
          "It seems that you have manually added `httpServerSpansIntegration` while `httpIntegration` is also present. Make sure to remove `httpIntegration` when adding `httpServerSpansIntegration`."
        );
      }
      if (!client.getIntegrationByName("Http.Server")) {
        debug.error(
          "It seems that you have manually added `httpServerSpansIntegration` without adding `httpServerIntegration`. This is a requiement for spans to be created - please add the `httpServerIntegration` integration."
        );
      }
    }
  };
});
const httpServerSpansIntegration = _httpServerSpansIntegration;
function isKnownPrefetchRequest(req) {
  return req.headers["next-router-prefetch"] === "1";
}
function isStaticAssetRequest(urlPath) {
  const path = stripUrlQueryAndFragment(urlPath);
  if (path.match(/\.(ico|png|jpg|jpeg|gif|svg|css|js|woff|woff2|ttf|eot|webp|avif)$/)) {
    return true;
  }
  if (path.match(/^\/(robots\.txt|sitemap\.xml|manifest\.json|browserconfig\.xml)$/)) {
    return true;
  }
  return false;
}
function shouldIgnoreSpansForIncomingRequest(request, {
  ignoreStaticAssets,
  ignoreIncomingRequests
}) {
  if (isTracingSuppressed()) {
    return true;
  }
  const urlPath = request.url;
  const method = request.method?.toUpperCase();
  if (method === "OPTIONS" || method === "HEAD" || !urlPath) {
    return true;
  }
  if (ignoreStaticAssets && method === "GET" && isStaticAssetRequest(urlPath)) {
    return true;
  }
  if (ignoreIncomingRequests?.(urlPath, request)) {
    return true;
  }
  return false;
}
function getRequestContentLengthAttribute(request) {
  const length = getContentLength(request.headers);
  if (length == null) {
    return {};
  }
  if (isCompressed(request.headers)) {
    return {
      ["http.request_content_length"]: length
    };
  } else {
    return {
      ["http.request_content_length_uncompressed"]: length
    };
  }
}
function getContentLength(headers) {
  const contentLengthHeader = headers["content-length"];
  if (contentLengthHeader === void 0) return null;
  const contentLength = parseInt(contentLengthHeader, 10);
  if (isNaN(contentLength)) return null;
  return contentLength;
}
function isCompressed(headers) {
  const encoding = headers["content-encoding"];
  return !!encoding && encoding !== "identity";
}
function getIncomingRequestAttributesOnResponse(request, response, rpcMetadata) {
  const { socket } = request;
  const { statusCode, statusMessage } = response;
  const newAttributes = {
    [HTTP_RESPONSE_STATUS_CODE]: statusCode,
    // eslint-disable-next-line typescript/no-deprecated
    [HTTP_STATUS_CODE]: statusCode,
    "http.status_text": statusMessage?.toUpperCase()
  };
  if (socket) {
    const { localAddress, localPort, remoteAddress, remotePort } = socket;
    newAttributes[NET_HOST_IP] = localAddress;
    newAttributes[NET_HOST_PORT] = localPort;
    newAttributes[NET_PEER_IP] = remoteAddress;
    newAttributes[NET_PEER_PORT] = remotePort;
  }
  newAttributes[HTTP_STATUS_CODE] = statusCode;
  newAttributes["http.status_text"] = (statusMessage || "").toUpperCase();
  if (rpcMetadata?.type === RPCType.HTTP && rpcMetadata.route !== void 0) {
    const routeName = rpcMetadata.route;
    newAttributes[HTTP_ROUTE] = routeName;
  }
  return newAttributes;
}
function shouldFilterStatusCode(statusCode, dropForStatusCodes) {
  return dropForStatusCodes.some((code) => {
    if (typeof code === "number") {
      return code === statusCode;
    }
    const [min, max] = code;
    return statusCode >= min && statusCode <= max;
  });
}

export { httpServerSpansIntegration, isStaticAssetRequest };
//# sourceMappingURL=httpServerSpansIntegration.js.map
