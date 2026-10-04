Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

function getConfig(config) {
  const resolved = config?.connectionConfig || config || {};
  const { host, port, database, user } = resolved;
  return { host, port, database, user };
}
function getJDBCString(host, port, database) {
  let jdbcString = `jdbc:mysql://${host || "localhost"}`;
  if (typeof port === "number") {
    jdbcString += `:${port}`;
  }
  if (typeof database === "string") {
    jdbcString += `/${database}`;
  }
  return jdbcString;
}
function getDbQueryText(query) {
  if (typeof query === "string") {
    return query;
  } else {
    return query.sql;
  }
}
function getSpanName(query) {
  const rawQuery = typeof query === "object" ? query.sql : query;
  const firstSpace = rawQuery?.indexOf(" ");
  if (typeof firstSpace === "number" && firstSpace !== -1) {
    return rawQuery?.substring(0, firstSpace);
  }
  return rawQuery;
}

exports.getConfig = getConfig;
exports.getDbQueryText = getDbQueryText;
exports.getJDBCString = getJDBCString;
exports.getSpanName = getSpanName;
//# sourceMappingURL=utils.js.map
