Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const subscribeInjection = require('./subscribe-injection.js');

const MODULE_NAME = "knex";
const RUNNER_FILES = [
  { filePath: "lib/execution/runner.js", versionRange: ">=0.22.0 <4" },
  { filePath: "lib/runner.js", versionRange: ">=0.10.0 <0.22.0" },
  { filePath: "src/runner.js", versionRange: ">=0.18.0 <0.19.0" }
];
const CLIENT_FILES = [
  { filePath: "lib/client.js", versionRange: ">=0.10.0 <4" },
  { filePath: "src/client.js", versionRange: ">=0.18.0 <0.19.0" }
];
const CLIENT_METHODS = ["queryBuilder", "schemaBuilder", "raw"];
function runnerQuery(filePath, versionRange) {
  return {
    channelName: "query",
    module: { name: MODULE_NAME, versionRange, filePath },
    functionQuery: { className: "Runner", methodName: "query", kind: "Async" }
  };
}
function clientMethod(methodName, filePath, versionRange) {
  return {
    channelName: methodName,
    module: { name: MODULE_NAME, versionRange, filePath },
    functionQuery: { className: "Client", methodName, kind: "Sync" }
  };
}
const knexConfig = [
  ...RUNNER_FILES.map(({ filePath, versionRange }) => runnerQuery(filePath, versionRange)),
  ...CLIENT_FILES.flatMap(
    ({ filePath, versionRange }) => CLIENT_METHODS.map((methodName) => clientMethod(methodName, filePath, versionRange))
  )
];
const knexChannels = {
  KNEX_QUERY: "orchestrion:knex:query",
  KNEX_QUERY_BUILDER: "orchestrion:knex:queryBuilder",
  KNEX_SCHEMA_BUILDER: "orchestrion:knex:schemaBuilder",
  KNEX_RAW: "orchestrion:knex:raw"
};
const knexSubscribeInjection = subscribeInjection.toSubscribeInjections(knexConfig);

exports.knexChannels = knexChannels;
exports.knexConfig = knexConfig;
exports.knexSubscribeInjection = knexSubscribeInjection;
//# sourceMappingURL=knex.js.map
