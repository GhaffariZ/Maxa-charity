import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, debug, getActiveSpan, startSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, getIsolationScope, getDefaultIsolationScope, getRootSpan, spanToJSON, SEMANTIC_ATTRIBUTE_SENTRY_OP } from '@sentry/core';
import { KOA_NAME, HTTP_ROUTE, KOA_TYPE, CODE_FUNCTION_NAME } from '@sentry/conventions/attributes';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';

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
      if (!diagnosticsChannel.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      DEBUG_BUILD && debug.log(`[orchestrion:koa] subscribing to channel "${CHANNELS.KOA_USE}"`);
      diagnosticsChannel.tracingChannel(CHANNELS.KOA_USE).subscribe({
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
    if (!getActiveSpan()) {
      return middlewareLayer(context, next);
    }
    const metadata = getMiddlewareMetadata(context, middlewareLayer, isRouter, layerPath);
    if (context._matchedRoute) {
      setHttpServerSpanRouteAttribute(context._matchedRoute.toString());
    }
    const koaName = metadata.attributes[KOA_NAME];
    const name = typeof koaName === "string" ? koaName || "< unknown >" : metadata.name;
    return startSpan(
      {
        name,
        op: `${layerType}.koa`,
        attributes: {
          ...metadata.attributes,
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        }
      },
      () => {
        const route = metadata.attributes[HTTP_ROUTE];
        if (getIsolationScope() === getDefaultIsolationScope()) {
          DEBUG_BUILD && debug.warn("Isolation scope is default isolation scope - skipping setting transactionName");
        } else if (route) {
          const method = context.request?.method?.toUpperCase() || "GET";
          getIsolationScope().setTransactionName(`${method} ${route}`);
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
        [KOA_NAME]: layerPath?.toString(),
        // TODO(v11): remove, replaced by http.route
        [KOA_TYPE]: LAYER_TYPE.ROUTER,
        [HTTP_ROUTE]: layerPath?.toString()
      },
      name: context._matchedRouteName || `router - ${layerPath}`
    };
  }
  return {
    attributes: {
      // oxlint-disable-next-line typescript/no-deprecated
      [KOA_NAME]: layer.name || "middleware",
      // TODO(v11): remove, replaced by code.function.name
      [KOA_TYPE]: LAYER_TYPE.MIDDLEWARE,
      [CODE_FUNCTION_NAME]: layer.name || "middleware"
    },
    name: `middleware - ${layer.name}`
  };
}
function setHttpServerSpanRouteAttribute(route) {
  const activeSpan = getActiveSpan();
  if (!activeSpan) {
    return;
  }
  const rootSpan = getRootSpan(activeSpan);
  if (!rootSpan) {
    return;
  }
  if (spanToJSON(rootSpan).data[SEMANTIC_ATTRIBUTE_SENTRY_OP] !== "http.server") {
    return;
  }
  rootSpan.setAttribute(HTTP_ROUTE, route);
}
const koaChannelIntegration = defineIntegration(_koaChannelIntegration);

export { koaChannelIntegration };
//# sourceMappingURL=koa.js.map
