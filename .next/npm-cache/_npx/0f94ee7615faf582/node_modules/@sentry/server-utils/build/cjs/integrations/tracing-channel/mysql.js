Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
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

const INTEGRATION_NAME = "Mysql";
const ATTR_DB_SYSTEM = "db.system";
const ATTR_DB_CONNECTION_STRING = "db.connection_string";
const ATTR_DB_NAME = "db.name";
const ATTR_DB_USER = "db.user";
const ATTR_DB_STATEMENT = "db.statement";
const ATTR_NET_PEER_NAME = "net.peer.name";
const ATTR_NET_PEER_PORT = "net.peer.port";
const _mysqlChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:mysql] subscribing to channel "${channels.CHANNELS.MYSQL_QUERY}"`);
      core.waitForTracingChannelBinding(() => {
        tracingChannel.bindTracingChannelToSpan(
          diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.MYSQL_QUERY),
          (data) => {
            const sql = extractSql(data.arguments[0]);
            const { host, port, database, user } = getConnectionConfig(data.self);
            const portNumber = typeof port === "string" ? parseInt(port, 10) : port;
            const portIsNumber = typeof portNumber === "number" && !isNaN(portNumber);
            data._sentryCallerScope = core.getCurrentScope();
            return core.startInactiveSpan({
              name: sql ?? "mysql.query",
              kind: core.SPAN_KIND.CLIENT,
              op: "db",
              attributes: {
                [ATTR_DB_SYSTEM]: "mysql",
                [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.db.orchestrion.mysql",
                [ATTR_DB_CONNECTION_STRING]: getJDBCString(host, portIsNumber ? portNumber : void 0, database),
                ...database ? { [ATTR_DB_NAME]: database } : {},
                ...user ? { [ATTR_DB_USER]: user } : {},
                ...sql ? { [ATTR_DB_STATEMENT]: sql } : {},
                ...host ? { [ATTR_NET_PEER_NAME]: host } : {},
                ...portIsNumber ? { [ATTR_NET_PEER_PORT]: portNumber } : {}
              }
            });
          },
          {
            // No-callback `query(sql)` returns a streamable `Query` emitter as `result`; it settles on the
            // emitter's `'end'`/`'error'`, not the channel, so defer ending to those.
            deferSpanEnd({ data, end }) {
              const result = data.result;
              if (!result || typeof result !== "object" || !hasOnMethod(result)) {
                return false;
              }
              const callerScope = data._sentryCallerScope;
              if (callerScope) {
                core.bindScopeToEmitter(result, callerScope);
              }
              result.on("error", (err) => end(err));
              result.on("end", () => end());
              return true;
            }
          }
        );
      });
    }
  };
});
function hasOnMethod(obj) {
  return "on" in obj && typeof obj.on === "function";
}
function extractSql(firstArg) {
  if (typeof firstArg === "string") {
    return firstArg;
  }
  if (core.isObjectLike(firstArg) && "sql" in firstArg) {
    const sql = firstArg.sql;
    return typeof sql === "string" ? sql : void 0;
  }
  return void 0;
}
function getConnectionConfig(connection) {
  const config = connection?.config?.connectionConfig ?? connection?.config ?? {};
  return {
    host: config.host,
    port: config.port,
    database: config.database,
    user: config.user
  };
}
function getJDBCString(host, port, database) {
  let s = `jdbc:mysql://${host || "localhost"}`;
  if (typeof port === "number") {
    s += `:${port}`;
  }
  if (database) {
    s += `/${database}`;
  }
  return s;
}
const mysqlChannelIntegration = core.defineIntegration(_mysqlChannelIntegration);

exports.mysqlChannelIntegration = mysqlChannelIntegration;
//# sourceMappingURL=mysql.js.map
