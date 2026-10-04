import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding, startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "GenericPool";
const _genericPoolChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => instrumentGenericPool());
    }
  };
});
const genericPoolChannelIntegration = defineIntegration(_genericPoolChannelIntegration);
function instrumentGenericPool() {
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(CHANNELS.GENERIC_POOL_ACQUIRE),
    () => startInactiveSpan({
      name: "generic-pool.acquire",
      attributes: {
        [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.db.orchestrion.generic_pool"
      }
    })
  );
}

export { genericPoolChannelIntegration };
//# sourceMappingURL=generic-pool.js.map
