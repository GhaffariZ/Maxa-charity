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

const INTEGRATION_NAME = "Google_GenAI";
const ORIGIN = "auto.ai.orchestrion.google_genai";
const INSTRUMENTED_CHANNELS = [
  { channel: channels.CHANNELS.GOOGLE_GENAI_GENERATE_CONTENT, operation: "generate_content" },
  { channel: channels.CHANNELS.GOOGLE_GENAI_EMBED_CONTENT, operation: "embeddings" },
  { channel: channels.CHANNELS.GOOGLE_GENAI_CHAT, operation: "chat" }
];
let subscribed = false;
const _googleGenAIChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      core.waitForTracingChannelBinding(() => {
        for (const { channel, operation } of INSTRUMENTED_CHANNELS) {
          debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:google-genai] subscribing to channel "${channel}"`);
          tracingChannel.bindTracingChannelToSpan(
            diagnosticsChannel__namespace.tracingChannel(channel),
            (data) => createGenAiSpan(data, operation, options),
            {
              beforeSpanEnd: (span, data) => {
                if (operation !== "embeddings") {
                  core.addGoogleGenAIResponseAttributes(
                    span,
                    data.result,
                    core.resolveAIRecordingOptions(options).recordOutputs
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
  if (core._INTERNAL_shouldSkipAiProviderWrapping(INTEGRATION_NAME)) {
    return void 0;
  }
  if (operation !== "chat") {
    const activeSpan = core.getActiveSpan();
    if (activeSpan) {
      const { op, origin } = core.spanToJSON(activeSpan);
      if (origin === ORIGIN && op === "gen_ai.chat") {
        return void 0;
      }
    }
  }
  const args = data.arguments ?? [];
  const params = args[0];
  const { recordInputs } = core.resolveAIRecordingOptions(options);
  const enableTruncation = core.shouldEnableTruncation(options.enableTruncation);
  const attributes = core.extractGoogleGenAIRequestAttributes(operation, params, data.self);
  const model = attributes[core.GEN_AI_REQUEST_MODEL_ATTRIBUTE] || "unknown";
  attributes[core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN] = ORIGIN;
  const span = core.startInactiveSpan({
    name: `${operation} ${model}`,
    op: `gen_ai.${operation}`,
    attributes
  });
  if (recordInputs && params) {
    core.addGoogleGenAIRequestAttributes(span, params, operation, enableTruncation);
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
  const instrumented = core.instrumentGoogleGenAIStream({ [Symbol.asyncIterator]: iterate }, span, recordOutputs ?? false);
  result[Symbol.asyncIterator] = () => instrumented;
  return true;
}
const googleGenAIChannelIntegration = core.defineIntegration(_googleGenAIChannelIntegration);

exports.googleGenAIChannelIntegration = googleGenAIChannelIntegration;
//# sourceMappingURL=google-genai.js.map
