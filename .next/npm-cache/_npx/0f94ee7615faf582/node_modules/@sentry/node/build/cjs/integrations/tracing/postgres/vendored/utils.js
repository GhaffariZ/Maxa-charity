Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const AttributeNames = require('./enums/AttributeNames.js');
const SpanNames = require('./enums/SpanNames.js');
const semconv = require('./semconv.js');
const attributes = require('@sentry/conventions/attributes');

const ORIGIN = "auto.db.otel.postgres";
function getQuerySpanName(dbName, queryConfig) {
  if (!queryConfig) return SpanNames.SpanNames.QUERY_PREFIX;
  const command = typeof queryConfig.name === "string" && queryConfig.name ? queryConfig.name : parseNormalizedOperationName(queryConfig.text);
  return `${SpanNames.SpanNames.QUERY_PREFIX}:${command}${dbName ? ` ${dbName}` : ""}`;
}
function parseNormalizedOperationName(queryText) {
  const trimmedQuery = queryText.trim();
  const indexOfFirstSpace = trimmedQuery.indexOf(" ");
  let sqlCommand = indexOfFirstSpace === -1 ? trimmedQuery : trimmedQuery.slice(0, indexOfFirstSpace);
  sqlCommand = sqlCommand.toUpperCase();
  return sqlCommand.endsWith(";") ? sqlCommand.slice(0, -1) : sqlCommand;
}
function parseAndMaskConnectionString(connectionString) {
  try {
    const url = new URL(connectionString);
    url.username = "";
    url.password = "";
    return url.toString();
  } catch {
    return "postgresql://localhost:5432/";
  }
}
function getConnectionString(params) {
  if ("connectionString" in params && params.connectionString) {
    return parseAndMaskConnectionString(params.connectionString);
  }
  const host = params.host || "localhost";
  const port = params.port || 5432;
  const database = params.database || "";
  return `postgresql://${host}:${port}/${database}`;
}
function getPort(port) {
  if (Number.isInteger(port)) {
    return port;
  }
  return void 0;
}
function getSemanticAttributesFromConnection(params) {
  return {
    [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_POSTGRESQL,
    [attributes.DB_NAME]: params.database,
    [semconv.ATTR_DB_CONNECTION_STRING]: getConnectionString(params),
    [attributes.DB_USER]: params.user,
    [attributes.NET_PEER_NAME]: params.host,
    // required
    [attributes.NET_PEER_PORT]: getPort(params.port)
  };
}
function getSemanticAttributesFromPoolConnection(params) {
  let url;
  try {
    url = params.connectionString ? new URL(params.connectionString) : void 0;
  } catch {
    url = void 0;
  }
  return {
    [AttributeNames.AttributeNames.IDLE_TIMEOUT_MILLIS]: params.idleTimeoutMillis,
    [AttributeNames.AttributeNames.MAX_CLIENT]: params.maxClient,
    [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_POSTGRESQL,
    [attributes.DB_NAME]: url?.pathname.slice(1) ?? params.database,
    [semconv.ATTR_DB_CONNECTION_STRING]: getConnectionString(params),
    [attributes.NET_PEER_NAME]: url?.hostname ?? params.host,
    [attributes.NET_PEER_PORT]: Number(url?.port) || getPort(params.port),
    [attributes.DB_USER]: url?.username ?? params.user
  };
}
function shouldSkipInstrumentation() {
  return core.getActiveSpan() === void 0;
}
function handleConfigQuery(queryConfig) {
  const { connectionParameters } = this;
  const dbName = connectionParameters.database;
  const spanName = getQuerySpanName(dbName, queryConfig);
  const span = core.startInactiveSpan({
    name: spanName,
    kind: core.SPAN_KIND.CLIENT,
    attributes: {
      ...getSemanticAttributesFromConnection(connectionParameters),
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
    }
  });
  if (!queryConfig) {
    return span;
  }
  if (queryConfig.text) {
    span.setAttribute(attributes.DB_STATEMENT, queryConfig.text);
  }
  if (typeof queryConfig.name === "string") {
    span.setAttribute(AttributeNames.AttributeNames.PG_PLAN, queryConfig.name);
  }
  return span;
}
function patchCallback(span, cb) {
  return function patchedCallback(err, res) {
    if (err) {
      span.setStatus({ code: core.SPAN_STATUS_ERROR, message: err.message });
    }
    span.end();
    cb.call(this, err, res);
  };
}
function patchCallbackPGPool(span, cb) {
  return function patchedCallback(err, res, done) {
    if (err) {
      span.setStatus({ code: core.SPAN_STATUS_ERROR, message: err.message });
    }
    span.end();
    cb.call(this, err, res, done);
  };
}
function patchClientConnectCallback(span, cb) {
  return function patchedClientConnectCallback(...args) {
    const err = args[0];
    if (err instanceof Error) {
      span.setStatus({ code: core.SPAN_STATUS_ERROR, message: err.message });
    }
    span.end();
    cb.apply(this, args);
  };
}
function getErrorMessage(e) {
  return typeof e === "object" && e !== null && "message" in e ? String(e.message) : void 0;
}
function isObjectWithTextString(it) {
  return typeof it === "object" && typeof it?.text === "string";
}

exports.ORIGIN = ORIGIN;
exports.getConnectionString = getConnectionString;
exports.getErrorMessage = getErrorMessage;
exports.getQuerySpanName = getQuerySpanName;
exports.getSemanticAttributesFromConnection = getSemanticAttributesFromConnection;
exports.getSemanticAttributesFromPoolConnection = getSemanticAttributesFromPoolConnection;
exports.handleConfigQuery = handleConfigQuery;
exports.isObjectWithTextString = isObjectWithTextString;
exports.parseAndMaskConnectionString = parseAndMaskConnectionString;
exports.parseNormalizedOperationName = parseNormalizedOperationName;
exports.patchCallback = patchCallback;
exports.patchCallbackPGPool = patchCallbackPGPool;
exports.patchClientConnectCallback = patchClientConnectCallback;
exports.shouldSkipInstrumentation = shouldSkipInstrumentation;
//# sourceMappingURL=utils.js.map
