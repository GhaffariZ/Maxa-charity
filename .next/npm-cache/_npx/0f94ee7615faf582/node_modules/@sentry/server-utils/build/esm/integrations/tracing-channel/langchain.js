import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, LANGCHAIN_INTEGRATION_NAME, createLangChainCallbackHandler, debug, waitForTracingChannelBinding, _INTERNAL_mergeLangChainCallbackHandler, startInactiveSpan, _INTERNAL_getLangChainEmbeddingsSpanOptions, _INTERNAL_skipAiProviderWrapping, OPENAI_INTEGRATION_NAME, ANTHROPIC_AI_INTEGRATION_NAME, GOOGLE_GENAI_INTEGRATION_NAME } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { langchainEmbeddingsChannels } from '../../orchestrion/config/langchain.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = LANGCHAIN_INTEGRATION_NAME;
const SKIPPED_PROVIDERS = [OPENAI_INTEGRATION_NAME, ANTHROPIC_AI_INTEGRATION_NAME, GOOGLE_GENAI_INTEGRATION_NAME];
let subscribed = false;
function markProvidersSkipped() {
  _INTERNAL_skipAiProviderWrapping(SKIPPED_PROVIDERS);
}
const _langChainChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      const sentryHandler = createLangChainCallbackHandler(options);
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
        callOptions.callbacks = _INTERNAL_mergeLangChainCallbackHandler(callOptions.callbacks, sentryHandler);
      };
      for (const channelName of [CHANNELS.LANGCHAIN_CHAT_MODEL_INVOKE, CHANNELS.LANGCHAIN_CHAT_MODEL_STREAM]) {
        DEBUG_BUILD && debug.log(`[orchestrion:langchain] subscribing to channel "${channelName}"`);
        diagnosticsChannel.tracingChannel(channelName).start.subscribe(injectHandler);
      }
      waitForTracingChannelBinding(() => {
        for (const channelName of langchainEmbeddingsChannels) {
          DEBUG_BUILD && debug.log(`[orchestrion:langchain] subscribing to channel "${channelName}"`);
          bindTracingChannelToSpan(
            diagnosticsChannel.tracingChannel(channelName),
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
  return startInactiveSpan(_INTERNAL_getLangChainEmbeddingsSpanOptions(data.self, input, options));
}
const langChainChannelIntegration = defineIntegration(_langChainChannelIntegration);

export { langChainChannelIntegration };
//# sourceMappingURL=langchain.js.map
