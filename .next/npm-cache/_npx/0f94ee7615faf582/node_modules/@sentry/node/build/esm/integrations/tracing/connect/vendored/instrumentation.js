import { ConnectTypes, AttributeNames } from './enums/AttributeNames.js';
import { SDK_VERSION, isError, SPAN_STATUS_ERROR, startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { setHttpServerSpanRouteAttribute } from '../../../../utils/setHttpServerSpanRouteAttribute.js';
import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { HTTP_ROUTE } from '@sentry/conventions/attributes';
import { addNewStackLayer, replaceCurrentStackRoute, generateRoute } from './utils.js';

const PACKAGE_NAME = "@sentry/instrumentation-connect";
const ANONYMOUS_NAME = "anonymous";
class ConnectInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    return [
      new InstrumentationNodeModuleDefinition("connect", [">=3.0.0 <4"], (moduleExports) => {
        return this._patchConstructor(moduleExports);
      })
    ];
  }
  _patchApp(patchedApp) {
    if (!isWrapped(patchedApp.use)) {
      this._wrap(patchedApp, "use", this._patchUse.bind(this));
    }
    if (!isWrapped(patchedApp.handle)) {
      this._wrap(patchedApp, "handle", this._patchHandle.bind(this));
    }
  }
  _patchConstructor(original) {
    const patchApp = this._patchApp.bind(this);
    return function(...args) {
      const app = Reflect.apply(original, this, args);
      patchApp(app);
      return app;
    };
  }
  _patchNext(next, span, finishSpan) {
    return function nextFunction(err) {
      if (isError(err)) {
        span.setStatus({ code: SPAN_STATUS_ERROR, message: "internal_error" });
      }
      const result = next.apply(this, [err]);
      finishSpan();
      return result;
    };
  }
  _startSpan(routeName, middleWare) {
    const connectType = routeName ? ConnectTypes.REQUEST_HANDLER : ConnectTypes.MIDDLEWARE;
    const connectName = routeName || middleWare.name || ANONYMOUS_NAME;
    return startInactiveSpan({
      name: connectName,
      op: `${connectType}.connect`,
      attributes: {
        [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.http.otel.connect",
        [HTTP_ROUTE]: routeName.length > 0 ? routeName : "/",
        [AttributeNames.CONNECT_TYPE]: connectType,
        [AttributeNames.CONNECT_NAME]: connectName
      }
    });
  }
  _patchMiddleware(routeName, middleWare) {
    const isEnabled = this.isEnabled.bind(this);
    const startSpan = this._startSpan.bind(this);
    const patchNext = this._patchNext.bind(this);
    const isErrorMiddleware = middleWare.length === 4;
    function patchedMiddleware() {
      if (!isEnabled()) {
        return Reflect.apply(middleWare, this, arguments);
      }
      const [reqArgIdx, resArgIdx, nextArgIdx] = isErrorMiddleware ? [1, 2, 3] : [0, 1, 2];
      const req = arguments[reqArgIdx];
      const res = arguments[resArgIdx];
      const next = arguments[nextArgIdx];
      replaceCurrentStackRoute(req, routeName);
      if (routeName) {
        setHttpServerSpanRouteAttribute(generateRoute(req));
      }
      const span = startSpan(routeName, middleWare);
      let spanFinished = false;
      function finishSpan() {
        if (!spanFinished) {
          spanFinished = true;
          span.end();
        }
        res.removeListener("close", finishSpan);
      }
      res.addListener("close", finishSpan);
      arguments[nextArgIdx] = patchNext(next, span, finishSpan);
      try {
        return Reflect.apply(middleWare, this, arguments);
      } catch (e) {
        span.setStatus({ code: SPAN_STATUS_ERROR, message: "internal_error" });
        finishSpan();
        throw e;
      }
    }
    Object.defineProperty(patchedMiddleware, "length", {
      value: middleWare.length,
      writable: false,
      configurable: true
    });
    return patchedMiddleware;
  }
  _patchUse(original) {
    const patchMiddleware = this._patchMiddleware.bind(this);
    return function(...args) {
      const middleWare = args[args.length - 1];
      const routeName = args[args.length - 2] || "";
      args[args.length - 1] = patchMiddleware(routeName, middleWare);
      return original.apply(this, args);
    };
  }
  _patchHandle(original) {
    const patchOut = this._patchOut.bind(this);
    return function() {
      const [reqIdx, outIdx] = [0, 2];
      const req = arguments[reqIdx];
      const out = arguments[outIdx];
      const completeStack = addNewStackLayer(req);
      if (typeof out === "function") {
        arguments[outIdx] = patchOut(out, completeStack);
      }
      return Reflect.apply(original, this, arguments);
    };
  }
  _patchOut(out, completeStack) {
    return function nextFunction(...args) {
      completeStack();
      return Reflect.apply(out, this, args);
    };
  }
}

export { ConnectInstrumentation };
//# sourceMappingURL=instrumentation.js.map
