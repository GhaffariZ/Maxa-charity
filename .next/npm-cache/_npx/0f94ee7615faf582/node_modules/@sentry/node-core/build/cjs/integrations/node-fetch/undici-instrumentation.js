Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagch = require('diagnostics_channel');
const url = require('url');
const core = require('@sentry/core');
const outgoingFetchRequest = require('../../utils/outgoingFetchRequest.js');
const attributes = require('@sentry/conventions/attributes');
const debugBuild = require('../../debug-build.js');

const ATTR_HTTP_REQUEST_METHOD_ORIGINAL = "http.request.method_original";
const _channelSubs = [];
const spanFromReq = /* @__PURE__ */ new WeakMap();
const ignoreRequestMap = /* @__PURE__ */ new WeakMap();
const propagationDecisionMap = new core.LRUMap(100);
function instrumentUndici(config = {}) {
  if (_channelSubs.length) {
    return;
  }
  subscribeToChannel("undici:request:create", (message) => onRequestCreated(config, message));
  subscribeToChannel(
    "undici:client:sendHeaders",
    (message) => onRequestHeaders(config, message)
  );
  subscribeToChannel("undici:request:headers", (message) => onResponseHeaders(config, message));
  subscribeToChannel("undici:request:trailers", (message) => onDone(message));
  subscribeToChannel("undici:request:error", (message) => onError(message));
}
function safeExecute(fn, onError2) {
  try {
    return fn();
  } catch (error) {
    onError2(error);
    return void 0;
  }
}
function subscribeToChannel(diagnosticChannel, onMessage) {
  const [major = 0, minor = 0] = process.version.replace("v", "").split(".").map((n) => Number(n));
  const useNewSubscribe = major > 18 || major === 18 && minor >= 19;
  if (useNewSubscribe) {
    _channelSubs.push(diagch.subscribe?.(diagnosticChannel, onMessage));
  } else {
    _channelSubs.push(diagch.channel(diagnosticChannel).subscribe(onMessage));
  }
}
function parseRequestHeaders(request) {
  const result = /* @__PURE__ */ new Map();
  if (Array.isArray(request.headers)) {
    for (let i = 0; i < request.headers.length; i += 2) {
      const key = request.headers[i];
      const value = request.headers[i + 1];
      if (typeof key === "string" && value !== void 0) {
        result.set(key.toLowerCase(), value);
      }
    }
  } else if (typeof request.headers === "string") {
    const headers = request.headers.split("\r\n");
    for (const line of headers) {
      if (!line) {
        continue;
      }
      const colonIndex = line.indexOf(":");
      if (colonIndex === -1) {
        continue;
      }
      const key = line.substring(0, colonIndex).toLowerCase();
      const value = line.substring(colonIndex + 1).trim();
      const allValues = result.get(key);
      if (allValues && Array.isArray(allValues)) {
        allValues.push(value);
      } else if (allValues) {
        result.set(key, [allValues, value]);
      } else {
        result.set(key, value);
      }
    }
  }
  return result;
}
function onRequestCreated(config, { request }) {
  const url$1 = getAbsoluteUrl(request.origin, request.path);
  const ignoredByCallback = safeExecute(
    () => !!config.ignoreOutgoingRequests?.(url$1),
    (e) => e && debugBuild.DEBUG_BUILD && core.debug.error("caught ignoreOutgoingRequests error: ", e)
  );
  const ignoreForBreadcrumbs = core.isTracingSuppressed() || !!ignoredByCallback;
  ignoreRequestMap.set(request, ignoreForBreadcrumbs);
  if (!config.spans) {
    if (config.tracePropagation !== false && !ignoreForBreadcrumbs) {
      outgoingFetchRequest.addTracePropagationHeadersToFetchRequest(request, propagationDecisionMap);
    }
    return;
  }
  if (request.method === "CONNECT" || ignoredByCallback) {
    return;
  }
  let requestUrl;
  try {
    requestUrl = new url.URL(request.path, request.origin);
  } catch (err) {
    debugBuild.DEBUG_BUILD && core.debug.warn("could not determine url.full:", err);
    return;
  }
  const urlScheme = requestUrl.protocol.replace(":", "");
  const requestMethod = getRequestMethod(request.method);
  const attributes$1 = {
    [attributes.HTTP_REQUEST_METHOD]: requestMethod,
    [ATTR_HTTP_REQUEST_METHOD_ORIGINAL]: request.method,
    [attributes.URL_FULL]: core.filterCollectedUrl(requestUrl.toString()),
    [attributes.URL_PATH]: requestUrl.pathname,
    [attributes.URL_QUERY]: core.filterCollectedUrlQuery(requestUrl.search),
    [attributes.URL_SCHEME]: urlScheme,
    [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.http.otel.node_fetch"
  };
  if (url$1.startsWith("data:")) {
    const sanitizedUrl = core.stripDataUrlContent(url$1);
    attributes$1["http.url"] = sanitizedUrl;
    attributes$1[attributes.URL_FULL] = sanitizedUrl;
    attributes$1[core.SEMANTIC_ATTRIBUTE_SENTRY_CUSTOM_SPAN_NAME] = `${request.method || "GET"} ${sanitizedUrl}`;
  }
  const schemePorts = { https: "443", http: "80" };
  const serverAddress = requestUrl.hostname;
  const serverPort = requestUrl.port || schemePorts[urlScheme];
  attributes$1[attributes.SERVER_ADDRESS] = serverAddress;
  if (serverPort && !isNaN(Number(serverPort))) {
    attributes$1[attributes.SERVER_PORT] = Number(serverPort);
  }
  const headersMap = parseRequestHeaders(request);
  const userAgentValues = headersMap.get("user-agent");
  if (userAgentValues) {
    const userAgent = Array.isArray(userAgentValues) ? userAgentValues[userAgentValues.length - 1] : userAgentValues;
    attributes$1[attributes.USER_AGENT_ORIGINAL] = userAgent;
  }
  const client = core.getClient();
  const span = core.startInactiveSpan({
    name: requestMethod === "_OTHER" ? "HTTP" : requestMethod,
    kind: core.SPAN_KIND.CLIENT,
    attributes: attributes$1,
    onlyIfParent: !client || !core.hasSpanStreamingEnabled(client)
  });
  safeExecute(
    () => config.requestHook?.(span, request),
    (e) => e && debugBuild.DEBUG_BUILD && core.debug.error("caught requestHook error: ", e)
  );
  outgoingFetchRequest.addTracePropagationHeadersToFetchRequest(request, propagationDecisionMap, span);
  spanFromReq.set(request, span);
}
function onRequestHeaders(config, { request, socket }) {
  const span = spanFromReq.get(request);
  if (!span) {
    return;
  }
  const { remoteAddress, remotePort } = socket;
  const spanAttributes = {
    [attributes.NETWORK_PEER_ADDRESS]: remoteAddress,
    [attributes.NETWORK_PEER_PORT]: remotePort
  };
  if (config.headersToSpanAttributes?.requestHeaders) {
    const headersToAttribs = new Set(config.headersToSpanAttributes.requestHeaders.map((n) => n.toLowerCase()));
    const headersMap = parseRequestHeaders(request);
    for (const [name, value] of headersMap.entries()) {
      if (headersToAttribs.has(name)) {
        const attrValue = Array.isArray(value) ? value : [value];
        spanAttributes[`http.request.header.${name}`] = attrValue;
      }
    }
  }
  span.setAttributes(spanAttributes);
}
function onResponseHeaders(config, { request, response }) {
  const breadcrumbsEnabled = config.breadcrumbs !== false;
  if (breadcrumbsEnabled && !ignoreRequestMap.get(request)) {
    outgoingFetchRequest.addFetchRequestBreadcrumb(request, response);
  }
  const span = spanFromReq.get(request);
  if (!span) {
    return;
  }
  const spanAttributes = {
    [attributes.HTTP_RESPONSE_STATUS_CODE]: response.statusCode
  };
  safeExecute(
    () => config.responseHook?.(span, { request, response }),
    (e) => e && debugBuild.DEBUG_BUILD && core.debug.error("caught responseHook error: ", e)
  );
  if (config.headersToSpanAttributes?.responseHeaders) {
    const headersToAttribs = /* @__PURE__ */ new Set();
    config.headersToSpanAttributes?.responseHeaders.forEach((name) => headersToAttribs.add(name.toLowerCase()));
    for (let idx = 0; idx < response.headers.length; idx = idx + 2) {
      const nameBuf = response.headers[idx];
      const valueBuf = response.headers[idx + 1];
      if (nameBuf === void 0 || valueBuf === void 0) {
        continue;
      }
      const name = nameBuf.toString().toLowerCase();
      const value = valueBuf;
      if (headersToAttribs.has(name)) {
        const attrName = `http.response.header.${name}`;
        if (!Object.prototype.hasOwnProperty.call(spanAttributes, attrName)) {
          spanAttributes[attrName] = [value.toString()];
        } else {
          spanAttributes[attrName].push(value.toString());
        }
      }
    }
  }
  span.setAttributes(spanAttributes);
  if (response.statusCode >= 400) {
    span.setStatus(core.getSpanStatusFromHttpCode(response.statusCode));
  }
}
function onDone({ request }) {
  const span = spanFromReq.get(request);
  if (!span) {
    return;
  }
  span.end();
  spanFromReq.delete(request);
}
function onError({ request, error }) {
  const span = spanFromReq.get(request);
  if (!span) {
    return;
  }
  span.setStatus({
    code: core.SPAN_STATUS_ERROR,
    message: error.message
  });
  span.end();
  spanFromReq.delete(request);
}
function getRequestMethod(original) {
  const knownMethods = {
    CONNECT: true,
    OPTIONS: true,
    HEAD: true,
    GET: true,
    POST: true,
    PUT: true,
    PATCH: true,
    DELETE: true,
    TRACE: true,
    // QUERY from https://datatracker.ietf.org/doc/draft-ietf-httpbis-safe-method-w-body/
    QUERY: true
  };
  if (original.toUpperCase() in knownMethods) {
    return original.toUpperCase();
  }
  return "_OTHER";
}
function getAbsoluteUrl(origin, path = "/") {
  const url = `${origin}`;
  if (url.endsWith("/") && path.startsWith("/")) {
    return `${url}${path.slice(1)}`;
  }
  if (!url.endsWith("/") && !path.startsWith("/")) {
    return `${url}/${path}`;
  }
  return `${url}${path}`;
}

exports.instrumentUndici = instrumentUndici;
//# sourceMappingURL=undici-instrumentation.js.map
