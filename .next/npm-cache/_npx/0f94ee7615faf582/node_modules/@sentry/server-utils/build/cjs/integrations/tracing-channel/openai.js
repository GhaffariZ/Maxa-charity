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

const INTEGRATION_NAME = "OpenAI";
const ORIGIN = "auto.ai.orchestrion.openai";
const INSTRUMENTED_CHANNELS = [
  { channel: channels.CHANNELS.OPENAI_CHAT, operation: "chat" },
  { channel: channels.CHANNELS.OPENAI_EMBEDDINGS, operation: "embeddings" }
];
let subscribed = false;
const _openaiChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      core.waitForTracingChannelBinding(() => {
        for (const { channel, operation } of INSTRUMENTED_CHANNELS) {
          debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:openai] subscribing to channel "${channel}"`);
          tracingChannel.bindTracingChannelToSpan(
            diagnosticsChannel__namespace.tracingChannel(channel),
            (data) => createGenAiSpan(data, operation, options),
            {
              beforeSpanEnd: (span, data) => {
                core.addOpenAiResponseAttributes(span, data.result, core.resolveAIRecordingOptions(options).recordOutputs);
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
  if (core._INTERNAL_shouldSkipAiProviderWrapping(INTEGRATION_NAME)) {
    return void 0;
  }
  const args = data.arguments ?? [];
  const params = args[0];
  const { recordInputs } = core.resolveAIRecordingOptions(options);
  const enableTruncation = core.shouldEnableTruncation(options.enableTruncation);
  const attributes = core.extractOpenAiRequestAttributes(args, operation);
  attributes[core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN] = ORIGIN;
  const model = params?.model || "unknown";
  const span = core.startInactiveSpan({
    name: `${operation} ${model}`,
    op: `gen_ai.${operation}`,
    attributes
  });
  if (recordInputs && params) {
    core.addOpenAiRequestAttributes(span, params, operation, enableTruncation);
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
  const { recordOutputs } = core.resolveAIRecordingOptions(options);
  const iterate = result[Symbol.asyncIterator].bind(result);
  const instrumented = core.instrumentOpenAiStream({ [Symbol.asyncIterator]: iterate }, span, recordOutputs ?? false);
  result[Symbol.asyncIterator] = () => instrumented;
  return true;
}
const openaiChannelIntegration = core.defineIntegration(_openaiChannelIntegration);

exports.openaiChannelIntegration = openaiChannelIntegration;
//# sourceMappingURL=openai.js.map
