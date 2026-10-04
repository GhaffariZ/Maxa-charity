import { HTTP_ROUTE } from '@sentry/conventions/attributes';
import { debug, getActiveSpan, getIsolationScope, getDefaultIsolationScope, startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_OP, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, getRootSpan, spanToJSON, stringMatchesSomePattern } from '@sentry/core';
import { DEBUG_BUILD } from '../../../debug-build.js';
import { CHANNELS } from '../../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../../tracing-channel.js';
import { setLayerRegisteredPath, getLayerPath, getLayerRegisteredPath, pushLayerPath, getConstructedRoute, getActualMatchedRoute, popLayerPath } from './route.js';

const ORIGIN = "auto.http.express";
const ATTR_EXPRESS_NAME = "express.name";
const ATTR_EXPRESS_TYPE = "express.type";
const NOOP = () => {
};
let _isInstrumented = false;
function instrumentExpress(options, tracingChannel) {
  if (_isInstrumented) {
    return;
  }
  _isInstrumented = true;
  for (const channelName of [CHANNELS.EXPRESS_REGISTER, CHANNELS.ROUTER_REGISTER]) {
    tracingChannel(channelName).subscribe({
      start: NOOP,
      asyncStart: NOOP,
      asyncEnd: NOOP,
      error: NOOP,
      end: captureRegisteredLayerPath
    });
  }
  for (const channelName of [CHANNELS.EXPRESS_HANDLE, CHANNELS.ROUTER_HANDLE]) {
    DEBUG_BUILD && debug.log(`[orchestrion:express] subscribing to channel "${channelName}"`);
    const channel = tracingChannel(channelName);
    bindTracingChannelToSpan(channel, (data) => getSpanForLayer(data, options), {
      beforeSpanEnd(_span, data) {
        data._sentryCleanup?.();
      }
    });
    channel.subscribe({
      start: NOOP,
      asyncEnd: NOOP,
      end: NOOP,
      error: NOOP,
      asyncStart: popLayerPathForLayer
    });
  }
}
function captureRegisteredLayerPath(data) {
  const stack = data.self?.stack;
  if (!Array.isArray(stack)) {
    return;
  }
  const layer = stack[stack.length - 1];
  if (layer) {
    setLayerRegisteredPath(layer, getLayerPath(data.arguments ?? []));
  }
}
function popLayerPathForLayer(data) {
  if (!data._sentryStoredLayer) {
    return;
  }
  data._sentryStoredLayer = false;
  const req = data.arguments?.[0];
  if (req) {
    popLayerPath(req);
  }
}
function getSpanForLayer(data, options) {
  const layer = data.self;
  const args = data.arguments;
  if (!layer || !Array.isArray(args)) {
    return void 0;
  }
  if (layer.handle?.length === 4) {
    return void 0;
  }
  if (layer.method && !layer.route) {
    return void 0;
  }
  const req = args[0];
  const res = args[1];
  if (!req) {
    return void 0;
  }
  if (!getActiveSpan()) {
    return void 0;
  }
  const type = getLayerType(layer);
  const registeredPath = getLayerRegisteredPath(layer);
  if (registeredPath != null) {
    pushLayerPath(req, registeredPath);
    data._sentryStoredLayer = true;
  }
  const constructedRoute = type === "request_handler" ? getConstructedRoute(req) : void 0;
  const matchedRoute = type === "request_handler" && constructedRoute != null ? getActualMatchedRoute(req, constructedRoute) : void 0;
  const name = type === "request_handler" ? constructedRoute || "request handler" : type === "router" ? layer.path ?? "/" : layer.name ?? "<anonymous>";
  if (matchedRoute) {
    setHttpServerSpanRoute(matchedRoute);
  }
  if (type === "request_handler" && constructedRoute) {
    const isolationScope = getIsolationScope();
    if (isolationScope !== getDefaultIsolationScope()) {
      const method = typeof req.method === "string" ? req.method.toUpperCase() : "GET";
      isolationScope.setTransactionName(`${method} ${constructedRoute}`);
    } else {
      DEBUG_BUILD && debug.warn(
        "[orchestrion:express] Isolation scope is still default isolation scope - skipping transaction name"
      );
    }
  }
  if (isLayerIgnored(name, type, options)) {
    return void 0;
  }
  const span = startInactiveSpan({
    name,
    attributes: {
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
      [SEMANTIC_ATTRIBUTE_SENTRY_OP]: `${type}.express`,
      [ATTR_EXPRESS_NAME]: name,
      [ATTR_EXPRESS_TYPE]: type,
      ...matchedRoute ? { [HTTP_ROUTE]: matchedRoute } : {}
    }
  });
  if (res && typeof res.once === "function") {
    const onFinish = () => {
      span.end();
    };
    res.once("finish", onFinish);
    data._sentryCleanup = () => res.removeListener("finish", onFinish);
  }
  return span;
}
function getLayerType(layer) {
  if (layer.name === "router") {
    return "router";
  }
  if (layer.name === "bound dispatch" || layer.name === "handle") {
    return "request_handler";
  }
  return "middleware";
}
function setHttpServerSpanRoute(route) {
  const activeSpan = getActiveSpan();
  const rootSpan = activeSpan && getRootSpan(activeSpan);
  if (!rootSpan) {
    return;
  }
  if (spanToJSON(rootSpan).data[SEMANTIC_ATTRIBUTE_SENTRY_OP] !== "http.server") {
    return;
  }
  rootSpan.setAttribute(HTTP_ROUTE, route);
}
function isLayerIgnored(name, type, options) {
  const { ignoreLayers, ignoreLayersType } = options;
  if (Array.isArray(ignoreLayersType) && ignoreLayersType.includes(type)) {
    return true;
  }
  if (!Array.isArray(ignoreLayers)) {
    return false;
  }
  try {
    return stringMatchesSomePattern(name, ignoreLayers, true);
  } catch {
    return false;
  }
}

export { instrumentExpress };
//# sourceMappingURL=instrumentation.js.map
