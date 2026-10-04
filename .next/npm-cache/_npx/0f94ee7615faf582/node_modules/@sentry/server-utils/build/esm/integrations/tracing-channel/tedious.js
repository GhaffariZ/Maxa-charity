import { EventEmitter } from 'node:events';
import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, debug, waitForTracingChannelBinding, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SPAN_STATUS_ERROR } from '@sentry/core';
import { NET_PEER_PORT, NET_PEER_NAME, DB_USER, DB_STATEMENT, DB_NAME, DB_SYSTEM } from '@sentry/conventions/attributes';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';

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
  diagnosticsChannel.tracingChannel(CHANNELS.TEDIOUS_CONNECT).start.subscribe((message) => {
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
  diagnosticsChannel.tracingChannel(channelName).start.subscribe((message) => {
    const data = message;
    const connection = data.self;
    const request = data.arguments[0];
    if (!connection || !(request instanceof EventEmitter)) {
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
    const attributes = {
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
      [DB_SYSTEM]: DB_SYSTEM_VALUE_MSSQL,
      [DB_NAME]: databaseName,
      // `>=4` uses the `authentication` object; older versions expose `userName` directly.
      [DB_USER]: connection.config?.userName ?? connection.config?.authentication?.options?.userName,
      [DB_STATEMENT]: sql,
      [ATTR_DB_SQL_TABLE]: request.table,
      [NET_PEER_NAME]: connection.config?.server,
      [NET_PEER_PORT]: connection.config?.options?.port
    };
    const span = startInactiveSpan({
      name: getSpanName(operation, databaseName, sql, request.table),
      kind: SPAN_KIND.CLIENT,
      op: "db",
      attributes
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
        span.setStatus({ code: SPAN_STATUS_ERROR, message: err.message });
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
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log(`[orchestrion:tedious] subscribing to channel "${CHANNELS.TEDIOUS_EXEC_SQL}"`);
      waitForTracingChannelBinding(() => {
        subscribeConnect();
        subscribeQuery(CHANNELS.TEDIOUS_EXEC_SQL, "execSql");
        subscribeQuery(CHANNELS.TEDIOUS_EXEC_SQL_BATCH, "execSqlBatch");
        subscribeQuery(CHANNELS.TEDIOUS_CALL_PROCEDURE, "callProcedure");
        subscribeQuery(CHANNELS.TEDIOUS_EXEC_BULK_LOAD, "execBulkLoad");
        subscribeQuery(CHANNELS.TEDIOUS_PREPARE, "prepare");
        subscribeQuery(CHANNELS.TEDIOUS_EXECUTE, "execute");
      });
    }
  };
});
const tediousChannelIntegration = defineIntegration(_tediousChannelIntegration);

export { tediousChannelIntegration };
//# sourceMappingURL=tedious.js.map
