Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const types = require('./types.js');
const AttributeNames = require('./enums/AttributeNames.js');
const attributes = require('@sentry/conventions/attributes');

const getMiddlewareMetadata = (context, layer, isRouter, layerPath) => {
  if (isRouter) {
    return {
      attributes: {
        [AttributeNames.AttributeNames.KOA_NAME]: layerPath?.toString(),
        // TODO(v11): remove, replaced by http.route
        [AttributeNames.AttributeNames.KOA_TYPE]: types.KoaLayerType.ROUTER,
        [attributes.HTTP_ROUTE]: layerPath?.toString()
      },
      name: context._matchedRouteName || `router - ${layerPath}`
    };
  } else {
    return {
      attributes: {
        [AttributeNames.AttributeNames.KOA_NAME]: layer.name ?? "middleware",
        // TODO(v11): remove, replaced by code.function.name
        [AttributeNames.AttributeNames.KOA_TYPE]: types.KoaLayerType.MIDDLEWARE,
        [attributes.CODE_FUNCTION_NAME]: layer.name ?? "middleware"
      },
      name: `middleware - ${layer.name}`
    };
  }
};
const isLayerIgnored = (type, config) => {
  return !!(Array.isArray(config?.ignoreLayersType) && config?.ignoreLayersType?.includes(type));
};

exports.getMiddlewareMetadata = getMiddlewareMetadata;
exports.isLayerIgnored = isLayerIgnored;
//# sourceMappingURL=utils.js.map
