import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, debug, waitForTracingChannelBinding } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "LruMemoizer";
const _lruMemoizerChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log(`[orchestrion:lru-memoizer] subscribing to channel "${CHANNELS.LRU_MEMOIZER_LOAD}"`);
      waitForTracingChannelBinding(() => {
        bindTracingChannelToSpan(
          diagnosticsChannel.tracingChannel(CHANNELS.LRU_MEMOIZER_LOAD),
          // We only want the helper's caller-context restore for the callback lru-memoizer fires from a detached `setImmediate`.
          () => void 0
        );
      });
    }
  };
});
const lruMemoizerChannelIntegration = defineIntegration(_lruMemoizerChannelIntegration);

export { lruMemoizerChannelIntegration };
//# sourceMappingURL=lru-memoizer.js.map
