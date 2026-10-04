Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const node_events = require('node:events');
const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const debugBuild = require('../../debug-build.js');
const channels = require('../../orchestrion/channels.js');

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

const INTEGRATION_NAME = "Tedious";
const ORIGIN = "auto.db.orchestrion.tedious";
const DB_SYSTEM_VALUE_MSSQL = "mssql";
const ATTR_DB_SQL_TABLE = "db.sql.table";
const currentDatabaseSymbol = /* @__PURE__ */ Symbol("sentry.orchestrion.tedious.current-database");
function setDatabase(databaseName) {
  Object.defineProperty(this, currentDatabaseSymbol, { value: databaseName, writable: true, configurable: true });
}
function removeDatabaseListener() {
  this.removeListener("databaseChange", setDatabase);
}
function subscribeConnect() {
  diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.TEDIOUS_CONNECT).start.subscribe((message) => {
    const connection = message.self;
    if (!connection) {
      return;
    }
    setDatabase.call(connection, connection.config?.options?.database);
    connection.removeListener("databaseChange", setDatabase);
    connection.on("databaseChange", setDatabase);
    connection.removeListener("end", removeDatabaseListener);
    connection.once("end", removeDatabaseListener);
  });
}
function subscribeQuery(channelName, operation) {
  diagnosticsChannel__namespace.tracingChannel(channelName).start.subscribe((message) => {
    const data = message;
    const connection = data.self;
    const request = data.arguments[0];
    if (!connection || !(request instanceof node_events.EventEmitter)) {
      return;
    }
    let procCount = 0;
    let statementCount = 0;
    const incrementStatementCount = () => {
      statementCount++;
    };
    const incrementProcCount = () => {
      procCount++;
    };
    const databaseName = connection[currentDatabaseSymbol];
    const sql = extractSql(request);
    const attributes$1 = {
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
      [attributes.DB_SYSTEM]: DB_SYSTEM_VALUE_MSSQL,
      [attributes.DB_NAME]: databaseName,
      // `>=4` uses the `authentication` object; older versions expose `userName` directly.
      [attributes.DB_USER]: connection.config?.userName ?? connection.config?.authentication?.options?.userName,
      [attributes.DB_STATEMENT]: sql,
      [ATTR_DB_SQL_TABLE]: request.table,
      [attributes.NET_PEER_NAME]: connection.config?.server,
      [attributes.NET_PEER_PORT]: connection.config?.options?.port
    };
    const span = core.startInactiveSpan({
      name: getSpanName(operation, databaseName, sql, request.table),
      kind: core.SPAN_KIND.CLIENT,
      op: "db",
      attributes: attributes$1
    });
    const endSpan = once((err) => {
      request.removeListener("done", incrementStatementCount);
      request.removeListener("doneInProc", incrementStatementCount);
      request.removeListener("doneProc", incrementProcCount);
      request.removeListener("error", endSpan);
      connection.removeListener("end", endSpan);
      span.setAttribute("tedious.procedure_count", procCount);
      span.setAttribute("tedious.statement_count", statementCount);
      if (err) {
        span.setStatus({ code: core.SPAN_STATUS_ERROR, message: err.message });
      }
      span.end();
    });
    request.on("done", incrementStatementCount);
    request.on("doneInProc", incrementStatementCount);
    request.on("doneProc", incrementProcCount);
    request.once("error", endSpan);
    connection.on("end", endSpan);
    if (typeof request.callback === "function") {
      const originalCallback = request.callback;
      request.callback = function(...args) {
        endSpan(args[0]);
        return originalCallback.apply(this, args);
      };
    }
  });
}
function extractSql(request) {
  if (request.sqlTextOrProcedure === "sp_prepare" && request.parametersByName?.stmt?.value != null) {
    const value = request.parametersByName.stmt.value;
    return typeof value === "string" ? value : void 0;
  }
  return request.sqlTextOrProcedure;
}
function getSpanName(operation, db, sql, bulkLoadTable) {
  if (operation === "execBulkLoad" && bulkLoadTable && db) {
    return `${operation} ${bulkLoadTable} ${db}`;
  }
  if (operation === "callProcedure") {
    return db ? `${operation} ${sql} ${db}` : `${operation} ${sql}`;
  }
  return db ? `${operation} ${db}` : operation;
}
function once(fn) {
  let called = false;
  return (...args) => {
    if (called) {
      return;
    }
    called = true;
    fn(...args);
  };
}
const _tediousChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:tedious] subscribing to channel "${channels.CHANNELS.TEDIOUS_EXEC_SQL}"`);
      core.waitForTracingChannelBinding(() => {
        subscribeConnect();
        subscribeQuery(channels.CHANNELS.TEDIOUS_EXEC_SQL, "execSql");
        subscribeQuery(channels.CHANNELS.TEDIOUS_EXEC_SQL_BATCH, "execSqlBatch");
        subscribeQuery(channels.CHANNELS.TEDIOUS_CALL_PROCEDURE, "callProcedure");
        subscribeQuery(channels.CHANNELS.TEDIOUS_EXEC_BULK_LOAD, "execBulkLoad");
        subscribeQuery(channels.CHANNELS.TEDIOUS_PREPARE, "prepare");
        subscribeQuery(channels.CHANNELS.TEDIOUS_EXECUTE, "execute");
      });
    }
  };
});
const tediousChannelIntegration = core.defineIntegration(_tediousChannelIntegration);

exports.tediousChannelIntegration = tediousChannelIntegration;
//# sourceMappingURL=tedious.js.map
