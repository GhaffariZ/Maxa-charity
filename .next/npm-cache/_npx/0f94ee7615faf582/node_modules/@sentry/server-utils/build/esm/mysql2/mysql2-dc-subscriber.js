import { SERVER_PORT, SERVER_ADDRESS, DB_NAMESPACE, DB_OPERATION_NAME, DB_QUERY_TEXT, DB_SYSTEM_NAME } from '@sentry/conventions/attributes';
import { _INTERNAL_sanitizeSqlQuery, startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_OP, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { bindTracingChannelToSpan } from '../tracing-channel.js';

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
function setupQueryChannel(tracingChannel, channelName) {
  bindTracingChannelToSpan(
    tracingChannel(channelName),
    (data) => {
      const queryText = data.query ? _INTERNAL_sanitizeSqlQuery(data.query) : void 0;
      const operation = queryText?.match(SQL_OPERATION_RE)?.[1]?.toUpperCase();
      return startInactiveSpan({
        name: queryText || "mysql2.query",
        attributes: {
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
          [DB_SYSTEM_NAME]: DB_SYSTEM_NAME_VALUE_MYSQL,
          [DB_QUERY_TEXT]: queryText,
          [DB_OPERATION_NAME]: operation,
          [DB_NAMESPACE]: data.database || void 0,
          [SERVER_ADDRESS]: data.serverAddress,
          [SERVER_PORT]: data.serverPort
        }
      });
    },
    { requiresParentSpan: true }
  );
}
function setupConnectChannel(tracingChannel, channelName, spanName) {
  bindTracingChannelToSpan(
    tracingChannel(channelName),
    (data) => {
      return startInactiveSpan({
        name: spanName,
        attributes: {
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
          [DB_SYSTEM_NAME]: DB_SYSTEM_NAME_VALUE_MYSQL,
          [DB_NAMESPACE]: data.database || void 0,
          [SERVER_ADDRESS]: data.serverAddress,
          [SERVER_PORT]: data.serverPort
        }
      });
    },
    { requiresParentSpan: true }
  );
}

export { MYSQL2_DC_CHANNEL_CONNECT, MYSQL2_DC_CHANNEL_EXECUTE, MYSQL2_DC_CHANNEL_POOL_CONNECT, MYSQL2_DC_CHANNEL_QUERY, subscribeMysql2DiagnosticChannels };
//# sourceMappingURL=mysql2-dc-subscriber.js.map
