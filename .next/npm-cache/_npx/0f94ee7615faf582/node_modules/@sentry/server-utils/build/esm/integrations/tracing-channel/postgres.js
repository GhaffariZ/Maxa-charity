import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding, debug, bindScopeToEmitter, getCurrentScope, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, isObjectLike } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "Postgres";
const ORIGIN = "auto.db.orchestrion.postgres";
const ATTR_DB_SYSTEM = "db.system";
const ATTR_DB_NAME = "db.name";
const ATTR_DB_CONNECTION_STRING = "db.connection_string";
const ATTR_DB_USER = "db.user";
const ATTR_DB_STATEMENT = "db.statement";
const ATTR_NET_PEER_NAME = "net.peer.name";
const ATTR_NET_PEER_PORT = "net.peer.port";
const ATTR_PG_PLAN = "db.postgresql.plan";
const ATTR_PG_IDLE_TIMEOUT = "db.postgresql.idle.timeout.millis";
const ATTR_PG_MAX_CLIENT = "db.postgresql.max.client";
const DB_SYSTEM_POSTGRESQL = "postgresql";
const SPAN_QUERY_FALLBACK = "pg.query";
const SPAN_CONNECT = "pg.connect";
const SPAN_POOL_CONNECT = "pg-pool.connect";
const _postgresChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeQueryLikeChannel(CHANNELS.PG_QUERY, querySpanOptions, { deferStreamedResult: true });
        if (!options.ignoreConnectSpans) {
          subscribeQueryLikeChannel(CHANNELS.PG_CONNECT, connectSpanOptions);
          subscribeQueryLikeChannel(CHANNELS.PGPOOL_CONNECT, poolConnectSpanOptions);
        }
      });
    }
  };
});
function subscribeQueryLikeChannel(channelName, getSpanOptions, { deferStreamedResult = false } = {}) {
  DEBUG_BUILD && debug.log(`[orchestrion:pg] subscribing to channel "${channelName}"`);
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(channelName),
    (data) => {
      data._sentryCallerScope = getCurrentScope();
      return startInactiveSpan({ ...getSpanOptions(data), kind: SPAN_KIND.CLIENT });
    },
    // `connect`/`pool-connect` resolve with a persistent `Client` (itself an
    // `EventEmitter`), which is NOT a streamed result. Deferring their span
    // to that emitter's `'end'`/`'error'` would keep it open for the whole
    // connection lifetime, so it never ends in time and is dropped. Only
    // `query` can return a streamable `Submittable`, so only it defers.
    deferStreamedResult ? {
      // Only instrument under an active span, leaving the context untouched otherwise
      // (e.g. connects issued during app startup).
      requiresParentSpan: true,
      // Streamable `Submittable` (e.g. `client.query(new Query())`)
      // returns an emitter that orchestrion stores on `ctx.result` while
      // firing no async events; the query isn't done until the emitter
      // emits `'end'`/`'error'`. Defer ending to those events for that
      // path; the callback, promise, and sync-throw paths carry no
      // emitter, so the helper ends the span as usual.
      deferSpanEnd({ data, end }) {
        const result = data.result;
        if (!result || typeof result !== "object" || !hasOnMethod(result)) {
          return false;
        }
        const callerScope = data._sentryCallerScope;
        if (callerScope) {
          bindScopeToEmitter(result, callerScope);
        }
        result.on("error", (err) => end(err));
        result.on("end", () => end());
        return true;
      }
    } : { requiresParentSpan: true }
  );
}
function querySpanOptions(ctx) {
  const params = ctx.self?.connectionParameters ?? {};
  const queryConfig = extractQueryConfig(ctx.arguments);
  return {
    // The description is the SQL statement
    name: queryConfig?.text ?? SPAN_QUERY_FALLBACK,
    op: "db",
    attributes: {
      ...getConnectionAttributes(params),
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
      [ATTR_DB_STATEMENT]: queryConfig?.text || void 0,
      [ATTR_PG_PLAN]: typeof queryConfig?.name === "string" ? queryConfig.name : void 0
    }
  };
}
function connectSpanOptions(ctx) {
  const params = ctx.self?.connectionParameters ?? {};
  return { name: SPAN_CONNECT, op: "db", attributes: getConnectionAttributes(params) };
}
function poolConnectSpanOptions(ctx) {
  const opts = ctx.self?.options ?? {};
  return { name: SPAN_POOL_CONNECT, op: "db", attributes: getPoolConnectionAttributes(opts) };
}
function hasOnMethod(obj) {
  return "on" in obj && typeof obj.on === "function";
}
function extractQueryConfig(args) {
  const arg0 = args[0];
  if (typeof arg0 === "string") {
    return { text: arg0 };
  }
  if (isObjectLike(arg0) && typeof arg0.text === "string") {
    const obj = arg0;
    return { text: obj.text, name: obj.name };
  }
  return void 0;
}
function getConnectionAttributes(params) {
  return {
    [ATTR_DB_SYSTEM]: DB_SYSTEM_POSTGRESQL,
    [ATTR_DB_CONNECTION_STRING]: getConnectionString(params),
    [ATTR_DB_NAME]: params.database,
    [ATTR_DB_USER]: params.user,
    [ATTR_NET_PEER_NAME]: params.host,
    [ATTR_NET_PEER_PORT]: Number.isInteger(params.port) ? params.port : void 0
  };
}
function getPoolConnectionAttributes(opts) {
  let url;
  try {
    url = opts.connectionString ? new URL(opts.connectionString) : void 0;
  } catch {
    url = void 0;
  }
  const database = url?.pathname.slice(1) || opts.database;
  const host = url?.hostname || opts.host;
  const port = Number(url?.port) || (Number.isInteger(opts.port) ? opts.port : void 0);
  const user = url?.username || opts.user;
  return {
    [ATTR_DB_SYSTEM]: DB_SYSTEM_POSTGRESQL,
    [ATTR_DB_CONNECTION_STRING]: getConnectionString(opts),
    [ATTR_PG_IDLE_TIMEOUT]: opts.idleTimeoutMillis,
    [ATTR_PG_MAX_CLIENT]: opts.max,
    [ATTR_DB_NAME]: database,
    [ATTR_NET_PEER_PORT]: port,
    // these two come from a url parse and slice, can be ''
    [ATTR_NET_PEER_NAME]: host || void 0,
    [ATTR_DB_USER]: user || void 0
  };
}
function getConnectionString(params) {
  if (params.connectionString) {
    try {
      const url = new URL(params.connectionString);
      url.username = "";
      url.password = "";
      return url.toString();
    } catch {
      return "postgresql://localhost:5432/";
    }
  }
  const host = params.host || "localhost";
  const port = params.port || 5432;
  const database = params.database || "";
  return `postgresql://${host}:${port}/${database}`;
}
const postgresChannelIntegration = defineIntegration(_postgresChannelIntegration);

export { postgresChannelIntegration };
//# sourceMappingURL=postgres.js.map
