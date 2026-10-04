import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding, debug, addOpenAiResponseAttributes, resolveAIRecordingOptions, instrumentOpenAiStream, _INTERNAL_shouldSkipAiProviderWrapping, shouldEnableTruncation, extractOpenAiRequestAttributes, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, startInactiveSpan, addOpenAiRequestAttributes } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "OpenAI";
const ORIGIN = "auto.ai.orchestrion.openai";
const INSTRUMENTED_CHANNELS = [
  { channel: CHANNELS.OPENAI_CHAT, operation: "chat" },
  { channel: CHANNELS.OPENAI_EMBEDDINGS, operation: "embeddings" }
];
let subscribed = false;
const _openaiChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      waitForTracingChannelBinding(() => {
        for (const { channel, operation } of INSTRUMENTED_CHANNELS) {
          DEBUG_BUILD && debug.log(`[orchestrion:openai] subscribing to channel "${channel}"`);
          bindTracingChannelToSpan(
            diagnosticsChannel.tracingChannel(channel),
            (data) => createGenAiSpan(data, operation, options),
            {
              beforeSpanEnd: (span, data) => {
                addOpenAiResponseAttributes(span, data.result, resolveAIRecordingOptions(options).recordOutputs);
              },
              // Streaming: the result is a `Stream` consumed later, so instrument it and let it end the span.
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
  const args = data.arguments ?? [];
  const params = args[0];
  const { recordInputs } = resolveAIRecordingOptions(options);
  const enableTruncation = shouldEnableTruncation(options.enableTruncation);
  const attributes = extractOpenAiRequestAttributes(args, operation);
  attributes[SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN] = ORIGIN;
  const model = params?.model || "unknown";
  const span = startInactiveSpan({
    name: `${operation} ${model}`,
    op: `gen_ai.${operation}`,
    attributes
  });
  if (recordInputs && params) {
    addOpenAiRequestAttributes(span, params, operation, enableTruncation);
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
  const instrumented = instrumentOpenAiStream({ [Symbol.asyncIterator]: iterate }, span, recordOutputs ?? false);
  result[Symbol.asyncIterator] = () => instrumented;
  return true;
}
const openaiChannelIntegration = defineIntegration(_openaiChannelIntegration);

export { openaiChannelIntegration };
//# sourceMappingURL=openai.js.map
