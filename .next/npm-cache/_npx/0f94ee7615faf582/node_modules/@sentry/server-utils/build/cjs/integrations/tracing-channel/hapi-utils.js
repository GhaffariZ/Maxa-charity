Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const hapiTypes = require('./hapi-types.js');

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
const isLifecycleExtType = (variableToCheck) => {
  return typeof variableToCheck === "string" && hapiTypes.HapiLifecycleMethodNames.has(variableToCheck);
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
    // eslint-disable-next-line typescript/no-deprecated -- TODO(v11): Replace deprecated attributes
    [attributes.HTTP_METHOD]: route.method
  };
  let name;
  if (pluginName) {
    attributes$1[hapiTypes.AttributeNames.HAPI_TYPE] = hapiTypes.HapiLayerType.PLUGIN;
    attributes$1[hapiTypes.AttributeNames.PLUGIN_NAME] = pluginName;
    name = `${pluginName}: route - ${route.path}`;
  } else {
    attributes$1[hapiTypes.AttributeNames.HAPI_TYPE] = hapiTypes.HapiLayerType.ROUTER;
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
        [hapiTypes.AttributeNames.EXT_TYPE]: extPoint,
        [hapiTypes.AttributeNames.HAPI_TYPE]: hapiTypes.HapiLayerType.EXT,
        [hapiTypes.AttributeNames.PLUGIN_NAME]: pluginName
      },
      name: `${pluginName}: ${baseName}`
    };
  }
  return {
    attributes: {
      [hapiTypes.AttributeNames.EXT_TYPE]: extPoint,
      [hapiTypes.AttributeNames.HAPI_TYPE]: hapiTypes.HapiLayerType.EXT
    },
    name: baseName
  };
};
function startMetadataSpan(metadata, original) {
  return core.startSpan(
    {
      name: metadata.name,
      op: `${metadata.attributes[hapiTypes.AttributeNames.HAPI_TYPE]}.hapi`,
      attributes: {
        ...metadata.attributes,
        [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.http.orchestrion.hapi"
      }
    },
    original
  );
}
function wrapRouteHandler(route, pluginName) {
  if (route[hapiTypes.handlerPatched] === true) return route;
  route[hapiTypes.handlerPatched] = true;
  const wrapHandler = (oldHandler) => {
    return function(...params) {
      if (!core.getActiveSpan()) {
        return oldHandler.call(this, ...params);
      }
      setHttpServerSpanRouteAttribute(route.path);
      const metadata = getRouteMetadata(route, pluginName);
      return startMetadataSpan(metadata, () => oldHandler.call(this, ...params));
    };
  };
  if (typeof route.handler === "function") {
    route.handler = wrapHandler(route.handler);
  } else if (typeof route.options === "function") {
    const oldOptions = route.options;
    route.options = function(server) {
      const options = oldOptions(server);
      if (typeof options.handler === "function") {
        options.handler = wrapHandler(options.handler);
      }
      return options;
    };
  } else if (typeof route.options?.handler === "function") {
    route.options.handler = wrapHandler(route.options.handler);
  }
  return route;
}
function wrapExtMethods(method, extPoint, pluginName) {
  if (Array.isArray(method)) {
    for (let i = 0; i < method.length; i++) {
      method[i] = wrapExtMethods(method[i], extPoint);
    }
    return method;
  } else if (isPatchableExtMethod(method)) {
    if (method[hapiTypes.handlerPatched] === true) return method;
    method[hapiTypes.handlerPatched] = true;
    const newHandler = function(...params) {
      if (!core.getActiveSpan()) {
        return method.apply(this, params);
      }
      const metadata = getExtMetadata(extPoint, pluginName, method.name);
      return startMetadataSpan(metadata, () => method.apply(void 0, params));
    };
    newHandler[hapiTypes.handlerPatched] = true;
    return newHandler;
  }
  return method;
}
function wrapRouteArguments(args, pluginName) {
  const route = args[0];
  if (Array.isArray(route)) {
    for (let i = 0; i < route.length; i++) {
      route[i] = wrapRouteHandler(route[i], pluginName);
    }
  } else {
    args[0] = wrapRouteHandler(route, pluginName);
  }
}
function wrapExtArguments(args, pluginName) {
  if (Array.isArray(args[0])) {
    const eventsList = args[0];
    for (let i = 0; i < eventsList.length; i++) {
      const eventObj = eventsList[i];
      if (isLifecycleExtType(eventObj.type)) {
        const lifecycleEventObj = eventObj;
        const handler = wrapExtMethods(lifecycleEventObj.method, eventObj.type, pluginName);
        lifecycleEventObj.method = handler;
        eventsList[i] = lifecycleEventObj;
      }
    }
    return;
  } else if (isDirectExtInput(args)) {
    const extInput = args;
    const method = extInput[1];
    const handler = wrapExtMethods(method, extInput[0], pluginName);
    args[1] = handler;
    return;
  } else if (isLifecycleExtEventObj(args[0])) {
    const lifecycleEventObj = args[0];
    const handler = wrapExtMethods(lifecycleEventObj.method, lifecycleEventObj.type, pluginName);
    lifecycleEventObj.method = handler;
  }
}

exports.getExtMetadata = getExtMetadata;
exports.getRouteMetadata = getRouteMetadata;
exports.wrapExtArguments = wrapExtArguments;
exports.wrapRouteArguments = wrapRouteArguments;
//# sourceMappingURL=hapi-utils.js.map
