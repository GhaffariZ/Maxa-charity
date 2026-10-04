Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const internalTypes = require('./internal-types.js');
const AttributeNames = require('./enums/AttributeNames.js');

function getPluginName(plugin) {
  if (plugin.name) {
    return plugin.name;
  } else {
    return plugin.pkg.name;
  }
}
const isLifecycleExtType = (variableToCheck) => {
  return typeof variableToCheck === "string" && internalTypes.HapiLifecycleMethodNames.has(variableToCheck);
};
const isLifecycleExtEventObj = (variableToCheck) => {
  const event = variableToCheck?.type;
  return event !== void 0 && isLifecycleExtType(event);
};
const isDirectExtInput = (variableToCheck) => {
  return Array.isArray(variableToCheck) && variableToCheck.length <= 3 && isLifecycleExtType(variableToCheck[0]) && typeof variableToCheck[1] === "function";
};
const isPatchableExtMethod = (variableToCheck) => {
  return !Array.isArray(variableToCheck);
};
const getRouteMetadata = (route, pluginName) => {
  const attributes$1 = {
    [attributes.HTTP_ROUTE]: route.path,
    // eslint-disable-next-line typescript/no-deprecated
    [attributes.HTTP_METHOD]: route.method
  };
  let name;
  if (pluginName) {
    attributes$1[AttributeNames.AttributeNames.HAPI_TYPE] = internalTypes.HapiLayerType.PLUGIN;
    attributes$1[AttributeNames.AttributeNames.PLUGIN_NAME] = pluginName;
    name = `${pluginName}: route - ${route.path}`;
  } else {
    attributes$1[AttributeNames.AttributeNames.HAPI_TYPE] = internalTypes.HapiLayerType.ROUTER;
    name = `route - ${route.path}`;
  }
  return { attributes: attributes$1, name };
};
const getExtMetadata = (extPoint, pluginName, methodName) => {
  let baseName = `ext - ${extPoint}`;
  if (methodName && methodName !== "method") {
    baseName = `ext - ${extPoint} - ${methodName}`;
  }
  if (pluginName) {
    return {
      attributes: {
        [AttributeNames.AttributeNames.EXT_TYPE]: extPoint,
        [AttributeNames.AttributeNames.HAPI_TYPE]: internalTypes.HapiLayerType.EXT,
        [AttributeNames.AttributeNames.PLUGIN_NAME]: pluginName
      },
      name: `${pluginName}: ${baseName}`
    };
  }
  return {
    attributes: {
      [AttributeNames.AttributeNames.EXT_TYPE]: extPoint,
      [AttributeNames.AttributeNames.HAPI_TYPE]: internalTypes.HapiLayerType.EXT
    },
    name: baseName
  };
};
const getPluginFromInput = (pluginObj) => {
  if ("plugin" in pluginObj) {
    if ("plugin" in pluginObj.plugin) {
      return pluginObj.plugin.plugin;
    }
    return pluginObj.plugin;
  }
  return pluginObj;
};

exports.getExtMetadata = getExtMetadata;
exports.getPluginFromInput = getPluginFromInput;
exports.getPluginName = getPluginName;
exports.getRouteMetadata = getRouteMetadata;
exports.isDirectExtInput = isDirectExtInput;
exports.isLifecycleExtEventObj = isLifecycleExtEventObj;
exports.isLifecycleExtType = isLifecycleExtType;
exports.isPatchableExtMethod = isPatchableExtMethod;
//# sourceMappingURL=utils.js.map
