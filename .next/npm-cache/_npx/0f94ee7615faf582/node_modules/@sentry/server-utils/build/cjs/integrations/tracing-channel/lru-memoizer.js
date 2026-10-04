Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const debugBuild = require('../../debug-build.js');
const channels = require('../../orchestrion/channels.js');
const tracingChannel = require('../../tracing-channel.js');

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

const INTEGRATION_NAME = "LruMemoizer";
const _lruMemoizerChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:lru-memoizer] subscribing to channel "${channels.CHANNELS.LRU_MEMOIZER_LOAD}"`);
      core.waitForTracingChannelBinding(() => {
        tracingChannel.bindTracingChannelToSpan(
          diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.LRU_MEMOIZER_LOAD),
          // We only want the helper's caller-context restore for the callback lru-memoizer fires from a detached `setImmediate`.
          () => void 0
        );
      });
    }
  };
});
const lruMemoizerChannelIntegration = core.defineIntegration(_lruMemoizerChannelIntegration);

exports.lruMemoizerChannelIntegration = lruMemoizerChannelIntegration;
//# sourceMappingURL=lru-memoizer.js.map
