Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const tracingChannel = require('../tracing-channel.js');

const MYSQL2_DC_CHANNEL_QUERY = "mysql2:query";
const MYSQL2_DC_CHANNEL_EXECUTE = "mysql2:execute";
const MYSQL2_DC_CHANNEL_CONNECT = "mysql2:connect";
const MYSQL2_DC_CHANNEL_POOL_CONNECT = "mysql2:pool:connect";
const ORIGIN = "auto.db.mysql2.diagnostic_channel";
const DB_SYSTEM_NAME_VALUE_MYSQL = "mysql";
const SQL_OPERATION_RE = /^\s*(\w+)/;
function subscribeMysql2DiagnosticChannels(tracingChannel) {
  setupQueryChannel(tracingChannel, MYSQL2_DC_CHANNEL_QUERY);
  setupQueryChannel(tracingChannel, MYSQL2_DC_CHANNEL_EXECUTE);
  setupConnectChannel(tracingChannel, MYSQL2_DC_CHANNEL_CONNECT, "mysql2.connect");
  setupConnectChannel(tracingChannel, MYSQL2_DC_CHANNEL_POOL_CONNECT, "mysql2.pool.connect");
}
function setupQueryChannel(tracingChannel$1, channelName) {
  tracingChannel.bindTracingChannelToSpan(
    tracingChannel$1(channelName),
    (data) => {
      const queryText = data.query ? core._INTERNAL_sanitizeSqlQuery(data.query) : void 0;
      const operation = queryText?.match(SQL_OPERATION_RE)?.[1]?.toUpperCase();
      return core.startInactiveSpan({
        name: queryText || "mysql2.query",
        attributes: {
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
          [attributes.DB_SYSTEM_NAME]: DB_SYSTEM_NAME_VALUE_MYSQL,
          [attributes.DB_QUERY_TEXT]: queryText,
          [attributes.DB_OPERATION_NAME]: operation,
          [attributes.DB_NAMESPACE]: data.database || void 0,
          [attributes.SERVER_ADDRESS]: data.serverAddress,
          [attributes.SERVER_PORT]: data.serverPort
        }
      });
    },
    { requiresParentSpan: true }
  );
}
function setupConnectChannel(tracingChannel$1, channelName, spanName) {
  tracingChannel.bindTracingChannelToSpan(
    tracingChannel$1(channelName),
    (data) => {
      return core.startInactiveSpan({
        name: spanName,
        attributes: {
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
          [attributes.DB_SYSTEM_NAME]: DB_SYSTEM_NAME_VALUE_MYSQL,
          [attributes.DB_NAMESPACE]: data.database || void 0,
          [attributes.SERVER_ADDRESS]: data.serverAddress,
          [attributes.SERVER_PORT]: data.serverPort
        }
      });
    },
    { requiresParentSpan: true }
  );
}

exports.MYSQL2_DC_CHANNEL_CONNECT = MYSQL2_DC_CHANNEL_CONNECT;
exports.MYSQL2_DC_CHANNEL_EXECUTE = MYSQL2_DC_CHANNEL_EXECUTE;
exports.MYSQL2_DC_CHANNEL_POOL_CONNECT = MYSQL2_DC_CHANNEL_POOL_CONNECT;
exports.MYSQL2_DC_CHANNEL_QUERY = MYSQL2_DC_CHANNEL_QUERY;
exports.subscribeMysql2DiagnosticChannels = subscribeMysql2DiagnosticChannels;
//# sourceMappingURL=mysql2-dc-subscriber.js.map
