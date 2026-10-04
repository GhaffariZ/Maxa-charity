Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const semconv = require('./semconv.js');

function getConnectionAttributes(config) {
  const { host, port, database, user } = getConfig(config);
  const attrs = {
    [semconv.ATTR_DB_CONNECTION_STRING]: getJDBCString(host, port, database),
    // oxlint-disable-next-line typescript/no-deprecated
    [attributes.DB_NAME]: database,
    [attributes.DB_USER]: user,
    // oxlint-disable-next-line typescript/no-deprecated
    [attributes.NET_PEER_NAME]: host
  };
  const portNumber = parseInt(port, 10);
  if (!isNaN(portNumber)) {
    attrs[attributes.NET_PEER_PORT] = portNumber;
  }
  return attrs;
}
function getConfig(config) {
  const { host, port, database, user } = config?.connectionConfig || config || {};
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
function getQueryText(query, format, values) {
  const [querySql, queryValues] = typeof query === "string" ? [query, values] : [query.sql, hasValues(query) ? values || query.values : values];
  try {
    if (format && queryValues) {
      return format(querySql, queryValues);
    } else {
      return querySql;
    }
  } catch {
    return "Could not determine the query due to an error in formatting";
  }
}
function hasValues(obj) {
  return "values" in obj;
}
function getSpanName(query) {
  const rawQuery = typeof query === "object" ? query.sql : query;
  const firstSpace = rawQuery?.indexOf(" ");
  if (typeof firstSpace === "number" && firstSpace !== -1) {
    return rawQuery?.substring(0, firstSpace);
  }
  return rawQuery;
}
const once = (fn) => {
  let called = false;
  return (...args) => {
    if (called) return;
    called = true;
    return fn(...args);
  };
};
function getConnectionPrototypeToInstrument(connection) {
  const connectionPrototype = connection.prototype;
  const basePrototype = Object.getPrototypeOf(connectionPrototype);
  if (typeof basePrototype?.query === "function" && typeof basePrototype?.execute === "function") {
    return basePrototype;
  }
  return connectionPrototype;
}

exports.getConnectionAttributes = getConnectionAttributes;
exports.getConnectionPrototypeToInstrument = getConnectionPrototypeToInstrument;
exports.getQueryText = getQueryText;
exports.getSpanName = getSpanName;
exports.once = once;
//# sourceMappingURL=utils.js.map
