Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const semconv = require('./semconv.js');

const getFormatter = (runner) => {
  if (runner) {
    if (runner.client) {
      if (runner.client._formatQuery) {
        return runner.client._formatQuery.bind(runner.client);
      } else if (runner.client.SqlString) {
        return runner.client.SqlString.format.bind(runner.client.SqlString);
      }
    }
    if (runner.builder) {
      return runner.builder.toString.bind(runner.builder);
    }
  }
  return () => "<noop formatter>";
};
const systemMap = /* @__PURE__ */ new Map([
  ["sqlite3", semconv.DB_SYSTEM_NAME_VALUE_SQLITE],
  ["pg", semconv.DB_SYSTEM_NAME_VALUE_POSTGRESQL]
]);
const mapSystem = (knexSystem) => {
  return systemMap.get(knexSystem) || knexSystem;
};
const getName = (db, operation, table) => {
  if (operation) {
    if (table) {
      return `${operation} ${db}.${table}`;
    }
    return `${operation} ${db}`;
  }
  return db;
};
const limitLength = (str, maxLength) => {
  if (typeof str === "string" && typeof maxLength === "number" && 0 < maxLength && maxLength < str.length) {
    return `${str.substring(0, maxLength)}..`;
  }
  return str;
};
const extractDatabaseFromConnectionString = (connectionString) => {
  if (!connectionString) return void 0;
  try {
    const db = new URL(connectionString).pathname?.replace(/^\//, "");
    return db || void 0;
  } catch {
    return void 0;
  }
};
const extractHostFromConnectionString = (connectionString) => {
  if (!connectionString) return void 0;
  try {
    return new URL(connectionString).hostname || void 0;
  } catch {
    return void 0;
  }
};
const extractPortFromConnectionString = (connectionString) => {
  if (!connectionString) return void 0;
  try {
    const port = new URL(connectionString).port;
    return port ? parseInt(port, 10) : void 0;
  } catch {
    return void 0;
  }
};
const extractTableName = (builder) => {
  const table = builder?._single?.table;
  if (typeof table === "object") {
    return extractTableName(table);
  }
  return table;
};

exports.extractDatabaseFromConnectionString = extractDatabaseFromConnectionString;
exports.extractHostFromConnectionString = extractHostFromConnectionString;
exports.extractPortFromConnectionString = extractPortFromConnectionString;
exports.extractTableName = extractTableName;
exports.getFormatter = getFormatter;
exports.getName = getName;
exports.limitLength = limitLength;
exports.mapSystem = mapSystem;
//# sourceMappingURL=utils.js.map
