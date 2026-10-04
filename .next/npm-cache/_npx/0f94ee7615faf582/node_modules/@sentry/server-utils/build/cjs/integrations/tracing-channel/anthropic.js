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

const INTEGRATION_NAME = "Anthropic_AI";
const ORIGIN = "auto.ai.orchestrion.anthropic";
const INSTRUMENTED_CHANNELS = [
  { channel: channels.CHANNELS.ANTHROPIC_CHAT, operation: "chat", methodPath: "messages.create", stream: "async-iterable" },
  { channel: channels.CHANNELS.ANTHROPIC_MODELS, operation: "models", methodPath: "models.retrieve", stream: "none" },
  {
    channel: channels.CHANNELS.ANTHROPIC_MESSAGES_STREAM,
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
      if (!diagnosticsChannel__namespace.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      core.waitForTracingChannelBinding(() => {
        for (const { channel, operation, methodPath, stream } of INSTRUMENTED_CHANNELS) {
          debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:anthropic] subscribing to channel "${channel}"`);
          tracingChannel.bindTracingChannelToSpan(
            diagnosticsChannel__namespace.tracingChannel(channel),
            (data) => createGenAiSpan(data, operation, methodPath, options),
            {
              beforeSpanEnd: (span, data) => {
                core.addAnthropicResponseAttributes(
                  span,
                  data.result,
                  core.resolveAIRecordingOptions(options).recordOutputs
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
  if (core._INTERNAL_shouldSkipAiProviderWrapping(INTEGRATION_NAME)) {
    return void 0;
  }
  const requestOptions = args[1];
  if (requestOptions?.headers?.["X-Stainless-Helper-Method"] === "stream") {
    return void 0;
  }
  const params = typeof args[0] === "object" && args[0] !== null ? args[0] : void 0;
  const { recordInputs } = core.resolveAIRecordingOptions(options);
  const enableTruncation = core.shouldEnableTruncation(options.enableTruncation);
  const attributes = core.extractAnthropicRequestAttributes(args, methodPath, operation);
  const model = attributes[core.GEN_AI_REQUEST_MODEL_ATTRIBUTE] || "unknown";
  attributes[core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN] = ORIGIN;
  const span = core.startInactiveSpan({
    name: `${operation} ${model}`,
    op: `gen_ai.${operation}`,
    attributes
  });
  if (recordInputs && params) {
    core.addAnthropicRequestAttributes(span, params, enableTruncation);
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
  const { recordOutputs } = core.resolveAIRecordingOptions(options);
  const result = data.result;
  if (stream === "async-iterable" && isAsyncIterable(result)) {
    const iterate = result[Symbol.asyncIterator].bind(result);
    const instrumented = core.instrumentAsyncIterableStream({ [Symbol.asyncIterator]: iterate }, span, recordOutputs);
    result[Symbol.asyncIterator] = () => instrumented;
    return true;
  }
  if (stream === "message-stream" && isMessageStream(result)) {
    core.instrumentMessageStream(result, span, recordOutputs);
    return true;
  }
  return false;
}
const anthropicChannelIntegration = core.defineIntegration(_anthropicChannelIntegration);

exports.anthropicChannelIntegration = anthropicChannelIntegration;
//# sourceMappingURL=anthropic.js.map
