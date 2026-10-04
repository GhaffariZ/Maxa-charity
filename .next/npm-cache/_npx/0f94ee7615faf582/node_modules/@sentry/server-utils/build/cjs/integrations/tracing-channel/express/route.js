Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const layerRegisteredPaths = /* @__PURE__ */ new WeakMap();
function setLayerRegisteredPath(layer, path) {
  layerRegisteredPaths.set(layer, path);
}
function getLayerRegisteredPath(layer) {
  return layerRegisteredPaths.get(layer);
}
const requestLayerPaths = /* @__PURE__ */ new WeakMap();
function getStore(req) {
  let store = requestLayerPaths.get(req);
  if (!store) {
    store = [];
    requestLayerPaths.set(req, store);
  }
  return store;
}
function pushLayerPath(req, path) {
  getStore(req).push(path);
}
function popLayerPath(req) {
  getStore(req).pop();
}
function getLayerPath(args) {
  const firstArg = args[0];
  if (Array.isArray(firstArg)) {
    return firstArg.map((segment) => extractLayerPathSegment(segment) ?? "").join(",");
  }
  return extractLayerPathSegment(firstArg);
}
function extractLayerPathSegment(segment) {
  return typeof segment === "string" ? segment : segment instanceof RegExp || typeof segment === "number" ? String(segment) : void 0;
}
function getConstructedRoute(req) {
  const layersStore = getStore(req);
  let constructedRoute = "";
  for (const path of layersStore) {
    if (path === "/" || path === "/*") {
      continue;
    }
    constructedRoute += !constructedRoute || constructedRoute.endsWith("/") ? path : `/${path}`;
  }
  return constructedRoute.replace(/\/{2,}/g, "/");
}
function getActualMatchedRoute(req, constructedRoute) {
  const layersStore = getStore(req);
  if (layersStore.length === 0) {
    return void 0;
  }
  const originalUrl = typeof req.originalUrl === "string" ? req.originalUrl : "";
  if (layersStore.every((path) => path === "/")) {
    return originalUrl === "/" ? "/" : void 0;
  }
  if (constructedRoute === "*") {
    return constructedRoute;
  }
  if (constructedRoute.includes("/") && (constructedRoute.includes(",") || constructedRoute.includes("\\") || constructedRoute.includes("*") || constructedRoute.includes("["))) {
    return constructedRoute;
  }
  const normalizedRoute = constructedRoute.startsWith("/") ? constructedRoute : `/${constructedRoute}`;
  const isValidRoute = normalizedRoute.length > 0 && (originalUrl === normalizedRoute || originalUrl.startsWith(normalizedRoute) || isRoutePattern(normalizedRoute));
  return isValidRoute ? normalizedRoute : void 0;
}
function isRoutePattern(route) {
  return route.includes(":") || route.includes("*");
}

exports.getActualMatchedRoute = getActualMatchedRoute;
exports.getConstructedRoute = getConstructedRoute;
exports.getLayerPath = getLayerPath;
exports.getLayerRegisteredPath = getLayerRegisteredPath;
exports.popLayerPath = popLayerPath;
exports.pushLayerPath = pushLayerPath;
exports.setLayerRegisteredPath = setLayerRegisteredPath;
//# sourceMappingURL=route.js.map
