import { defineIntegration, extendIntegration, waitForTracingChannelBinding } from '@sentry/core';
import { vercelAiIntegration } from '../../vercel-ai/index.js';
import * as diagnosticsChannel from 'node:diagnostics_channel';
import { subscribeVercelAiOrchestrionChannels } from '../../vercel-ai/vercel-ai-orchestrion-subscriber.js';

const _vercelAiChannelIntegration = ((options = {}) => {
  const parentIntegration = vercelAiIntegration(options);
  return extendIntegration(parentIntegration, {
    options,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeVercelAiOrchestrionChannels(diagnosticsChannel.tracingChannel, options);
      });
    }
  });
});
const vercelAiChannelIntegration = defineIntegration(_vercelAiChannelIntegration);

export { vercelAiChannelIntegration };
//# sourceMappingURL=vercel-ai.js.map
