import { getActiveSpan, getRootSpan, spanToJSON, SEMANTIC_ATTRIBUTE_SENTRY_OP, startSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { HTTP_ROUTE, HTTP_METHOD } from '@sentry/conventions/attributes';
import { handlerPatched, HapiLifecycleMethodNames, HapiLayerType, AttributeNames } from './hapi-types.js';

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
const isLifecycleExtType = (variableToCheck) => {
  return typeof variableToCheck === "string" && HapiLifecycleMethodNames.has(variableToCheck);
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
  const attributes = {
    [HTTP_ROUTE]: route.path,
    // eslint-disable-next-line typescript/no-deprecated -- TODO(v11): Replace deprecated attributes
    [HTTP_METHOD]: route.method
  };
  let name;
  if (pluginName) {
    attributes[AttributeNames.HAPI_TYPE] = HapiLayerType.PLUGIN;
    attributes[AttributeNames.PLUGIN_NAME] = pluginName;
    name = `${pluginName}: route - ${route.path}`;
  } else {
    attributes[AttributeNames.HAPI_TYPE] = HapiLayerType.ROUTER;
    name = `route - ${route.path}`;
  }
  return { attributes, name };
};
const getExtMetadata = (extPoint, pluginName, methodName) => {
  let baseName = `ext - ${extPoint}`;
  if (methodName && methodName !== "method") {
    baseName = `ext - ${extPoint} - ${methodName}`;
  }
  if (pluginName) {
    return {
      attributes: {
        [AttributeNames.EXT_TYPE]: extPoint,
        [AttributeNames.HAPI_TYPE]: HapiLayerType.EXT,
        [AttributeNames.PLUGIN_NAME]: pluginName
      },
      name: `${pluginName}: ${baseName}`
    };
  }
  return {
    attributes: {
      [AttributeNames.EXT_TYPE]: extPoint,
      [AttributeNames.HAPI_TYPE]: HapiLayerType.EXT
    },
    name: baseName
  };
};
function startMetadataSpan(metadata, original) {
  return startSpan(
    {
      name: metadata.name,
      op: `${metadata.attributes[AttributeNames.HAPI_TYPE]}.hapi`,
      attributes: {
        ...metadata.attributes,
        [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.http.orchestrion.hapi"
      }
    },
    original
  );
}
function wrapRouteHandler(route, pluginName) {
  if (route[handlerPatched] === true) return route;
  route[handlerPatched] = true;
  const wrapHandler = (oldHandler) => {
    return function(...params) {
      if (!getActiveSpan()) {
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
    if (method[handlerPatched] === true) return method;
    method[handlerPatched] = true;
    const newHandler = function(...params) {
      if (!getActiveSpan()) {
        return method.apply(this, params);
      }
      const metadata = getExtMetadata(extPoint, pluginName, method.name);
      return startMetadataSpan(metadata, () => method.apply(void 0, params));
    };
    newHandler[handlerPatched] = true;
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

export { getExtMetadata, getRouteMetadata, wrapExtArguments, wrapRouteArguments };
//# sourceMappingURL=hapi-utils.js.map
