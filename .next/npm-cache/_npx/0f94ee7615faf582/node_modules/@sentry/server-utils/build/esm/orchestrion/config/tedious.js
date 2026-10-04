import { toSubscribeInjections } from './subscribe-injection.js';

const MODULE_NAME = "tedious";
const FILE_PATH = "lib/connection.js";
const VERSION_RANGE = ">=1.11.0 <20";
const METHODS = ["connect", "execSql", "execSqlBatch", "callProcedure", "execBulkLoad", "prepare", "execute"];
const tediousConfig = METHODS.map((methodName) => ({
  channelName: methodName,
  module: { name: MODULE_NAME, versionRange: VERSION_RANGE, filePath: FILE_PATH },
  functionQuery: { className: "Connection", methodName, kind: "Sync" }
}));
const tediousChannels = {
  TEDIOUS_CONNECT: "orchestrion:tedious:connect",
  TEDIOUS_EXEC_SQL: "orchestrion:tedious:execSql",
  TEDIOUS_EXEC_SQL_BATCH: "orchestrion:tedious:execSqlBatch",
  TEDIOUS_CALL_PROCEDURE: "orchestrion:tedious:callProcedure",
  TEDIOUS_EXEC_BULK_LOAD: "orchestrion:tedious:execBulkLoad",
  TEDIOUS_PREPARE: "orchestrion:tedious:prepare",
  TEDIOUS_EXECUTE: "orchestrion:tedious:execute"
};
const tediousSubscribeInjection = toSubscribeInjections(tediousConfig);

export { tediousChannels, tediousConfig, tediousSubscribeInjection };
//# sourceMappingURL=tedious.js.map
