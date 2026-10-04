Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const diagnosticsChannel = require('node:diagnostics_channel');
const redisDcSubscriber = require('./redis-dc-subscriber.js');

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

const _redisIntegration = ((options = {}) => {
  return {
    name: "Redis",
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        redisDcSubscriber.subscribeRedisDiagnosticChannels(diagnosticsChannel__namespace.tracingChannel, options.responseHook);
      });
    }
  };
});
const redisIntegration = core.defineIntegration(_redisIntegration);

exports.redisIntegration = redisIntegration;
//# sourceMappingURL=index.js.map
