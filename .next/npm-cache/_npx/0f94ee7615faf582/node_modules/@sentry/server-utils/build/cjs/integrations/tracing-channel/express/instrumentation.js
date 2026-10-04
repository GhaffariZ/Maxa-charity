Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const debugBuild = require('../../../debug-build.js');
const channels = require('../../../orchestrion/channels.js');
const tracingChannel = require('../../../tracing-channel.js');
const route = require('./route.js');

const ORIGIN = "auto.http.express";
const ATTR_EXPRESS_NAME = "express.name";
const ATTR_EXPRESS_TYPE = "express.type";
const NOOP = () => {
};
let _isInstrumented = false;
function instrumentExpress(options, tracingChannel$1) {
  if (_isInstrumented) {
    return;
  }
  _isInstrumented = true;
  for (const channelName of [channels.CHANNELS.EXPRESS_REGISTER, channels.CHANNELS.ROUTER_REGISTER]) {
    tracingChannel$1(channelName).subscribe({
      start: NOOP,
      asyncStart: NOOP,
      asyncEnd: NOOP,
      error: NOOP,
      end: captureRegisteredLayerPath
    });
  }
  for (const channelName of [channels.CHANNELS.EXPRESS_HANDLE, channels.CHANNELS.ROUTER_HANDLE]) {
    debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:express] subscribing to channel "${channelName}"`);
    const channel = tracingChannel$1(channelName);
    tracingChannel.bindTracingChannelToSpan(channel, (data) => getSpanForLayer(data, options), {
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
    route.setLayerRegisteredPath(layer, route.getLayerPath(data.arguments ?? []));
  }
}
function popLayerPathForLayer(data) {
  if (!data._sentryStoredLayer) {
    return;
  }
  data._sentryStoredLayer = false;
  const req = data.arguments?.[0];
  if (req) {
    route.popLayerPath(req);
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
  if (!core.getActiveSpan()) {
    return void 0;
  }
  const type = getLayerType(layer);
  const registeredPath = route.getLayerRegisteredPath(layer);
  if (registeredPath != null) {
    route.pushLayerPath(req, registeredPath);
    data._sentryStoredLayer = true;
  }
  const constructedRoute = type === "request_handler" ? route.getConstructedRoute(req) : void 0;
  const matchedRoute = type === "request_handler" && constructedRoute != null ? route.getActualMatchedRoute(req, constructedRoute) : void 0;
  const name = type === "request_handler" ? constructedRoute || "request handler" : type === "router" ? layer.path ?? "/" : layer.name ?? "<anonymous>";
  if (matchedRoute) {
    setHttpServerSpanRoute(matchedRoute);
  }
  if (type === "request_handler" && constructedRoute) {
    const isolationScope = core.getIsolationScope();
    if (isolationScope !== core.getDefaultIsolationScope()) {
      const method = typeof req.method === "string" ? req.method.toUpperCase() : "GET";
      isolationScope.setTransactionName(`${method} ${constructedRoute}`);
    } else {
      debugBuild.DEBUG_BUILD && core.debug.warn(
        "[orchestrion:express] Isolation scope is still default isolation scope - skipping transaction name"
      );
    }
  }
  if (isLayerIgnored(name, type, options)) {
    return void 0;
  }
  const span = core.startInactiveSpan({
    name,
    attributes: {
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
      [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: `${type}.express`,
      [ATTR_EXPRESS_NAME]: name,
      [ATTR_EXPRESS_TYPE]: type,
      ...matchedRoute ? { [attributes.HTTP_ROUTE]: matchedRoute } : {}
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
  const activeSpan = core.getActiveSpan();
  const rootSpan = activeSpan && core.getRootSpan(activeSpan);
  if (!rootSpan) {
    return;
  }
  if (core.spanToJSON(rootSpan).data[core.SEMANTIC_ATTRIBUTE_SENTRY_OP] !== "http.server") {
    return;
  }
  rootSpan.setAttribute(attributes.HTTP_ROUTE, route);
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
    return core.stringMatchesSomePattern(name, ignoreLayers, true);
  } catch {
    return false;
  }
}

exports.instrumentExpress = instrumentExpress;
//# sourceMappingURL=instrumentation.js.map
