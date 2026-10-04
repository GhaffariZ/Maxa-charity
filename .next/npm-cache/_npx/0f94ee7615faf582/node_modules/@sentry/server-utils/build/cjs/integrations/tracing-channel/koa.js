Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const debugBuild = require('../../debug-build.js');
const channels = require('../../orchestrion/channels.js');

function _interopNamespaceDefault(e) {
  const n = Object.create(null, { [Symbol.toStringTag]: { value: 'Module' } });
  if (e) {
    for (const k in e) {
      n[k] = e[k];
    }
  }
  n.default = e;
  return n;
}

const diagnosticsChannel__namespace = /*#__PURE__*/_interopNamespaceDefault(diagnosticsChannel);

const INTEGRATION_NAME = "Koa";
const ORIGIN = "auto.http.orchestrion.koa";
const LAYER_TYPE = {
  ROUTER: "router",
  MIDDLEWARE: "middleware"
};
const kLayerPatched = /* @__PURE__ */ Symbol("sentry.koa.layer-patched");
let subscribed = false;
const _koaChannelIntegration = ((options = {}) => {
  const ignoreLayersType = options.ignoreLayersType ?? [];
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:koa] subscribing to channel "${channels.CHANNELS.KOA_USE}"`);
      diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.KOA_USE).subscribe({
        start(rawCtx) {
          handleUse(rawCtx, ignoreLayersType);
        },
        end() {
        },
        asyncStart() {
        },
        asyncEnd() {
        },
        error() {
        }
      });
    }
  };
});
function handleUse(ctx, ignoreLayersType) {
  const middleware = ctx.arguments[0];
  if (typeof middleware === "function") {
    ctx.arguments[0] = patchUse(middleware, ignoreLayersType);
  }
}
function patchUse(middleware, ignoreLayersType) {
  return middleware.router ? patchRouterDispatch(middleware, ignoreLayersType) : patchLayer(middleware, false, ignoreLayersType);
}
function patchRouterDispatch(dispatchLayer, ignoreLayersType) {
  const router = dispatchLayer.router;
  const routesStack = router?.stack ?? [];
  for (const pathLayer of routesStack) {
    const path = pathLayer.path;
    const pathStack = pathLayer.stack;
    pathStack.forEach((routedMiddleware, j) => {
      pathStack[j] = patchLayer(routedMiddleware, true, ignoreLayersType, path);
    });
  }
  return dispatchLayer;
}
function patchLayer(middlewareLayer, isRouter, ignoreLayersType, layerPath) {
  const layerType = isRouter ? LAYER_TYPE.ROUTER : LAYER_TYPE.MIDDLEWARE;
  if (middlewareLayer[kLayerPatched] === true || ignoreLayersType.includes(layerType)) {
    return middlewareLayer;
  }
  if (middlewareLayer.constructor.name === "GeneratorFunction" || middlewareLayer.constructor.name === "AsyncGeneratorFunction") {
    return middlewareLayer;
  }
  middlewareLayer[kLayerPatched] = true;
  return (context, next) => {
    if (!core.getActiveSpan()) {
      return middlewareLayer(context, next);
    }
    const metadata = getMiddlewareMetadata(context, middlewareLayer, isRouter, layerPath);
    if (context._matchedRoute) {
      setHttpServerSpanRouteAttribute(context._matchedRoute.toString());
    }
    const koaName = metadata.attributes[attributes.KOA_NAME];
    const name = typeof koaName === "string" ? koaName || "< unknown >" : metadata.name;
    return core.startSpan(
      {
        name,
        op: `${layerType}.koa`,
        attributes: {
          ...metadata.attributes,
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        }
      },
      () => {
        const route = metadata.attributes[attributes.HTTP_ROUTE];
        if (core.getIsolationScope() === core.getDefaultIsolationScope()) {
          debugBuild.DEBUG_BUILD && core.debug.warn("Isolation scope is default isolation scope - skipping setting transactionName");
        } else if (route) {
          const method = context.request?.method?.toUpperCase() || "GET";
          core.getIsolationScope().setTransactionName(`${method} ${route}`);
        }
        return middlewareLayer(context, next);
      }
    );
  };
}
function getMiddlewareMetadata(context, layer, isRouter, layerPath) {
  if (isRouter) {
    return {
      attributes: {
        // oxlint-disable-next-line typescript/no-deprecated
        [attributes.KOA_NAME]: layerPath?.toString(),
        // TODO(v11): remove, replaced by http.route
        [attributes.KOA_TYPE]: LAYER_TYPE.ROUTER,
        [attributes.HTTP_ROUTE]: layerPath?.toString()
      },
      name: context._matchedRouteName || `router - ${layerPath}`
    };
  }
  return {
    attributes: {
      // oxlint-disable-next-line typescript/no-deprecated
      [attributes.KOA_NAME]: layer.name || "middleware",
      // TODO(v11): remove, replaced by code.function.name
      [attributes.KOA_TYPE]: LAYER_TYPE.MIDDLEWARE,
      [attributes.CODE_FUNCTION_NAME]: layer.name || "middleware"
    },
    name: `middleware - ${layer.name}`
  };
}
function setHttpServerSpanRouteAttribute(route) {
  const activeSpan = core.getActiveSpan();
  if (!activeSpan) {
    return;
  }
  const rootSpan = core.getRootSpan(activeSpan);
  if (!rootSpan) {
    return;
  }
  if (core.spanToJSON(rootSpan).data[core.SEMANTIC_ATTRIBUTE_SENTRY_OP] !== "http.server") {
    return;
  }
  rootSpan.setAttribute(attributes.HTTP_ROUTE, route);
}
const koaChannelIntegration = core.defineIntegration(_koaChannelIntegration);

exports.koaChannelIntegration = koaChannelIntegration;
//# sourceMappingURL=koa.js.map
