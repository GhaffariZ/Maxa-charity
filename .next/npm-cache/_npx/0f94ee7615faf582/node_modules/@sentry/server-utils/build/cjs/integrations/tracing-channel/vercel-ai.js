Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const index = require('../../vercel-ai/index.js');
const diagnosticsChannel = require('node:diagnostics_channel');
const vercelAiOrchestrionSubscriber = require('../../vercel-ai/vercel-ai-orchestrion-subscriber.js');

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

const _vercelAiChannelIntegration = ((options = {}) => {
  const parentIntegration = index.vercelAiIntegration(options);
  return core.extendIntegration(parentIntegration, {
    options,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        vercelAiOrchestrionSubscriber.subscribeVercelAiOrchestrionChannels(diagnosticsChannel__namespace.tracingChannel, options);
      });
    }
  });
});
const vercelAiChannelIntegration = core.defineIntegration(_vercelAiChannelIntegration);

exports.vercelAiChannelIntegration = vercelAiChannelIntegration;
//# sourceMappingURL=vercel-ai.js.map
