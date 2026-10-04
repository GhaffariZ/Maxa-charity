Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const debugBuild = require('../../debug-build.js');
const channels = require('../../orchestrion/channels.js');
const langchain = require('../../orchestrion/config/langchain.js');
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

const INTEGRATION_NAME = core.LANGCHAIN_INTEGRATION_NAME;
const SKIPPED_PROVIDERS = [core.OPENAI_INTEGRATION_NAME, core.ANTHROPIC_AI_INTEGRATION_NAME, core.GOOGLE_GENAI_INTEGRATION_NAME];
let subscribed = false;
function markProvidersSkipped() {
  core._INTERNAL_skipAiProviderWrapping(SKIPPED_PROVIDERS);
}
const _langChainChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      const sentryHandler = core.createLangChainCallbackHandler(options);
      const injectHandler = (message) => {
        markProvidersSkipped();
        const args = message.arguments;
        if (!Array.isArray(args)) {
          return;
        }
        let callOptions = args[1];
        if (!callOptions || typeof callOptions !== "object" || Array.isArray(callOptions)) {
          callOptions = {};
          args[1] = callOptions;
        }
        callOptions.callbacks = core._INTERNAL_mergeLangChainCallbackHandler(callOptions.callbacks, sentryHandler);
      };
      for (const channelName of [channels.CHANNELS.LANGCHAIN_CHAT_MODEL_INVOKE, channels.CHANNELS.LANGCHAIN_CHAT_MODEL_STREAM]) {
        debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:langchain] subscribing to channel "${channelName}"`);
        diagnosticsChannel__namespace.tracingChannel(channelName).start.subscribe(injectHandler);
      }
      core.waitForTracingChannelBinding(() => {
        for (const channelName of langchain.langchainEmbeddingsChannels) {
          debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:langchain] subscribing to channel "${channelName}"`);
          tracingChannel.bindTracingChannelToSpan(
            diagnosticsChannel__namespace.tracingChannel(channelName),
            (data) => createEmbeddingsSpan(data, options)
          );
        }
      });
    }
  };
});
function createEmbeddingsSpan(data, options) {
  markProvidersSkipped();
  const input = (data.arguments ?? [])[0];
  return core.startInactiveSpan(core._INTERNAL_getLangChainEmbeddingsSpanOptions(data.self, input, options));
}
const langChainChannelIntegration = core.defineIntegration(_langChainChannelIntegration);

exports.langChainChannelIntegration = langChainChannelIntegration;
//# sourceMappingURL=langchain.js.map
