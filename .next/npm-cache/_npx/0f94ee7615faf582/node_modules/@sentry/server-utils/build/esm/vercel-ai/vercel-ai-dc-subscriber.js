import { GEN_AI_TOOL_OUTPUT, GEN_AI_USAGE_INPUT_TOKENS, GEN_AI_USAGE_OUTPUT_TOKENS, GEN_AI_USAGE_TOTAL_TOKENS, GEN_AI_RESPONSE_FINISH_REASONS, GEN_AI_RESPONSE_ID, GEN_AI_RESPONSE_MODEL, GEN_AI_OUTPUT_MESSAGES, GEN_AI_EMBEDDINGS_INPUT, GEN_AI_OPERATION_NAME, GEN_AI_TOOL_INPUT, GEN_AI_TOOL_NAME, GEN_AI_TOOL_TYPE, GEN_AI_REQUEST_AVAILABLE_TOOLS, GEN_AI_FUNCTION_ID, GEN_AI_RESPONSE_STREAMING, GEN_AI_INPUT_MESSAGES, GEN_AI_REQUEST_MODEL, GEN_AI_SYSTEM } from '@sentry/conventions/attributes';
import { GEN_AI_EXECUTE_TOOL_SPAN_OP, GEN_AI_INVOKE_AGENT_SPAN_OP } from '@sentry/conventions/op';
import { isObjectLike, stringify, getProviderMetadataAttributes, GEN_AI_CONVERSATION_ID_ATTRIBUTE, spanToJSON, _INTERNAL_skipAiProviderWrapping, getClient, shouldEnableTruncation, SPAN_STATUS_ERROR, withScope, spanToTraceContext, captureException, LAST_STEP_ONLY_USAGE_KEYS, startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, getTruncatedJsonString, GEN_AI_SYSTEM_INSTRUCTIONS_ATTRIBUTE, GEN_AI_INPUT_MESSAGES_ORIGINAL_LENGTH_ATTRIBUTE } from '@sentry/core';
import { bindTracingChannelToSpan } from '../tracing-channel.js';
import { isReadableStream, asString, tapModelCallStream, sum, asNumber } from './util.js';

const AI_SDK_TELEMETRY_TRACING_CHANNEL = "ai:telemetry";
const ORIGIN = "auto.vercelai.channel";
const GEN_AI_TOOL_CALL_ID_ATTRIBUTE = "gen_ai.tool.call.id";
const GEN_AI_TOOL_DESCRIPTION_ATTRIBUTE = "gen_ai.tool.description";
const GEN_AI_EMBEDDINGS_OPERATION = "embeddings";
const GEN_AI_RERANK_OPERATION = "rerank";
const GEN_AI_GENERATE_CONTENT_OPERATION = "generate_content";
const WORKERS_AI_INTEGRATION_NAME = "WorkersAI";
const VERCEL_AI_OPERATION_ID_ATTRIBUTE = "vercel.ai.operationId";
const VERCEL_AI_MODEL_PROVIDER_ATTRIBUTE = "vercel.ai.model.provider";
const VERCEL_AI_SETTINGS_MAX_RETRIES_ATTRIBUTE = "vercel.ai.settings.maxRetries";
const operationIdByCallId = /* @__PURE__ */ new Map();
const toolDescriptionsByCallId = /* @__PURE__ */ new Map();
const invokeAgentSpanByCallId = /* @__PURE__ */ new Map();
const ROOT_OPERATION_TYPES = /* @__PURE__ */ new Set([
  "generateText",
  "streamText",
  "generateObject",
  "embed",
  "embedMany",
  "rerank"
]);
function clearOperationId(data) {
  if (!ROOT_OPERATION_TYPES.has(data.type)) {
    return;
  }
  const callId = asString(data.event.callId);
  if (callId) {
    clearOperationCallId(callId);
  }
}
function clearOperationCallId(callId) {
  operationIdByCallId.delete(callId);
  toolDescriptionsByCallId.delete(callId);
  invokeAgentSpanByCallId.delete(callId);
}
function dropLastStepOnlyUsage(providerAttributes, type) {
  if (!ROOT_OPERATION_TYPES.has(type)) {
    return;
  }
  for (const key of LAST_STEP_ONLY_USAGE_KEYS) {
    delete providerAttributes[key];
  }
}
function recordToolDescriptions(callId, tools) {
  if (!callId || !Array.isArray(tools)) {
    return;
  }
  let descriptions = toolDescriptionsByCallId.get(callId);
  for (const tool of tools) {
    if (isObjectLike(tool) && typeof tool.name === "string" && typeof tool.description === "string") {
      descriptions = descriptions ?? /* @__PURE__ */ new Map();
      if (!descriptions.has(tool.name)) {
        descriptions.set(tool.name, tool.description);
      }
    }
  }
  if (descriptions) {
    toolDescriptionsByCallId.set(callId, descriptions);
  }
}
function resolveToolDescription(callId, toolName, tools) {
  const fromMap = callId ? toolDescriptionsByCallId.get(callId)?.get(toolName) : void 0;
  if (fromMap) {
    return fromMap;
  }
  if (Array.isArray(tools)) {
    const match = tools.find((tool) => isObjectLike(tool) && tool.name === toolName);
    return isObjectLike(match) ? asString(match.description) : void 0;
  }
  if (isObjectLike(tools)) {
    const tool = tools[toolName];
    return isObjectLike(tool) ? asString(tool.description) : void 0;
  }
  return void 0;
}
function subscribeVercelAiTracingChannel(tracingChannel, options = {}) {
  bindTracingChannelToSpan(
    tracingChannel(AI_SDK_TELEMETRY_TRACING_CHANNEL),
    (data) => createSpanFromMessage(data, options),
    {
      // The helper ends the span; we enrich it from the settled result first (tokens, output messages,
      // finish reasons, response model/id, provider metadata) and drop the per-operation `callId` maps.
      beforeSpanEnd: (span, data) => {
        enrichSpanOnEnd(span, data, options);
        clearOperationId(data);
      },
      // A streamed model call resolves before its stream is drained, so we tap the stream, keep the
      // span open, and end it (via `end`) once the final usage/finish/output chunks arrive.
      deferSpanEnd: ({ data, end }) => deferStreamedModelCallEnd(data, options, end)
    }
  );
}
function deferStreamedModelCallEnd(data, options, end) {
  if (data.type !== "languageModelCall" || !isObjectLike(data.result)) {
    return false;
  }
  const result = data.result;
  const stream = result.stream;
  if (!isReadableStream(stream)) {
    return false;
  }
  const callId = asString(data.event.callId);
  const { recordOutputs } = getRecordingOptions(data.event, options);
  result.stream = tapModelCallStream(
    stream,
    (final) => {
      data.result = { ...result, ...streamedResultToChannelResult(final) };
      end();
      enrichInvokeAgentFromStream(callId, final, recordOutputs);
    },
    (error) => end(error)
  );
  return true;
}
function streamedResultToChannelResult(final) {
  const content = [];
  if (final.text) {
    content.push({ type: "text", text: final.text });
  }
  for (const toolCall of final.toolCalls) {
    content.push({ type: "tool-call", ...toolCall });
  }
  return {
    content,
    ...final.usage !== void 0 ? { usage: final.usage } : {},
    ...final.finishReason !== void 0 ? { finishReason: final.finishReason } : {},
    ...final.providerMetadata !== void 0 ? { providerMetadata: final.providerMetadata } : {},
    ...final.responseId || final.responseModel ? {
      response: {
        ...final.responseId ? { id: final.responseId } : {},
        ...final.responseModel ? { modelId: final.responseModel } : {}
      }
    } : {}
  };
}
function enrichInvokeAgentFromStream(callId, final, recordOutputs) {
  const span = callId ? invokeAgentSpanByCallId.get(callId) : void 0;
  if (!span) {
    return;
  }
  const usage = isObjectLike(final.usage) ? final.usage : void 0;
  if (usage) {
    const input = tokenCount(usage.inputTokens) ?? tokenCount(usage.promptTokens) ?? tokenCount(usage.tokens);
    const output = tokenCount(usage.outputTokens) ?? tokenCount(usage.completionTokens);
    addTokensToSpan(span, GEN_AI_USAGE_INPUT_TOKENS, input);
    addTokensToSpan(span, GEN_AI_USAGE_OUTPUT_TOKENS, output);
    addTokensToSpan(span, GEN_AI_USAGE_TOTAL_TOKENS, tokenCount(usage.totalTokens) ?? sum(input, output));
  }
  if (recordOutputs) {
    const parts = partsFromTextAndToolCalls(final.text, final.toolCalls);
    const outputMessages = buildOutputMessages(parts, getFinishReason({ finishReason: final.finishReason }));
    if (outputMessages) {
      span.setAttribute(GEN_AI_OUTPUT_MESSAGES, outputMessages);
    }
  }
}
function addTokensToSpan(span, attribute, value) {
  if (value === void 0) {
    return;
  }
  const current = spanToJSON(span).data[attribute];
  span.setAttribute(attribute, (typeof current === "number" ? current : 0) + value);
}
function createSpanFromMessage(data, channelOptions) {
  const { type, event } = data;
  if (type === "step" || !event || typeof event !== "object") {
    return void 0;
  }
  const { recordInputs, enableTruncation } = getRecordingOptions(event, channelOptions);
  const provider = asString(event.provider);
  const modelId = asString(event.modelId);
  const callId = asString(event.callId);
  const maxRetries = asNumber(event.maxRetries);
  if (recordInputs) {
    recordToolDescriptions(callId, event.tools);
  }
  const baseAttributes = {
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
    ...provider ? { [GEN_AI_SYSTEM]: provider, [VERCEL_AI_MODEL_PROVIDER_ATTRIBUTE]: provider } : {},
    ...modelId ? { [GEN_AI_REQUEST_MODEL]: modelId } : {},
    ...maxRetries !== void 0 ? { [VERCEL_AI_SETTINGS_MAX_RETRIES_ATTRIBUTE]: maxRetries } : {}
  };
  switch (type) {
    case "generateText":
    case "streamText":
    case "generateObject":
      return buildInvokeAgentSpan(event, baseAttributes, recordInputs, enableTruncation, callId, type === "streamText");
    case "languageModelCall":
      _INTERNAL_skipAiProviderWrapping([WORKERS_AI_INTEGRATION_NAME]);
      return buildModelCallSpan(event, baseAttributes, recordInputs, enableTruncation, callId, modelId);
    case "executeTool":
      return buildToolSpan(event, recordInputs);
    case "embed":
    case "embedMany": {
      const input = type === "embedMany" ? event.values : event.value;
      return startGenAiSpan(GEN_AI_EMBEDDINGS_OPERATION, modelId, {
        ...baseAttributes,
        ...recordInputs && input !== void 0 ? { [GEN_AI_EMBEDDINGS_INPUT]: stringify(input) } : {}
      });
    }
    case "rerank":
      return startGenAiSpan(GEN_AI_RERANK_OPERATION, modelId, baseAttributes);
    default:
      return void 0;
  }
}
function startGenAiSpan(operation, suffix, attributes) {
  return startInactiveSpan({
    name: suffix ? `${operation} ${suffix}` : operation,
    op: `gen_ai.${operation}`,
    attributes: { [GEN_AI_OPERATION_NAME]: operation, ...attributes }
  });
}
function buildInvokeAgentSpan(event, baseAttributes, recordInputs, enableTruncation, callId, isStream) {
  const functionId = asString(event.functionId);
  const operationId = asString(event.operationId) ?? (isStream ? "ai.streamText" : "ai.generateText");
  if (callId) {
    operationIdByCallId.set(callId, { operationId, isStream });
  }
  const span = startGenAiSpan(GEN_AI_INVOKE_AGENT_SPAN_OP, functionId, {
    ...baseAttributes,
    [VERCEL_AI_OPERATION_ID_ATTRIBUTE]: operationId,
    [GEN_AI_RESPONSE_STREAMING]: isStream,
    ...functionId ? { [GEN_AI_FUNCTION_ID]: functionId } : {},
    ...recordInputs ? buildInputMessageAttributes(event, enableTruncation) : {}
  });
  if (isStream && callId) {
    invokeAgentSpanByCallId.set(callId, span);
  }
  return span;
}
function buildModelCallSpan(event, baseAttributes, recordInputs, enableTruncation, callId, modelId) {
  const parent = callId ? operationIdByCallId.get(callId) : void 0;
  const operationId = parent ? `${parent.operationId}.${parent.isStream ? "doStream" : "doGenerate"}` : "ai.generateText.doGenerate";
  return startGenAiSpan(GEN_AI_GENERATE_CONTENT_OPERATION, modelId, {
    ...baseAttributes,
    [VERCEL_AI_OPERATION_ID_ATTRIBUTE]: operationId,
    ...recordInputs ? buildInputMessageAttributes(event, enableTruncation) : {},
    ...recordInputs && Array.isArray(event.tools) ? { [GEN_AI_REQUEST_AVAILABLE_TOOLS]: stringify(event.tools) } : {}
  });
}
function buildToolSpan(event, recordInputs) {
  const toolCall = isObjectLike(event.toolCall) ? event.toolCall : {};
  const toolName = asString(toolCall.toolName);
  const toolCallId = asString(event.toolCallId) ?? asString(toolCall.toolCallId);
  const toolInput = toolCall.input ?? toolCall.args;
  const description = recordInputs && toolName ? resolveToolDescription(asString(event.callId), toolName, event.tools) : void 0;
  return startGenAiSpan(GEN_AI_EXECUTE_TOOL_SPAN_OP, toolName, {
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
    [GEN_AI_TOOL_TYPE]: "function",
    ...toolName ? { [GEN_AI_TOOL_NAME]: toolName } : {},
    ...toolCallId ? { [GEN_AI_TOOL_CALL_ID_ATTRIBUTE]: toolCallId } : {},
    ...description ? { [GEN_AI_TOOL_DESCRIPTION_ATTRIBUTE]: description } : {},
    ...recordInputs && toolInput !== void 0 ? { [GEN_AI_TOOL_INPUT]: stringify(toolInput) } : {}
  });
}
function enrichSpanOnEnd(span, data, channelOptions) {
  const { type, result } = data;
  if (!isObjectLike(result)) {
    return;
  }
  const { recordOutputs } = getRecordingOptions(data.event, channelOptions);
  if (type === "executeTool") {
    if (recordOutputs) {
      span.setAttribute(GEN_AI_TOOL_OUTPUT, stringify(result.output ?? result));
    }
    const output = isObjectLike(result.output) ? result.output : void 0;
    if (output?.type === "tool-error") {
      captureToolError(span, data, output.error);
    }
    return;
  }
  const usage = isObjectLike(result.usage) ? result.usage : void 0;
  if (usage) {
    const inputTokens = tokenCount(usage.inputTokens) ?? tokenCount(usage.promptTokens) ?? tokenCount(usage.tokens);
    const outputTokens = tokenCount(usage.outputTokens) ?? tokenCount(usage.completionTokens);
    const totalTokens = tokenCount(usage.totalTokens) ?? sum(inputTokens, outputTokens);
    if (inputTokens !== void 0) {
      span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, inputTokens);
    }
    if (outputTokens !== void 0) {
      span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, outputTokens);
    }
    if (totalTokens !== void 0) {
      span.setAttribute(GEN_AI_USAGE_TOTAL_TOKENS, totalTokens);
    }
  }
  const finishReason = getFinishReason(result);
  if (finishReason && type === "languageModelCall") {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, stringify([finishReason]));
  }
  const response = isObjectLike(result.response) ? result.response : void 0;
  const responseId = asString(response?.id) ?? asString(result.responseId);
  if (responseId) {
    span.setAttribute(GEN_AI_RESPONSE_ID, responseId);
  }
  const responseModel = asString(response?.modelId) ?? asString(data.event.modelId);
  if (responseModel) {
    span.setAttribute(GEN_AI_RESPONSE_MODEL, responseModel);
  }
  const providerMetadata = result.providerMetadata;
  const providerAttributes = getProviderMetadataAttributes(providerMetadata);
  if (GEN_AI_CONVERSATION_ID_ATTRIBUTE in providerAttributes && spanToJSON(span).data[GEN_AI_CONVERSATION_ID_ATTRIBUTE]) {
    delete providerAttributes[GEN_AI_CONVERSATION_ID_ATTRIBUTE];
  }
  dropLastStepOnlyUsage(providerAttributes, type);
  span.setAttributes(providerAttributes);
  if (recordOutputs) {
    const parts = type === "languageModelCall" && Array.isArray(result.content) ? partsFromContent(result.content) : partsFromTextAndToolCalls(result.text, result.toolCalls);
    const outputMessages = buildOutputMessages(parts, finishReason);
    if (outputMessages) {
      span.setAttribute(GEN_AI_OUTPUT_MESSAGES, outputMessages);
    }
  }
}
function normalizeFinishReason(finishReason) {
  return finishReason === "tool-calls" ? "tool_call" : finishReason ?? "stop";
}
function getFinishReason(result) {
  const finishReason = result.finishReason;
  if (typeof finishReason === "string") {
    return finishReason;
  }
  return isObjectLike(finishReason) ? asString(finishReason.unified) : void 0;
}
function tokenCount(value) {
  return asNumber(value) ?? (isObjectLike(value) ? asNumber(value.total) : void 0);
}
function buildOutputMessages(parts, finishReason) {
  if (!parts.length) {
    return void 0;
  }
  return stringify([{ role: "assistant", parts, finish_reason: normalizeFinishReason(finishReason) }]);
}
function toolCallPart(toolCall) {
  const args = toolCall.input ?? toolCall.args;
  return {
    type: "tool_call",
    id: asString(toolCall.toolCallId),
    name: asString(toolCall.toolName),
    arguments: typeof args === "string" ? args : stringify(args ?? {})
  };
}
function partsFromContent(content) {
  const parts = [];
  for (const item of content) {
    if (!isObjectLike(item)) {
      continue;
    }
    if (item.type === "text" && typeof item.text === "string") {
      parts.push({ type: "text", content: item.text });
    } else if (item.type === "tool-call") {
      parts.push(toolCallPart(item));
    }
  }
  return parts;
}
function partsFromTextAndToolCalls(text, toolCalls) {
  const parts = [];
  if (typeof text === "string" && text.length) {
    parts.push({ type: "text", content: text });
  }
  if (Array.isArray(toolCalls)) {
    for (const toolCall of toolCalls) {
      if (isObjectLike(toolCall)) {
        parts.push(toolCallPart(toolCall));
      }
    }
  }
  return parts;
}
function captureToolError(span, data, error) {
  span.setStatus({
    code: SPAN_STATUS_ERROR,
    message: error instanceof Error ? error.message : "tool_error"
  });
  const toolCall = isObjectLike(data.event.toolCall) ? data.event.toolCall : {};
  const toolName = asString(toolCall.toolName);
  const toolCallId = asString(data.event.toolCallId) ?? asString(toolCall.toolCallId);
  withScope((scope) => {
    scope.setContext("trace", spanToTraceContext(span));
    if (toolName) {
      scope.setTag("vercel.ai.tool.name", toolName);
    }
    if (toolCallId) {
      scope.setTag("vercel.ai.tool.callId", toolCallId);
    }
    scope.setLevel("error");
    captureException(
      error instanceof Error ? error : new Error(typeof error === "string" ? error : "Tool execution failed"),
      {
        mechanism: { type: "auto.vercelai.channel", handled: false }
      }
    );
  });
}
function getRecordingOptions(event, channelOptions) {
  const genAI = getClient()?.getDataCollectionOptions().genAI;
  return {
    recordInputs: resolveRecording(channelOptions.recordInputs, event.recordInputs, genAI?.inputs),
    recordOutputs: resolveRecording(channelOptions.recordOutputs, event.recordOutputs, genAI?.outputs),
    enableTruncation: shouldEnableTruncation(channelOptions.enableTruncation)
  };
}
function resolveRecording(integrationOption, perCallOption, globalDefault) {
  if (typeof integrationOption === "boolean") {
    return integrationOption;
  }
  if (typeof perCallOption === "boolean") {
    return perCallOption;
  }
  return globalDefault === true;
}
function buildInputMessageAttributes(event, enableTruncation) {
  const attributes = {};
  const instructions = asString(event.instructions);
  if (instructions) {
    attributes[GEN_AI_SYSTEM_INSTRUCTIONS_ATTRIBUTE] = stringify([{ type: "text", content: instructions }]);
  }
  const messages = event.messages ?? event.prompt;
  if (messages !== void 0) {
    attributes[GEN_AI_INPUT_MESSAGES] = enableTruncation ? getTruncatedJsonString(messages) : stringify(messages);
    attributes[GEN_AI_INPUT_MESSAGES_ORIGINAL_LENGTH_ATTRIBUTE] = Array.isArray(messages) ? messages.length : 1;
  }
  return attributes;
}

export { captureToolError, clearOperationCallId, clearOperationId, createSpanFromMessage, enrichSpanOnEnd, streamedResultToChannelResult, subscribeVercelAiTracingChannel };
//# sourceMappingURL=vercel-ai-dc-subscriber.js.map
