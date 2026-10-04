import * as diagnosticsChannel from 'node:diagnostics_channel';
import { DB_QUERY_TEXT, DB_SYSTEM_NAME, ERROR_TYPE } from '@sentry/conventions/attributes';
import { defineIntegration, debug, waitForTracingChannelBinding, _INTERNAL_reconstructPostgresQuery, _INTERNAL_sanitizeSqlQuery, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, _INTERNAL_buildPostgresConnectionContext, _INTERNAL_setPostgresOperationName, SPAN_STATUS_ERROR, _INTERNAL_setPostgresConnectionAttributes } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "PostgresJs";
const ORIGIN = "auto.db.orchestrion.postgresjs";
const DB_RESPONSE_STATUS_CODE = "db.response.status_code";
const NOOP = () => {
};
const QUERY_FROM_INSTRUMENTED_SQL = /* @__PURE__ */ Symbol.for("sentry.query.from.instrumented.sql");
const QUERY_SPAN = /* @__PURE__ */ Symbol("sentryPostgresJsSpan");
const CONNECTION_ATTRS_SET = /* @__PURE__ */ Symbol("sentryPostgresJsConnectionAttrsSet");
const SPAN_ENDED = /* @__PURE__ */ Symbol("sentryPostgresJsSpanEnded");
const connectionContexts = /* @__PURE__ */ new WeakMap();
const endpointRegistry = [];
function registerEndpoint(context) {
  const alreadyKnown = endpointRegistry.some(
    (e) => e.ATTR_SERVER_ADDRESS === context.ATTR_SERVER_ADDRESS && e.ATTR_SERVER_PORT === context.ATTR_SERVER_PORT && e.ATTR_DB_NAMESPACE === context.ATTR_DB_NAMESPACE
  );
  if (!alreadyKnown) {
    endpointRegistry.push(context);
  }
}
function resolveSingleEndpoint() {
  return endpointRegistry.length === 1 ? endpointRegistry[0] : void 0;
}
function recordConnectionFromChannel(message) {
  const connection = message.result;
  const options = message.arguments?.[0];
  if (!connection || typeof connection !== "object" || !options) {
    return;
  }
  const context = _INTERNAL_buildPostgresConnectionContext(options);
  connectionContexts.set(connection, context);
  registerEndpoint(context);
}
function setConnectionAttributes(span, query, context) {
  const queryRecord = query;
  if (queryRecord[CONNECTION_ATTRS_SET]) {
    return;
  }
  queryRecord[CONNECTION_ATTRS_SET] = true;
  _INTERNAL_setPostgresConnectionAttributes(span, context);
}
function attachConnectionAttributesFromChannel(message) {
  const connection = message.self;
  const query = message.arguments?.[0];
  if (!connection || !query) {
    return;
  }
  const span = query[QUERY_SPAN];
  const context = connectionContexts.get(connection);
  if (span && context) {
    setConnectionAttributes(span, query, context);
  }
}
function wrapQuerySettlement(data, span, sanitizedSqlQuery) {
  const query = data.self;
  if (!query) {
    return;
  }
  const markEnded = () => {
    data[SPAN_ENDED] = true;
  };
  const originalResolve = query.resolve;
  if (typeof originalResolve === "function") {
    query.resolve = function(...resolveArgs) {
      markEnded();
      try {
        const command = resolveArgs[0]?.command;
        _INTERNAL_setPostgresOperationName(span, sanitizedSqlQuery, command);
        span.end();
      } catch (e) {
        DEBUG_BUILD && debug.error("[orchestrion:postgresjs] error ending span in resolve:", e);
      }
      return originalResolve.apply(this, resolveArgs);
    };
  }
  const originalReject = query.reject;
  if (typeof originalReject === "function") {
    query.reject = function(...rejectArgs) {
      markEnded();
      try {
        const err = rejectArgs[0];
        span.setStatus({ code: SPAN_STATUS_ERROR, message: err?.message || "unknown_error" });
        span.setAttribute(DB_RESPONSE_STATUS_CODE, err?.code || "unknown");
        span.setAttribute(ERROR_TYPE, err?.name || "unknown");
        _INTERNAL_setPostgresOperationName(span, sanitizedSqlQuery);
        span.end();
      } catch (e) {
        DEBUG_BUILD && debug.error("[orchestrion:postgresjs] error ending span in reject:", e);
      }
      return originalReject.apply(this, rejectArgs);
    };
  }
}
const _postgresJsChannelIntegration = ((options = {}) => {
  const { requireParentSpan, requestHook } = options;
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log(`[orchestrion:postgresjs] subscribing to "${CHANNELS.POSTGRESJS_HANDLE}"`);
      diagnosticsChannel.tracingChannel(CHANNELS.POSTGRESJS_CONNECTION).subscribe({
        start: NOOP,
        asyncStart: NOOP,
        asyncEnd: NOOP,
        error: NOOP,
        end: recordConnectionFromChannel
      });
      diagnosticsChannel.tracingChannel(CHANNELS.POSTGRESJS_EXECUTE).subscribe({
        end: NOOP,
        asyncStart: NOOP,
        asyncEnd: NOOP,
        error: NOOP,
        start: attachConnectionAttributesFromChannel
      });
      diagnosticsChannel.tracingChannel(CHANNELS.POSTGRESJS_CONNECT).subscribe({
        end: NOOP,
        asyncStart: NOOP,
        asyncEnd: NOOP,
        error: NOOP,
        start: attachConnectionAttributesFromChannel
      });
      waitForTracingChannelBinding(() => {
        bindTracingChannelToSpan(
          diagnosticsChannel.tracingChannel(CHANNELS.POSTGRESJS_HANDLE),
          (data) => {
            const query = data.self;
            if (!query) {
              return void 0;
            }
            if (query.executed === true || query[QUERY_FROM_INSTRUMENTED_SQL]) {
              return void 0;
            }
            const fullQuery = _INTERNAL_reconstructPostgresQuery(query.strings);
            const sanitizedSqlQuery = _INTERNAL_sanitizeSqlQuery(fullQuery);
            const span = startInactiveSpan({
              name: sanitizedSqlQuery || "postgresjs.query",
              op: "db",
              kind: SPAN_KIND.CLIENT,
              attributes: {
                [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
                [DB_SYSTEM_NAME]: "postgres",
                [DB_QUERY_TEXT]: sanitizedSqlQuery
              }
            });
            query[QUERY_SPAN] = span;
            const context = resolveSingleEndpoint();
            if (context) {
              setConnectionAttributes(span, query, context);
            }
            if (requestHook) {
              try {
                requestHook(span, sanitizedSqlQuery, context);
              } catch (e) {
                span.setAttribute("sentry.hook.error", "requestHook failed");
                DEBUG_BUILD && debug.error("[orchestrion:postgresjs] error in requestHook:", e);
              }
            }
            wrapQuerySettlement(data, span, sanitizedSqlQuery);
            return span;
          },
          {
            requiresParentSpan: requireParentSpan !== false,
            deferSpanEnd({ data }) {
              if (data[SPAN_ENDED]) {
                return true;
              }
              if ("error" in data) {
                return false;
              }
              return true;
            }
          }
        );
      });
    }
  };
});
const postgresJsChannelIntegration = defineIntegration(_postgresJsChannelIntegration);

export { postgresJsChannelIntegration };
//# sourceMappingURL=postgres-js.js.map
