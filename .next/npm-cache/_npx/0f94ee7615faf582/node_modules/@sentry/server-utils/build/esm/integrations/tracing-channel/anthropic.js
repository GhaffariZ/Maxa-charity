import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding, debug, addAnthropicResponseAttributes, resolveAIRecordingOptions, instrumentAsyncIterableStream, instrumentMessageStream, _INTERNAL_shouldSkipAiProviderWrapping, shouldEnableTruncation, extractAnthropicRequestAttributes, GEN_AI_REQUEST_MODEL_ATTRIBUTE, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, startInactiveSpan, addAnthropicRequestAttributes } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "Anthropic_AI";
const ORIGIN = "auto.ai.orchestrion.anthropic";
const INSTRUMENTED_CHANNELS = [
  { channel: CHANNELS.ANTHROPIC_CHAT, operation: "chat", methodPath: "messages.create", stream: "async-iterable" },
  { channel: CHANNELS.ANTHROPIC_MODELS, operation: "models", methodPath: "models.retrieve", stream: "none" },
  {
    channel: CHANNELS.ANTHROPIC_MESSAGES_STREAM,
    operation: "chat",
    methodPath: "messages.stream",
    stream: "message-stream"
  }
];
let subscribed = false;
const _anthropicChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      waitForTracingChannelBinding(() => {
        for (const { channel, operation, methodPath, stream } of INSTRUMENTED_CHANNELS) {
          DEBUG_BUILD && debug.log(`[orchestrion:anthropic] subscribing to channel "${channel}"`);
          bindTracingChannelToSpan(
            diagnosticsChannel.tracingChannel(channel),
            (data) => createGenAiSpan(data, operation, methodPath, options),
            {
              beforeSpanEnd: (span, data) => {
                addAnthropicResponseAttributes(
                  span,
                  data.result,
                  resolveAIRecordingOptions(options).recordOutputs
                );
              },
              deferSpanEnd: ({ span, data }) => wrapStreamResult(span, data, stream, options)
            }
          );
        }
      });
    }
  };
});
function createGenAiSpan(data, operation, methodPath, options) {
  const args = data.arguments ?? [];
  if (_INTERNAL_shouldSkipAiProviderWrapping(INTEGRATION_NAME)) {
    return void 0;
  }
  const requestOptions = args[1];
  if (requestOptions?.headers?.["X-Stainless-Helper-Method"] === "stream") {
    return void 0;
  }
  const params = typeof args[0] === "object" && args[0] !== null ? args[0] : void 0;
  const { recordInputs } = resolveAIRecordingOptions(options);
  const enableTruncation = shouldEnableTruncation(options.enableTruncation);
  const attributes = extractAnthropicRequestAttributes(args, methodPath, operation);
  const model = attributes[GEN_AI_REQUEST_MODEL_ATTRIBUTE] || "unknown";
  attributes[SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN] = ORIGIN;
  const span = startInactiveSpan({
    name: `${operation} ${model}`,
    op: `gen_ai.${operation}`,
    attributes
  });
  if (recordInputs && params) {
    addAnthropicRequestAttributes(span, params, enableTruncation);
  }
  return span;
}
function isAsyncIterable(value) {
  return !!value && typeof value[Symbol.asyncIterator] === "function";
}
function isMessageStream(value) {
  return !!value && typeof value.on === "function";
}
function wrapStreamResult(span, data, stream, options) {
  const { recordOutputs } = resolveAIRecordingOptions(options);
  const result = data.result;
  if (stream === "async-iterable" && isAsyncIterable(result)) {
    const iterate = result[Symbol.asyncIterator].bind(result);
    const instrumented = instrumentAsyncIterableStream({ [Symbol.asyncIterator]: iterate }, span, recordOutputs);
    result[Symbol.asyncIterator] = () => instrumented;
    return true;
  }
  if (stream === "message-stream" && isMessageStream(result)) {
    instrumentMessageStream(result, span, recordOutputs);
    return true;
  }
  return false;
}
const anthropicChannelIntegration = defineIntegration(_anthropicChannelIntegration);

export { anthropicChannelIntegration };
//# sourceMappingURL=anthropic.js.map
