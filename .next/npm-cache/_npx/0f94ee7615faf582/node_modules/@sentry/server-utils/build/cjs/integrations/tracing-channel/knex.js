Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const debugBuild = require('../../debug-build.js');
const channels = require('../../orchestrion/channels.js');
const tracingChannel = require('../../tracing-channel.js');

function _interopNamespaceDefault(e) {
  const n = Object.create(null, { [Symbol.toStringTag]: { value: 'Module' } });
  if (e) {
    for (const k in e) {
      n[k] = e[k];
    }
  }
  n.default = e;
  return n;
}

const diagnosticsChannel__namespace = /*#__PURE__*/_interopNamespaceDefault(diagnosticsChannel);

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
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:knex] subscribing to channel "${channels.CHANNELS.KNEX_QUERY}"`);
      core.waitForTracingChannelBinding(() => {
        subscribeBuilder(channels.CHANNELS.KNEX_QUERY_BUILDER);
        subscribeBuilder(channels.CHANNELS.KNEX_SCHEMA_BUILDER);
        subscribeBuilder(channels.CHANNELS.KNEX_RAW);
        subscribeQuery();
      });
    }
  };
});
function subscribeBuilder(channelName) {
  diagnosticsChannel__namespace.tracingChannel(channelName).end.subscribe((message) => {
    const builder = message.result;
    if (!builder || typeof builder !== "object" || parentSpanSymbol in builder) {
      return;
    }
    const activeSpan = core.getActiveSpan();
    if (!activeSpan) {
      return;
    }
    Object.defineProperty(builder, parentSpanSymbol, { value: activeSpan });
  });
}
function subscribeQuery() {
  tracingChannel.bindTracingChannelToSpan(
    diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.KNEX_QUERY),
    (data) => {
      const runner = data.self;
      const builder = runner?.builder;
      const parentSpan = builder?.[parentSpanSymbol] ?? core.getActiveSpan();
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
      const attributes$1 = {
        [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        "knex.version": data.moduleVersion,
        [attributes.DB_SYSTEM]: mapSystem(client?.driverName),
        [ATTR_DB_SQL_TABLE]: table,
        [attributes.DB_OPERATION]: operation,
        [attributes.DB_USER]: connection?.user,
        [attributes.DB_NAME]: name,
        [attributes.NET_PEER_NAME]: connection?.host ?? extractHostFromConnectionString(connectionString),
        [attributes.NET_PEER_PORT]: connection?.port ?? extractPortFromConnectionString(connectionString),
        [attributes.NET_TRANSPORT]: connection?.filename === ":memory:" ? "inproc" : void 0,
        [attributes.DB_STATEMENT]: query?.sql != null ? core.truncate(query.sql, MAX_QUERY_LENGTH) : void 0
      };
      return core.startInactiveSpan({
        name: getName(name, operation, table) ?? "knex.query",
        kind: core.SPAN_KIND.CLIENT,
        op: "db",
        parentSpan,
        attributes: attributes$1
      });
    },
    {
      beforeSpanEnd(span, data) {
        if ("error" in data) {
          const message = cleanErrorMessage(data);
          if (message !== void 0) {
            span.setStatus({ code: core.SPAN_STATUS_ERROR, message });
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
const knexChannelIntegration = core.defineIntegration(_knexChannelIntegration);

exports.knexChannelIntegration = knexChannelIntegration;
//# sourceMappingURL=knex.js.map
