const LIFECYCLE_EXT_POINTS = [
  "onPreAuth",
  "onCredentials",
  "onPostAuth",
  "onPreHandler",
  "onPostHandler",
  "onPreResponse",
  "onRequest"
];
const handlerPatched = /* @__PURE__ */ Symbol("hapi-handler-patched");
const HapiLayerType = {
  ROUTER: "router",
  PLUGIN: "plugin",
  EXT: "server.ext"
};
const HapiLifecycleMethodNames = new Set(LIFECYCLE_EXT_POINTS);
var AttributeNames = /* @__PURE__ */ ((AttributeNames2) => {
  AttributeNames2["HAPI_TYPE"] = "hapi.type";
  AttributeNames2["PLUGIN_NAME"] = "hapi.plugin.name";
  AttributeNames2["EXT_TYPE"] = "server.ext.type";
  return AttributeNames2;
})(AttributeNames || {});

export { AttributeNames, HapiLayerType, HapiLifecycleMethodNames, handlerPatched };
//# sourceMappingURL=hapi-types.js.map
