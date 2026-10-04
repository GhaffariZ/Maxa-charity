Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const debugBuild = require('../debug-build.js');
const channels = require('../orchestrion/channels.js');
const tracingChannel = require('../tracing-channel.js');
const vercelAiDcSubscriber = require('./vercel-ai-dc-subscriber.js');
const util = require('./util.js');

const PATCHED = /* @__PURE__ */ Symbol("SentryVercelAiModelPatched");
const TOOL_PATCHED = /* @__PURE__ */ Symbol("SentryVercelAiToolPatched");
let callIdCounter = 0;
function nextCallId() {
  return `v6-${++callIdCounter}`;
}
const messages = /* @__PURE__ */ new WeakMap();
const operationSpans = /* @__PURE__ */ new WeakSet();
const toolCallSpans = /* @__PURE__ */ new WeakSet();
const callIdBySpan = /* @__PURE__ */ new WeakMap();
const recordingBySpan = /* @__PURE__ */ new WeakMap();
const operationErrorInfoBySpan = /* @__PURE__ */ new WeakMap();
const suppressedTelemetry = /* @__PURE__ */ new WeakSet();
let subscribed = false;
function subscribeVercelAiOrchestrionChannels(tracingChannel, options = {}) {
  if (subscribed) {
    return;
  }
  subscribed = true;
  try {
    bindOperation(tracingChannel, channels.CHANNELS.VERCEL_AI_GENERATE_TEXT, buildTextMessage("generateText"), options);
    bindOperation(tracingChannel, channels.CHANNELS.VERCEL_AI_STREAM_TEXT, buildTextMessage("streamText"), options);
    bindOperation(tracingChannel, channels.CHANNELS.VERCEL_AI_GENERATE_OBJECT, buildTextMessage("generateObject"), options);
    bindOperation(
      tracingChannel,
      channels.CHANNELS.VERCEL_AI_EMBED,
      (callOptions, telemetry) => ({
        type: "embed",
        event: {
          callId: nextCallId(),
          ...modelFields(callOptions.model),
          maxRetries: callOptions.maxRetries,
          value: callOptions.value,
          ...recording(telemetry)
        }
      }),
      options
    );
    bindOperation(
      tracingChannel,
      channels.CHANNELS.VERCEL_AI_EMBED_MANY,
      // `embedMany` takes a `values` array (vs `embed`'s single `value`); the shared core reads it as the
      // embeddings input, matching the OTel path's batch `ai.embedMany` span.
      (callOptions, telemetry) => ({
        type: "embedMany",
        event: {
          callId: nextCallId(),
          ...modelFields(callOptions.model),
          maxRetries: callOptions.maxRetries,
          values: callOptions.values,
          ...recording(telemetry)
        }
      }),
      options
    );
    bindOperation(
      tracingChannel,
      channels.CHANNELS.VERCEL_AI_EXECUTE_TOOL_CALL,
      (callOptions, telemetry) => ({
        type: "executeTool",
        // v6 carries the tool definitions on the executeToolCall args (a record keyed by name);
        // the shared core reads the matching tool's `description` for the span.
        event: {
          callId: nextCallId(),
          toolCall: callOptions.toolCall,
          tools: callOptions.tools,
          ...recording(telemetry)
        }
      }),
      options
    );
    subscribeResolveLanguageModel(tracingChannel, channels.CHANNELS.VERCEL_AI_RESOLVE_LANGUAGE_MODEL, options);
  } catch {
    debugBuild.DEBUG_BUILD && core.debug.log("Vercel AI orchestrion channel subscription failed.");
  }
}
function bindOperation(tracingChannel$1, channelName, build, options) {
  const channel = tracingChannel$1(channelName);
  const buildOperationSpan = (data) => {
    const callOptions = core.isObjectLike(data.arguments[0]) ? data.arguments[0] : {};
    const telemetry = core.isObjectLike(callOptions.experimental_telemetry) ? callOptions.experimental_telemetry : {};
    if (telemetry.isEnabled === false && !suppressedTelemetry.has(telemetry)) {
      return void 0;
    }
    const message = build(callOptions, telemetry);
    suppressNativeTelemetry(callOptions, telemetry);
    const callSiteSpan = core.getActiveSpan();
    const span = vercelAiDcSubscriber.createSpanFromMessage(message, options);
    if (span) {
      messages.set(data, message);
      operationSpans.add(span);
      const isV4 = core.isObjectLike(callOptions.model) && callOptions.model.specificationVersion === "v1";
      operationErrorInfoBySpan.set(span, { callSiteSpan, toolErrorsBubbleToCaller: isV4 });
      if (message.type === "executeTool") {
        toolCallSpans.add(span);
      }
      const callId = util.asString(message.event.callId);
      if (callId) {
        callIdBySpan.set(span, callId);
      }
      recordingBySpan.set(span, recording(telemetry));
      if (core.isObjectLike(callOptions.tools)) {
        patchOperationTools(callOptions.tools, options);
      }
      if (isV4) {
        patchModelMethods(callOptions.model, options);
      }
    }
    return span;
  };
  tracingChannel.bindTracingChannelToSpan(
    channel,
    (data) => buildOperationSpan(data),
    {
      beforeSpanEnd: (span, data) => {
        const message = messages.get(data);
        if (!message) {
          return;
        }
        if ("error" in data) {
          const callSiteSpan = operationErrorInfoBySpan.get(span)?.callSiteSpan;
          if (callSiteSpan && core.isObjectLike(data.error)) {
            core.addNonEnumerableProperty(data.error, "_sentry_active_span", callSiteSpan);
          }
        } else {
          message.result = message.type === "executeTool" ? { output: data.result } : data.result;
          vercelAiDcSubscriber.enrichSpanOnEnd(span, message, options);
        }
        if (message.type !== "streamText") {
          vercelAiDcSubscriber.clearOperationId(message);
        }
        messages.delete(data);
      },
      // `streamText` returns synchronously, so its operation span would otherwise end before the stream
      // drains — losing the aggregate usage/output. Defer the end and await the result's completion
      // promises (`totalUsage`/`text`/…, which resolve on drain), mirroring how v7's channel defers the
      // operation span on the SDK's total-usage promise.
      deferSpanEnd: ({ data, end }) => deferStreamTextOperationEnd(data, end)
    }
  );
}
function deferStreamTextOperationEnd(data, end) {
  if (messages.get(data)?.type !== "streamText" || "error" in data || !isStreamingResult(data.result)) {
    return false;
  }
  const streamResult = data.result;
  void (async () => {
    try {
      const [usage, text, toolCalls, finishReason, response] = await Promise.all([
        streamResult.totalUsage ?? streamResult.usage,
        streamResult.text,
        streamResult.toolCalls,
        streamResult.finishReason,
        streamResult.response
      ]);
      data.result = { usage, text, toolCalls, finishReason, response };
      end();
    } catch (error) {
      end(error);
    }
  })();
  return true;
}
function isStreamingResult(result) {
  return core.isObjectLike(result) && (isThenable(result.totalUsage) || isThenable(result.usage));
}
function isThenable(value) {
  return core.isObjectLike(value) && typeof value.then === "function";
}
function suppressNativeTelemetry(callOptions, telemetry) {
  if (telemetry.isEnabled !== true) {
    return;
  }
  const suppressed = { ...telemetry, isEnabled: false };
  suppressedTelemetry.add(suppressed);
  callOptions.experimental_telemetry = suppressed;
}
function subscribeResolveLanguageModel(tracingChannel, channelName, options) {
  tracingChannel(channelName).subscribe({
    end(rawCtx) {
      const ctx = rawCtx;
      if (!core.isObjectLike(ctx.result)) {
        return;
      }
      patchModelMethods(ctx.result, options);
    },
    start() {
    },
    asyncStart() {
    },
    asyncEnd() {
    },
    error() {
    }
  });
}
function resolveModelCallParent() {
  const active = core.getActiveSpan();
  return active && operationSpans.has(active) ? active : void 0;
}
function patchModelMethods(model, options) {
  if (model[PATCHED]) {
    return;
  }
  model[PATCHED] = true;
  patchModelMethod(model, "doGenerate", options);
  patchModelMethod(model, "doStream", options);
}
function patchModelMethod(model, method, options) {
  const original = model[method];
  if (typeof original !== "function") {
    return;
  }
  model[method] = function(...args) {
    const parent = resolveModelCallParent();
    if (!parent) {
      return Promise.resolve(original.apply(this, args));
    }
    const callArgs = core.isObjectLike(args[0]) ? args[0] : {};
    const callId = callIdBySpan.get(parent);
    const message = {
      type: "languageModelCall",
      event: {
        callId,
        provider: model.provider,
        modelId: model.modelId,
        // v4 nests the tool list under `mode.tools` (the `LanguageModelV1` call shape); v5+ passes a
        // top-level `tools` array. Reading both keeps `available_tools` populated on the model-call span.
        tools: callArgs.tools ?? (core.isObjectLike(callArgs.mode) ? callArgs.mode.tools : void 0),
        messages: callArgs.prompt,
        // Inherit the enclosing operation's per-call recording flags so inputs/tools/outputs are recorded on
        // the model-call span whenever they are on the parent `invoke_agent` span.
        ...recordingBySpan.get(parent)
      }
    };
    const span = core.withActiveSpan(parent, () => vercelAiDcSubscriber.createSpanFromMessage(message, options));
    if (!span) {
      return Promise.resolve(original.apply(this, args));
    }
    const clearStreamCallId = () => {
      if (method === "doStream" && callId) {
        vercelAiDcSubscriber.clearOperationCallId(callId);
      }
    };
    const failSpan = (error) => {
      span.setStatus({ code: core.SPAN_STATUS_ERROR, message: error instanceof Error ? error.message : "unknown_error" });
      span.end();
      clearStreamCallId();
      throw error;
    };
    try {
      const result = Promise.resolve(original.apply(this, args));
      return result.then((value) => {
        if (method === "doStream" && core.isObjectLike(value) && util.isReadableStream(value.stream)) {
          value.stream = util.tapModelCallStream(
            value.stream,
            (final) => {
              message.result = { ...value, ...vercelAiDcSubscriber.streamedResultToChannelResult(final) };
              vercelAiDcSubscriber.enrichSpanOnEnd(span, message, options);
              span.end();
              clearStreamCallId();
            },
            (error) => {
              span.setStatus({
                code: core.SPAN_STATUS_ERROR,
                message: error instanceof Error ? error.message : "unknown_error"
              });
              span.end();
              clearStreamCallId();
            }
          );
          return value;
        }
        message.result = value;
        vercelAiDcSubscriber.enrichSpanOnEnd(span, message, options);
        span.end();
        clearStreamCallId();
        return value;
      }, failSpan);
    } catch (error) {
      return failSpan(error);
    }
  };
}
function patchOperationTools(tools, options) {
  try {
    for (const [toolName, tool] of Object.entries(tools)) {
      if (core.isObjectLike(tool)) {
        patchToolExecute(toolName, tool, tools, options);
      }
    }
  } catch {
    debugBuild.DEBUG_BUILD && core.debug.log("Vercel AI orchestrion tool patching failed.");
  }
}
function patchToolExecute(toolName, tool, tools, options) {
  const original = tool.execute;
  if (typeof original !== "function" || tool[TOOL_PATCHED]) {
    return;
  }
  tool[TOOL_PATCHED] = true;
  tool.execute = function(input, ...rest) {
    const parent = resolveModelCallParent();
    if (!parent || toolCallSpans.has(parent)) {
      return original.apply(this, [input, ...rest]);
    }
    const callOptions = core.isObjectLike(rest[0]) ? rest[0] : {};
    const message = {
      type: "executeTool",
      event: {
        callId: callIdBySpan.get(parent),
        toolCall: { toolName, toolCallId: util.asString(callOptions.toolCallId), input },
        // The `tools` record (keyed by name) lets the shared core backfill the tool's `description`.
        tools,
        // Inherit the enclosing operation's per-call recording flags so tool inputs/outputs are recorded
        // whenever they are on the parent `invoke_agent` span.
        ...recordingBySpan.get(parent)
      }
    };
    const span = core.withActiveSpan(parent, () => vercelAiDcSubscriber.createSpanFromMessage(message, options));
    if (!span) {
      return original.apply(this, [input, ...rest]);
    }
    const failSpan = (error) => {
      if (operationErrorInfoBySpan.get(parent)?.toolErrorsBubbleToCaller) {
        span.setStatus({ code: core.SPAN_STATUS_ERROR, message: error instanceof Error ? error.message : "tool_error" });
      } else {
        vercelAiDcSubscriber.captureToolError(span, message, error);
      }
      span.end();
      throw error;
    };
    try {
      const result = Promise.resolve(original.apply(this, [input, ...rest]));
      return result.then((value) => {
        message.result = { output: value };
        vercelAiDcSubscriber.enrichSpanOnEnd(span, message, options);
        span.end();
        return value;
      }, failSpan);
    } catch (error) {
      return failSpan(error);
    }
  };
}
function buildTextMessage(type) {
  return (options, telemetry) => ({
    type,
    event: {
      callId: nextCallId(),
      operationId: `ai.${type}`,
      functionId: util.asString(telemetry.functionId),
      ...modelFields(options.model),
      maxRetries: options.maxRetries,
      // The `ai` SDK takes the system prompt as a top-level `system` option (all of v4/v5/v6); the
      // shared core lifts `event.instructions` into the system-instructions attribute, matching v7's
      // native channel (which carries it as a distinct field rather than inside the messages array).
      instructions: util.asString(options.system),
      // Normalize to the message-array shape the shared core (and v7's channel) expects: a bare string
      // `prompt` becomes a single user message, matching the SDK's own normalization.
      messages: normalizePromptMessages(options),
      ...recording(telemetry)
    }
  });
}
function normalizePromptMessages(options) {
  if (Array.isArray(options.messages)) {
    return options.messages;
  }
  if (typeof options.prompt === "string") {
    return [{ role: "user", content: options.prompt }];
  }
  return options.messages ?? options.prompt;
}
function recording(telemetry) {
  const enabledDefault = telemetry.isEnabled === true ? true : void 0;
  return {
    recordInputs: telemetry.recordInputs ?? enabledDefault,
    recordOutputs: telemetry.recordOutputs ?? enabledDefault
  };
}
function modelFields(model) {
  return { provider: modelField(model, "provider"), modelId: modelField(model, "modelId") };
}
function modelField(model, field) {
  return core.isObjectLike(model) ? util.asString(model[field]) : void 0;
}

exports.subscribeVercelAiOrchestrionChannels = subscribeVercelAiOrchestrionChannels;
//# sourceMappingURL=vercel-ai-orchestrion-subscriber.js.map
