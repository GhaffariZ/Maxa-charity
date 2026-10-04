import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_OP, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, isObjectLike } from '@sentry/core';
import { subscribeMysql2DiagnosticChannels } from '../../mysql2/mysql2-dc-subscriber.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';
import { DB_STATEMENT, DB_SYSTEM, NET_PEER_PORT, NET_PEER_NAME, DB_USER, DB_NAME } from '@sentry/conventions/attributes';

const INTEGRATION_NAME = "Mysql2";
const ORIGIN = "auto.db.orchestrion.mysql2";
const DB_SYSTEM_VALUE_MYSQL = "mysql";
function instrumentMysql2() {
  subscribeMysql2DiagnosticChannels(diagnosticsChannel.tracingChannel);
  subscribeQueryChannel(CHANNELS.MYSQL2_QUERY);
  subscribeQueryChannel(CHANNELS.MYSQL2_EXECUTE);
}
function subscribeQueryChannel(channelName) {
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(channelName),
    (data) => {
      const statement = getQueryText(data.arguments);
      return startInactiveSpan({
        name: statement ?? "mysql2.query",
        kind: SPAN_KIND.CLIENT,
        attributes: {
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_SYSTEM]: DB_SYSTEM_VALUE_MYSQL,
          ...getConnectionAttributes(data.self?.config),
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_STATEMENT]: statement || void 0
        }
      });
    },
    { requiresParentSpan: true }
  );
}
function getQueryText(args) {
  return extractSql(args[0]);
}
function extractSql(firstArg) {
  if (typeof firstArg === "string") {
    return firstArg;
  }
  if (isObjectLike(firstArg) && "sql" in firstArg) {
    const sql = firstArg.sql;
    return typeof sql === "string" ? sql : void 0;
  }
  return void 0;
}
function getConnectionAttributes(config) {
  const { host, port, database, user } = config?.connectionConfig ?? config ?? {};
  const portNumber = typeof port === "string" ? parseInt(port, 10) : port;
  const portIsNumber = typeof portNumber === "number" && !isNaN(portNumber);
  return {
    // oxlint-disable-next-line typescript/no-deprecated
    [DB_NAME]: database || void 0,
    [DB_USER]: user || void 0,
    // oxlint-disable-next-line typescript/no-deprecated
    [NET_PEER_NAME]: host || void 0,
    // oxlint-disable-next-line typescript/no-deprecated
    [NET_PEER_PORT]: portIsNumber ? portNumber : void 0
  };
}
const _mysql2ChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        instrumentMysql2();
      });
    }
  };
});
const mysql2ChannelIntegration = defineIntegration(_mysql2ChannelIntegration);

export { mysql2ChannelIntegration };
//# sourceMappingURL=mysql2.js.map
