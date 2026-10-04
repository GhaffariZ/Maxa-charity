import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, debug, waitForTracingChannelBinding, getActiveSpan, SPAN_STATUS_ERROR, truncate, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { DB_STATEMENT, NET_TRANSPORT, NET_PEER_PORT, NET_PEER_NAME, DB_USER, DB_SYSTEM, DB_NAME, DB_OPERATION } from '@sentry/conventions/attributes';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "Knex";
const ORIGIN = "auto.db.orchestrion.knex";
const MAX_QUERY_LENGTH = 1021;
const ATTR_DB_SQL_TABLE = "db.sql.table";
const DB_SYSTEM_SQLITE = "sqlite";
const DB_SYSTEM_POSTGRESQL = "postgresql";
const parentSpanSymbol = /* @__PURE__ */ Symbol("sentry.orchestrion.knex.parent-span");
const _knexChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log(`[orchestrion:knex] subscribing to channel "${CHANNELS.KNEX_QUERY}"`);
      waitForTracingChannelBinding(() => {
        subscribeBuilder(CHANNELS.KNEX_QUERY_BUILDER);
        subscribeBuilder(CHANNELS.KNEX_SCHEMA_BUILDER);
        subscribeBuilder(CHANNELS.KNEX_RAW);
        subscribeQuery();
      });
    }
  };
});
function subscribeBuilder(channelName) {
  diagnosticsChannel.tracingChannel(channelName).end.subscribe((message) => {
    const builder = message.result;
    if (!builder || typeof builder !== "object" || parentSpanSymbol in builder) {
      return;
    }
    const activeSpan = getActiveSpan();
    if (!activeSpan) {
      return;
    }
    Object.defineProperty(builder, parentSpanSymbol, { value: activeSpan });
  });
}
function subscribeQuery() {
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(CHANNELS.KNEX_QUERY),
    (data) => {
      const runner = data.self;
      const builder = runner?.builder;
      const parentSpan = builder?.[parentSpanSymbol] ?? getActiveSpan();
      if (!parentSpan) {
        return void 0;
      }
      const query = data.arguments[0];
      const client = runner?.client;
      const connection = client?.config?.connection;
      const connectionString = connection?.connectionString;
      const table = extractTableName(builder);
      const operation = query?.method;
      const name = connection?.filename || connection?.database || extractDatabaseFromConnectionString(connectionString);
      const attributes = {
        [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        "knex.version": data.moduleVersion,
        [DB_SYSTEM]: mapSystem(client?.driverName),
        [ATTR_DB_SQL_TABLE]: table,
        [DB_OPERATION]: operation,
        [DB_USER]: connection?.user,
        [DB_NAME]: name,
        [NET_PEER_NAME]: connection?.host ?? extractHostFromConnectionString(connectionString),
        [NET_PEER_PORT]: connection?.port ?? extractPortFromConnectionString(connectionString),
        [NET_TRANSPORT]: connection?.filename === ":memory:" ? "inproc" : void 0,
        [DB_STATEMENT]: query?.sql != null ? truncate(query.sql, MAX_QUERY_LENGTH) : void 0
      };
      return startInactiveSpan({
        name: getName(name, operation, table) ?? "knex.query",
        kind: SPAN_KIND.CLIENT,
        op: "db",
        parentSpan,
        attributes
      });
    },
    {
      beforeSpanEnd(span, data) {
        if ("error" in data) {
          const message = cleanErrorMessage(data);
          if (message !== void 0) {
            span.setStatus({ code: SPAN_STATUS_ERROR, message });
          }
        }
      }
    }
  );
}
function cleanErrorMessage(data) {
  const error = data.error;
  if (!error || typeof error !== "object" || typeof error.message !== "string") {
    return void 0;
  }
  const rawMessage = error.message;
  const query = data.arguments[0];
  if (!query?.sql) {
    return rawMessage;
  }
  try {
    const formatter = getFormatter(data.self);
    const fullQuery = formatter(query.sql, query.bindings || []);
    return rawMessage.replace(`${fullQuery} - `, "");
  } catch {
    return rawMessage;
  }
}
function getFormatter(runner) {
  if (runner) {
    const client = runner.client;
    if (client) {
      if (client._formatQuery) {
        return client._formatQuery.bind(client);
      } else if (client.SqlString) {
        return client.SqlString.format.bind(client.SqlString);
      }
    }
    if (runner.builder?.toString) {
      return runner.builder.toString.bind(runner.builder);
    }
  }
  return () => "<noop formatter>";
}
function mapSystem(driverName) {
  if (driverName === "sqlite3") {
    return DB_SYSTEM_SQLITE;
  }
  if (driverName === "pg") {
    return DB_SYSTEM_POSTGRESQL;
  }
  return driverName;
}
function getName(db, operation, table) {
  if (operation && db) {
    return table ? `${operation} ${db}.${table}` : `${operation} ${db}`;
  }
  return db;
}
function extractTableName(builder) {
  const table = builder?._single?.table;
  if (table && typeof table === "object") {
    return extractTableName(table);
  }
  return typeof table === "string" ? table : void 0;
}
function extractDatabaseFromConnectionString(connectionString) {
  if (!connectionString) {
    return void 0;
  }
  try {
    const db = new URL(connectionString).pathname?.replace(/^\//, "");
    return db || void 0;
  } catch {
    return void 0;
  }
}
function extractHostFromConnectionString(connectionString) {
  if (!connectionString) {
    return void 0;
  }
  try {
    return new URL(connectionString).hostname || void 0;
  } catch {
    return void 0;
  }
}
function extractPortFromConnectionString(connectionString) {
  if (!connectionString) {
    return void 0;
  }
  try {
    const port = new URL(connectionString).port;
    return port ? parseInt(port, 10) : void 0;
  } catch {
    return void 0;
  }
}
const knexChannelIntegration = defineIntegration(_knexChannelIntegration);

export { knexChannelIntegration };
//# sourceMappingURL=knex.js.map
