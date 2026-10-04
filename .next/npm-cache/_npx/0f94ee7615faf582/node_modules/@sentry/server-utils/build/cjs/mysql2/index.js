Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const diagnosticsChannel = require('node:diagnostics_channel');
const mysql2DcSubscriber = require('./mysql2-dc-subscriber.js');

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

const _mysql2Integration = (() => {
  return {
    name: "Mysql2",
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        mysql2DcSubscriber.subscribeMysql2DiagnosticChannels(diagnosticsChannel__namespace.tracingChannel);
      });
    }
  };
});
const mysql2Integration = core.defineIntegration(_mysql2Integration);

exports.mysql2Integration = mysql2Integration;
//# sourceMappingURL=index.js.map
