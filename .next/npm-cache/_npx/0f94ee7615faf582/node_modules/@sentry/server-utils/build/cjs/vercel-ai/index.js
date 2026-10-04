Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const vercelAiDcSubscriber = require('./vercel-ai-dc-subscriber.js');
const diagnosticsChannel = require('node:diagnostics_channel');

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

const _vercelAiIntegration = ((options = {}) => {
  return {
    name: "VercelAI",
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        vercelAiDcSubscriber.subscribeVercelAiTracingChannel(diagnosticsChannel__namespace.tracingChannel, options);
      });
    }
  };
});
const vercelAiIntegration = core.defineIntegration(_vercelAiIntegration);

exports.vercelAiIntegration = vercelAiIntegration;
//# sourceMappingURL=index.js.map
