import { KoaLayerType } from './types.js';
import { AttributeNames } from './enums/AttributeNames.js';
import { HTTP_ROUTE, CODE_FUNCTION_NAME } from '@sentry/conventions/attributes';

const getMiddlewareMetadata = (context, layer, isRouter, layerPath) => {
  if (isRouter) {
    return {
      attributes: {
        [AttributeNames.KOA_NAME]: layerPath?.toString(),
        // TODO(v11): remove, replaced by http.route
        [AttributeNames.KOA_TYPE]: KoaLayerType.ROUTER,
        [HTTP_ROUTE]: layerPath?.toString()
      },
      name: context._matchedRouteName || `router - ${layerPath}`
    };
  } else {
    return {
      attributes: {
        [AttributeNames.KOA_NAME]: layer.name ?? "middleware",
        // TODO(v11): remove, replaced by code.function.name
        [AttributeNames.KOA_TYPE]: KoaLayerType.MIDDLEWARE,
        [CODE_FUNCTION_NAME]: layer.name ?? "middleware"
      },
      name: `middleware - ${layer.name}`
    };
  }
};
const isLayerIgnored = (type, config) => {
  return !!(Array.isArray(config?.ignoreLayersType) && config?.ignoreLayersType?.includes(type));
};

export { getMiddlewareMetadata, isLayerIgnored };
//# sourceMappingURL=utils.js.map
