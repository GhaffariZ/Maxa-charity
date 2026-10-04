import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding, debug, addGoogleGenAIResponseAttributes, resolveAIRecordingOptions, instrumentGoogleGenAIStream, _INTERNAL_shouldSkipAiProviderWrapping, getActiveSpan, spanToJSON, shouldEnableTruncation, extractGoogleGenAIRequestAttributes, GEN_AI_REQUEST_MODEL_ATTRIBUTE, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, startInactiveSpan, addGoogleGenAIRequestAttributes } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "Google_GenAI";
const ORIGIN = "auto.ai.orchestrion.google_genai";
const INSTRUMENTED_CHANNELS = [
  { channel: CHANNELS.GOOGLE_GENAI_GENERATE_CONTENT, operation: "generate_content" },
  { channel: CHANNELS.GOOGLE_GENAI_EMBED_CONTENT, operation: "embeddings" },
  { channel: CHANNELS.GOOGLE_GENAI_CHAT, operation: "chat" }
];
let subscribed = false;
const _googleGenAIChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      waitForTracingChannelBinding(() => {
        for (const { channel, operation } of INSTRUMENTED_CHANNELS) {
          DEBUG_BUILD && debug.log(`[orchestrion:google-genai] subscribing to channel "${channel}"`);
          bindTracingChannelToSpan(
            diagnosticsChannel.tracingChannel(channel),
            (data) => createGenAiSpan(data, operation, options),
            {
              beforeSpanEnd: (span, data) => {
                if (operation !== "embeddings") {
                  addGoogleGenAIResponseAttributes(
                    span,
                    data.result,
                    resolveAIRecordingOptions(options).recordOutputs
                  );
                }
              },
              deferSpanEnd: ({ span, data }) => wrapStreamResult(span, data, options)
            }
          );
        }
      });
    }
  };
});
function createGenAiSpan(data, operation, options) {
  if (_INTERNAL_shouldSkipAiProviderWrapping(INTEGRATION_NAME)) {
    return void 0;
  }
  if (operation !== "chat") {
    const activeSpan = getActiveSpan();
    if (activeSpan) {
      const { op, origin } = spanToJSON(activeSpan);
      if (origin === ORIGIN && op === "gen_ai.chat") {
        return void 0;
      }
    }
  }
  const args = data.arguments ?? [];
  const params = args[0];
  const { recordInputs } = resolveAIRecordingOptions(options);
  const enableTruncation = shouldEnableTruncation(options.enableTruncation);
  const attributes = extractGoogleGenAIRequestAttributes(operation, params, data.self);
  const model = attributes[GEN_AI_REQUEST_MODEL_ATTRIBUTE] || "unknown";
  attributes[SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN] = ORIGIN;
  const span = startInactiveSpan({
    name: `${operation} ${model}`,
    op: `gen_ai.${operation}`,
    attributes
  });
  if (recordInputs && params) {
    addGoogleGenAIRequestAttributes(span, params, operation, enableTruncation);
  }
  return span;
}
function isAsyncIterable(value) {
  return !!value && typeof value[Symbol.asyncIterator] === "function";
}
function wrapStreamResult(span, data, options) {
  const result = data.result;
  if (!isAsyncIterable(result)) {
    return false;
  }
  const { recordOutputs } = resolveAIRecordingOptions(options);
  const iterate = result[Symbol.asyncIterator].bind(result);
  const instrumented = instrumentGoogleGenAIStream({ [Symbol.asyncIterator]: iterate }, span, recordOutputs ?? false);
  result[Symbol.asyncIterator] = () => instrumented;
  return true;
}
const googleGenAIChannelIntegration = defineIntegration(_googleGenAIChannelIntegration);

export { googleGenAIChannelIntegration };
//# sourceMappingURL=google-genai.js.map
