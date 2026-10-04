import * as diagnosticsChannel from 'node:diagnostics_channel';
import { HTTP_ROUTE, URL_PATH, HTTP_REQUEST_METHOD, HTTP_RESPONSE_STATUS_CODE } from '@sentry/conventions/attributes';
import { debug, getIsolationScope, getActiveSpan, getRootSpan, spanToJSON, SEMANTIC_ATTRIBUTE_SENTRY_OP, startInactiveSpan, withActiveSpan, startSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, isObjectLike, SPAN_STATUS_ERROR } from '@sentry/core';
import { DEBUG_BUILD } from '../../../debug-build.js';

const PACKAGE_NAME = "@sentry/instrumentation-fastify";
const SUPPORTED_VERSIONS = ">=3.21.0 <6";
const ORIGIN = "auto.http.otel.fastify";
const HOOK_OP = "hook.fastify";
const REQUEST_HANDLER_OP = "request_handler.fastify";
const FASTIFY_HOOKS = [
  "onRequest",
  "preParsing",
  "preValidation",
  "preHandler",
  "preSerialization",
  "onSend",
  "onResponse",
  "onError"
];
const ATTRIBUTE_HOOK_NAME = "hook.name";
const ATTRIBUTE_FASTIFY_TYPE = "fastify.type";
const ATTRIBUTE_HOOK_CALLBACK_NAME = "hook.callback.name";
const ATTRIBUTE_FASTIFY_ROOT = "fastify.root";
const HOOK_TYPE_ROUTE = "route-hook";
const HOOK_TYPE_INSTANCE = "hook";
const HOOK_TYPE_HANDLER = "request-handler";
const ANONYMOUS_FUNCTION_NAME = "anonymous";
const kRequestSpan = /* @__PURE__ */ Symbol("sentry fastify request span");
const kAddHookOriginal = /* @__PURE__ */ Symbol("sentry fastify addHook original");
const kSetNotFoundOriginal = /* @__PURE__ */ Symbol("sentry fastify setNotFoundHandler original");
function getRequestRouteUrl(request) {
  return request.routeOptions?.url ?? request.routerPath;
}
function getRequestRouteConfig(request) {
  return request.routeOptions?.config ?? request.routeConfig;
}
function isFastifyRequest(arg) {
  return isObjectLike(arg) && !!arg.method && !!arg.url && (!!arg.routeOptions || "routerPath" in arg);
}
function fastifyOtelPlugin(instance, _opts, done) {
  instance.decorate(kAddHookOriginal, instance.addHook);
  instance.decorate(kSetNotFoundOriginal, instance.setNotFoundHandler);
  instance.decorateRequest("opentelemetry", function opentelemetry() {
    return { span: this[kRequestSpan] };
  });
  instance.decorateRequest(kRequestSpan, null);
  instance.addHook("onRoute", otelWireRoute);
  instance.addHook("onRequest", startRequestSpanHook);
  instance.addHook("onResponse", finalizeNotFoundSpanHook);
  instance.addHook = addHookPatched;
  instance.setNotFoundHandler = setNotFoundHandlerPatched;
  done();
}
const pluginSymbols = fastifyOtelPlugin;
pluginSymbols[/* @__PURE__ */ Symbol.for("skip-override")] = true;
pluginSymbols[/* @__PURE__ */ Symbol.for("fastify.display-name")] = PACKAGE_NAME;
pluginSymbols[/* @__PURE__ */ Symbol.for("plugin-meta")] = {
  fastify: SUPPORTED_VERSIONS,
  name: PACKAGE_NAME
};
function otelWireRoute(routeOptions) {
  if (routeOptions.config?.otel === false) {
    return;
  }
  for (const hook of FASTIFY_HOOKS) {
    const handlerLike = routeOptions[hook];
    if (typeof handlerLike === "function") {
      routeOptions[hook] = handlerWrapper(
        handlerLike,
        hook,
        routeHookAttributes(this.pluginName, hook, handlerLike, routeOptions.url)
      );
    } else if (Array.isArray(handlerLike)) {
      routeOptions[hook] = handlerLike.map(
        (handler) => handlerWrapper(handler, hook, routeHookAttributes(this.pluginName, hook, handler, routeOptions.url))
      );
    }
  }
  routeOptions.onSend = appendRouteHook(routeOptions.onSend, finalizeResponseSpanHook);
  routeOptions.onError = appendRouteHook(routeOptions.onError, recordErrorInSpanHook);
  routeOptions.handler = handlerWrapper(routeOptions.handler, "handler", {
    [ATTRIBUTE_HOOK_NAME]: `${this.pluginName} - route-handler`,
    [ATTRIBUTE_FASTIFY_TYPE]: HOOK_TYPE_HANDLER,
    [HTTP_ROUTE]: routeOptions.url,
    [ATTRIBUTE_HOOK_CALLBACK_NAME]: routeOptions.handler.name.length > 0 ? routeOptions.handler.name : ANONYMOUS_FUNCTION_NAME
  });
}
function routeHookAttributes(pluginName, hook, handler, url) {
  return {
    [ATTRIBUTE_HOOK_NAME]: `${pluginName} - route -> ${hook}`,
    [ATTRIBUTE_FASTIFY_TYPE]: HOOK_TYPE_ROUTE,
    [HTTP_ROUTE]: url,
    [ATTRIBUTE_HOOK_CALLBACK_NAME]: handler.name?.length > 0 ? handler.name : ANONYMOUS_FUNCTION_NAME
  };
}
function appendRouteHook(existing, hook) {
  if (existing == null) {
    return hook;
  }
  return Array.isArray(existing) ? [...existing, hook] : [existing, hook];
}
function startRequestSpanHook(request, _reply, hookDone) {
  if (getRequestRouteConfig(request)?.otel === false) {
    return hookDone();
  }
  const attributes = {
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
    [ATTRIBUTE_FASTIFY_ROOT]: PACKAGE_NAME,
    [HTTP_REQUEST_METHOD]: request.method,
    [URL_PATH]: request.url
  };
  const route = getRequestRouteUrl(request);
  if (route != null) {
    attributes[HTTP_ROUTE] = route;
    const activeSpan = getActiveSpan();
    const rootSpan = activeSpan && getRootSpan(activeSpan);
    if (rootSpan && spanToJSON(rootSpan).data[SEMANTIC_ATTRIBUTE_SENTRY_OP] === "http.server") {
      rootSpan.setAttribute(HTTP_ROUTE, route);
    }
  }
  const requestSpan = startInactiveSpan({ name: "request", op: REQUEST_HANDLER_OP, attributes });
  request[kRequestSpan] = requestSpan;
  withActiveSpan(requestSpan, () => {
    hookDone();
  });
}
function finalizeNotFoundSpanHook(request, reply, hookDone) {
  const span = request[kRequestSpan];
  if (span != null) {
    span.setAttributes({ [HTTP_RESPONSE_STATUS_CODE]: reply.statusCode });
    span.end();
  }
  request[kRequestSpan] = null;
  hookDone();
}
function finalizeResponseSpanHook(request, reply, payload, hookDone) {
  const span = request[kRequestSpan];
  if (span != null) {
    if (reply.statusCode >= 500) {
      span.setStatus({ code: SPAN_STATUS_ERROR });
    }
    span.setAttributes({ [HTTP_RESPONSE_STATUS_CODE]: reply.statusCode });
    span.end();
  }
  request[kRequestSpan] = null;
  hookDone(null, payload);
}
function recordErrorInSpanHook(request, _reply, error, hookDone) {
  const span = request[kRequestSpan];
  if (span != null) {
    span.setStatus({ code: SPAN_STATUS_ERROR, message: error.message });
  }
  hookDone();
}
function addHookPatched(name, hook) {
  const addHookOriginal = this[kAddHookOriginal];
  if (FASTIFY_HOOKS.includes(name)) {
    return addHookOriginal.call(
      this,
      name,
      handlerWrapper(hook, name, {
        [ATTRIBUTE_HOOK_NAME]: `${this.pluginName} - ${name}`,
        [ATTRIBUTE_FASTIFY_TYPE]: HOOK_TYPE_INSTANCE,
        [ATTRIBUTE_HOOK_CALLBACK_NAME]: hook.name?.length > 0 ? hook.name : ANONYMOUS_FUNCTION_NAME
      })
    );
  }
  return addHookOriginal.call(this, name, hook);
}
function setNotFoundHandlerPatched(hooks, handler) {
  const setNotFoundHandlerOriginal = this[kSetNotFoundOriginal];
  if (typeof hooks === "function") {
    setNotFoundHandlerOriginal.call(
      this,
      handlerWrapper(hooks, "notFoundHandler", {
        [ATTRIBUTE_HOOK_NAME]: `${this.pluginName} - not-found-handler`,
        [ATTRIBUTE_FASTIFY_TYPE]: HOOK_TYPE_INSTANCE,
        [ATTRIBUTE_HOOK_CALLBACK_NAME]: hooks.name?.length > 0 ? hooks.name : ANONYMOUS_FUNCTION_NAME
      })
    );
    return;
  }
  if (hooks.preValidation != null) {
    hooks.preValidation = handlerWrapper(hooks.preValidation, "notFoundHandler - preValidation", {
      [ATTRIBUTE_HOOK_NAME]: `${this.pluginName} - not-found-handler - preValidation`,
      [ATTRIBUTE_FASTIFY_TYPE]: HOOK_TYPE_INSTANCE,
      [ATTRIBUTE_HOOK_CALLBACK_NAME]: hooks.preValidation.name?.length > 0 ? hooks.preValidation.name : ANONYMOUS_FUNCTION_NAME
    });
  }
  if (hooks.preHandler != null) {
    hooks.preHandler = handlerWrapper(hooks.preHandler, "notFoundHandler - preHandler", {
      [ATTRIBUTE_HOOK_NAME]: `${this.pluginName} - not-found-handler - preHandler`,
      [ATTRIBUTE_FASTIFY_TYPE]: HOOK_TYPE_INSTANCE,
      [ATTRIBUTE_HOOK_CALLBACK_NAME]: hooks.preHandler.name?.length > 0 ? hooks.preHandler.name : ANONYMOUS_FUNCTION_NAME
    });
  }
  if (handler == null) {
    setNotFoundHandlerOriginal.call(this, hooks);
    return;
  }
  setNotFoundHandlerOriginal.call(
    this,
    hooks,
    handlerWrapper(handler, "notFoundHandler", {
      [ATTRIBUTE_HOOK_NAME]: `${this.pluginName} - not-found-handler`,
      [ATTRIBUTE_FASTIFY_TYPE]: HOOK_TYPE_INSTANCE,
      [ATTRIBUTE_HOOK_CALLBACK_NAME]: handler.name?.length > 0 ? handler.name : ANONYMOUS_FUNCTION_NAME
    })
  );
}
function getRequestFromArgs(args) {
  for (const arg of args) {
    if (isFastifyRequest(arg)) {
      return arg;
    }
  }
  return null;
}
function handlerWrapper(handler, hookName, spanAttributes = {}) {
  return function handlerWrapped(...args) {
    const request = getRequestFromArgs(args);
    if (request === null || getRequestRouteConfig(request)?.otel === false) {
      return handler.call(this, ...args);
    }
    const parentSpan = request[kRequestSpan] ?? void 0;
    const handlerName = handler.name?.length > 0 ? handler.name : this.pluginName ?? ANONYMOUS_FUNCTION_NAME;
    const hookType = spanAttributes[ATTRIBUTE_FASTIFY_TYPE];
    const op = hookType === HOOK_TYPE_INSTANCE ? HOOK_OP : hookType === HOOK_TYPE_HANDLER ? REQUEST_HANDLER_OP : void 0;
    const name = op ? stripFastifyPrefix(spanAttributes[ATTRIBUTE_HOOK_NAME]) : `${hookName} - ${handlerName}`;
    return startSpan(
      {
        name,
        op,
        attributes: {
          ...spanAttributes,
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        },
        parentSpan
      },
      () => handler.call(this, ...args)
    );
  };
}
function stripFastifyPrefix(hookName = "") {
  return hookName.replace(/^fastify -> /, "").replace(/^@fastify\/otel -> /, "").replace(/^@sentry\/instrumentation-fastify -> /, "");
}
function instrumentOnRequest(fastify) {
  fastify.addHook("onRequest", async (request, _reply) => {
    const routeName = getRequestRouteUrl(request);
    const method = request.method || "GET";
    getIsolationScope().setTransactionName(`${method} ${routeName}`);
  });
}
let _isInstrumented = false;
const instrumentFastify = Object.assign(
  function instrumentFastify2() {
    if (_isInstrumented) {
      return;
    }
    _isInstrumented = true;
    diagnosticsChannel.subscribe("fastify.initialization", (message) => {
      const fastifyInstance = message.fastify;
      fastifyInstance?.register(fastifyOtelPlugin).after((err) => {
        if (err) {
          DEBUG_BUILD && debug.error("Failed to setup Fastify instrumentation", err);
        } else if (fastifyInstance) {
          instrumentOnRequest(fastifyInstance);
        }
      });
    });
  },
  { id: "Fastify.v5" }
);

export { instrumentFastify };
//# sourceMappingURL=instrumentation.js.map
