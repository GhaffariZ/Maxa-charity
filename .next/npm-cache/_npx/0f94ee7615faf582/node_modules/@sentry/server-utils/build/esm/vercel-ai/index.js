import { defineIntegration, waitForTracingChannelBinding } from '@sentry/core';
import { subscribeVercelAiTracingChannel } from './vercel-ai-dc-subscriber.js';
import * as diagnosticsChannel from 'node:diagnostics_channel';

const _vercelAiIntegration = ((options = {}) => {
  return {
    name: "VercelAI",
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeVercelAiTracingChannel(diagnosticsChannel.tracingChannel, options);
      });
    }
  };
});
const vercelAiIntegration = defineIntegration(_vercelAiIntegration);

export { vercelAiIntegration };
//# sourceMappingURL=index.js.map
