Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const mysql2DcSubscriber = require('../../mysql2/mysql2-dc-subscriber.js');
const channels = require('../../orchestrion/channels.js');
const tracingChannel = require('../../tracing-channel.js');
const attributes = require('@sentry/conventions/attributes');

function _interopNamespaceDefault(e) {
  const n = Object.create(null, { [Symbol.toStringTag]: { value: 'Module' } });
  if (e) {
    for (const k in e) {
      n[k] = e[k];
    }
  }
  n.default = e;
  return n;
}

const diagnosticsChannel__namespace = /*#__PURE__*/_interopNamespaceDefault(diagnosticsChannel);

const INTEGRATION_NAME = "Mysql2";
const ORIGIN = "auto.db.orchestrion.mysql2";
const DB_SYSTEM_VALUE_MYSQL = "mysql";
function instrumentMysql2() {
  mysql2DcSubscriber.subscribeMysql2DiagnosticChannels(diagnosticsChannel__namespace.tracingChannel);
  subscribeQueryChannel(channels.CHANNELS.MYSQL2_QUERY);
  subscribeQueryChannel(channels.CHANNELS.MYSQL2_EXECUTE);
}
function subscribeQueryChannel(channelName) {
  tracingChannel.bindTracingChannelToSpan(
    diagnosticsChannel__namespace.tracingChannel(channelName),
    (data) => {
      const statement = getQueryText(data.arguments);
      return core.startInactiveSpan({
        name: statement ?? "mysql2.query",
        kind: core.SPAN_KIND.CLIENT,
        attributes: {
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_SYSTEM]: DB_SYSTEM_VALUE_MYSQL,
          ...getConnectionAttributes(data.self?.config),
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_STATEMENT]: statement || void 0
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
  if (core.isObjectLike(firstArg) && "sql" in firstArg) {
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
    [attributes.DB_NAME]: database || void 0,
    [attributes.DB_USER]: user || void 0,
    // oxlint-disable-next-line typescript/no-deprecated
    [attributes.NET_PEER_NAME]: host || void 0,
    // oxlint-disable-next-line typescript/no-deprecated
    [attributes.NET_PEER_PORT]: portIsNumber ? portNumber : void 0
  };
}
const _mysql2ChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        instrumentMysql2();
      });
    }
  };
});
const mysql2ChannelIntegration = core.defineIntegration(_mysql2ChannelIntegration);

exports.mysql2ChannelIntegration = mysql2ChannelIntegration;
//# sourceMappingURL=mysql2.js.map
