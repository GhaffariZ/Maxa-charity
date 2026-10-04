Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const orchestrion = require('@sentry/server-utils/orchestrion');
const register = require('@sentry/server-utils/orchestrion/register');
const cache = require('../integrations/tracing/redis/cache.js');
const diagnosticsChannelInjection = require('./diagnosticsChannelInjection.js');

function diagnosticsChannelInjectionIntegrations() {
  return orchestrion.channelIntegrations;
}
function experimentalUseDiagnosticsChannelInjection(_options) {
  diagnosticsChannelInjection.setDiagnosticsChannelInjectionLoader(() => {
    const integrations = Object.values(orchestrion.channelIntegrations).map((createIntegration) => createIntegration());
    const replacedOtelIntegrationNames = integrations.map((i) => i.name);
    return {
      // ioredis and redis are wired separately (not in `channelIntegrations`): they need the node
      // redis cache `responseHook` and only partially replace the composite OTel `Redis` integration,
      // so they're kept OUT of `replacedOtelIntegrationNames` — `Redis` must stay (batch + >=5.11 native DC).
      integrations: [
        ...integrations,
        orchestrion.ioredisChannelIntegration({ responseHook: cache.cacheResponseHook }),
        orchestrion.redisChannelIntegration({ responseHook: cache.cacheResponseHook })
      ],
      replacedOtelIntegrationNames,
      register: () => register.registerDiagnosticsChannelInjection(),
      detect: orchestrion.detectOrchestrionSetup
    };
  });
}

exports.diagnosticsChannelInjectionIntegrations = diagnosticsChannelInjectionIntegrations;
exports.experimentalUseDiagnosticsChannelInjection = experimentalUseDiagnosticsChannelInjection;
//# sourceMappingURL=experimentalUseDiagnosticsChannelInjection.js.map
