import { defineIntegration, waitForTracingChannelBinding } from '@sentry/core';
import * as diagnosticsChannel from 'node:diagnostics_channel';
import { subscribeRedisDiagnosticChannels } from './redis-dc-subscriber.js';

const _redisIntegration = ((options = {}) => {
  return {
    name: "Redis",
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeRedisDiagnosticChannels(diagnosticsChannel.tracingChannel, options.responseHook);
      });
    }
  };
});
const redisIntegration = defineIntegration(_redisIntegration);

export { redisIntegration };
//# sourceMappingURL=index.js.map
