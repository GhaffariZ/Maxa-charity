import { channelIntegrations, detectOrchestrionSetup, ioredisChannelIntegration, redisChannelIntegration } from '@sentry/server-utils/orchestrion';
import { registerDiagnosticsChannelInjection } from '@sentry/server-utils/orchestrion/register';
import { cacheResponseHook } from '../integrations/tracing/redis/cache.js';
import { setDiagnosticsChannelInjectionLoader } from './diagnosticsChannelInjection.js';

function diagnosticsChannelInjectionIntegrations() {
  return channelIntegrations;
}
function experimentalUseDiagnosticsChannelInjection(_options) {
  setDiagnosticsChannelInjectionLoader(() => {
    const integrations = Object.values(channelIntegrations).map((createIntegration) => createIntegration());
    const replacedOtelIntegrationNames = integrations.map((i) => i.name);
    return {
      // ioredis and redis are wired separately (not in `channelIntegrations`): they need the node
      // redis cache `responseHook` and only partially replace the composite OTel `Redis` integration,
      // so they're kept OUT of `replacedOtelIntegrationNames` — `Redis` must stay (batch + >=5.11 native DC).
      integrations: [
        ...integrations,
        ioredisChannelIntegration({ responseHook: cacheResponseHook }),
        redisChannelIntegration({ responseHook: cacheResponseHook })
      ],
      replacedOtelIntegrationNames,
      register: () => registerDiagnosticsChannelInjection(),
      detect: detectOrchestrionSetup
    };
  });
}

export { diagnosticsChannelInjectionIntegrations, experimentalUseDiagnosticsChannelInjection };
//# sourceMappingURL=experimentalUseDiagnosticsChannelInjection.js.map
