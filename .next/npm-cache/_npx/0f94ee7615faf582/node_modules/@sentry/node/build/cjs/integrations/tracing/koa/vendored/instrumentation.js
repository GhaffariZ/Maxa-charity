Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const api = require('@opentelemetry/api');
const instrumentation = require('@opentelemetry/instrumentation');
const types = require('./types.js');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const utils = require('./utils.js');
const setHttpServerSpanRouteAttribute = require('../../../../utils/setHttpServerSpanRouteAttribute.js');
const debugBuild = require('../../../../debug-build.js');
const AttributeNames = require('./enums/AttributeNames.js');
const internalTypes = require('./internal-types.js');

const PACKAGE_NAME = "@sentry/instrumentation-koa";
class KoaInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    return new instrumentation.InstrumentationNodeModuleDefinition(
      "koa",
      [">=2.0.0 <4"],
      (module) => {
        const moduleExports = module[Symbol.toStringTag] === "Module" ? module.default : module;
        if (moduleExports == null) {
          return moduleExports;
        }
        if (instrumentation.isWrapped(moduleExports.prototype.use)) {
          this._unwrap(moduleExports.prototype, "use");
        }
        this._wrap(moduleExports.prototype, "use", this._getKoaUsePatch.bind(this));
        return module;
      },
      (module) => {
        const moduleExports = module[Symbol.toStringTag] === "Module" ? module.default : module;
        if (moduleExports && instrumentation.isWrapped(moduleExports.prototype.use)) {
          this._unwrap(moduleExports.prototype, "use");
        }
      }
    );
  }
  /**
   * Patches the Koa.use function in order to instrument each original
   * middleware layer which is introduced
   * @param {KoaMiddleware} middleware - the original middleware function
   */
  _getKoaUsePatch(original) {
    const patchRouterDispatch = this._patchRouterDispatch.bind(this);
    const patchLayer = this._patchLayer.bind(this);
    return function use(middlewareFunction) {
      const patchedFunction = middlewareFunction.router ? patchRouterDispatch(middlewareFunction) : patchLayer(middlewareFunction, false);
      return original.apply(this, [patchedFunction]);
    };
  }
  /**
   * Patches the dispatch function used by @koa/router. This function
   * goes through each routed middleware and adds instrumentation via a call
   * to the @function _patchLayer function.
   * @param {KoaMiddleware} dispatchLayer - the original dispatch function which dispatches
   * routed middleware
   */
  _patchRouterDispatch(dispatchLayer) {
    const router = dispatchLayer.router;
    const routesStack = router?.stack ?? [];
    for (const pathLayer of routesStack) {
      const path = pathLayer.path;
      const pathStack = pathLayer.stack;
      for (let j = 0; j < pathStack.length; j++) {
        const routedMiddleware = pathStack[j];
        pathStack[j] = this._patchLayer(routedMiddleware, true, path);
      }
    }
    return dispatchLayer;
  }
  /**
   * Patches each individual @param middlewareLayer function in order to create the
   * span and propagate context. It does not create spans when there is no parent span.
   * @param {KoaMiddleware} middlewareLayer - the original middleware function.
   * @param {boolean} isRouter - tracks whether the original middleware function
   * was dispatched by the router originally
   * @param {string?} layerPath - if present, provides additional data from the
   * router about the routed path which the middleware is attached to
   */
  _patchLayer(middlewareLayer, isRouter, layerPath) {
    const layerType = isRouter ? types.KoaLayerType.ROUTER : types.KoaLayerType.MIDDLEWARE;
    if (middlewareLayer[internalTypes.kLayerPatched] === true || utils.isLayerIgnored(layerType, this.getConfig())) return middlewareLayer;
    if (middlewareLayer.constructor.name === "GeneratorFunction" || middlewareLayer.constructor.name === "AsyncGeneratorFunction") {
      return middlewareLayer;
    }
    middlewareLayer[internalTypes.kLayerPatched] = true;
    return (context, next) => {
      const parent = api.trace.getSpan(api.context.active());
      if (parent === void 0) {
        return middlewareLayer(context, next);
      }
      const metadata = utils.getMiddlewareMetadata(context, middlewareLayer, isRouter, layerPath);
      if (context._matchedRoute) {
        setHttpServerSpanRouteAttribute.setHttpServerSpanRouteAttribute(context._matchedRoute.toString());
      }
      const koaName = metadata.attributes[AttributeNames.AttributeNames.KOA_NAME];
      const name = typeof koaName === "string" ? koaName || "< unknown >" : metadata.name;
      return core.startSpan(
        {
          name,
          op: `${layerType}.koa`,
          attributes: {
            ...metadata.attributes,
            [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.http.otel.koa"
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
}

exports.KoaInstrumentation = KoaInstrumentation;
//# sourceMappingURL=instrumentation.js.map
