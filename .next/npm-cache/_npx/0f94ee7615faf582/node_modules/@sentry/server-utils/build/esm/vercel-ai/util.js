import { isObjectLike } from '@sentry/core';

function asString(value) {
  return typeof value === "string" ? value : void 0;
}
function asNumber(value) {
  return typeof value === "number" && !isNaN(value) ? value : void 0;
}
function sum(a, b) {
  return a === void 0 && b === void 0 ? void 0 : (a ?? 0) + (b ?? 0);
}
function isReadableStream(value) {
  return isObjectLike(value) && typeof value.pipeThrough === "function" && typeof value.getReader === "function";
}
function tapModelCallStream(stream, onFinal, onError) {
  const reader = stream.getReader();
  const state = { toolCalls: [] };
  let text = "";
  let settled = false;
  const finalize = () => {
    if (settled) {
      return;
    }
    settled = true;
    if (text) {
      state.text = text;
    }
    onFinal(state);
  };
  const fail = (error) => {
    if (settled) {
      return;
    }
    settled = true;
    onError(error);
  };
  return new ReadableStream({
    async pull(controller) {
      try {
        const { done, value } = await reader.read();
        if (done) {
          finalize();
          controller.close();
          return;
        }
        text += accumulateChunk(state, value) ?? "";
        controller.enqueue(value);
      } catch (error) {
        fail(error);
        controller.error(error);
      }
    },
    cancel(reason) {
      finalize();
      return reader.cancel(reason);
    }
  });
}
function accumulateChunk(state, chunk) {
  if (!isObjectLike(chunk)) {
    return void 0;
  }
  const {
    type,
    delta,
    textDelta,
    id,
    modelId,
    toolCallId,
    toolName,
    input,
    args,
    finishReason,
    usage,
    providerMetadata
  } = chunk;
  switch (type) {
    case "text-delta": {
      const textChunk = delta ?? textDelta;
      return typeof textChunk === "string" ? textChunk : void 0;
    }
    case "tool-call":
      state.toolCalls.push({ toolCallId, toolName, input: input ?? args });
      return void 0;
    case "response-metadata":
      if (typeof id === "string") {
        state.responseId = id;
      }
      if (typeof modelId === "string") {
        state.responseModel = modelId;
      }
      return void 0;
    case "finish":
      state.finishReason = finishReason;
      state.usage = usage;
      if (providerMetadata !== void 0) {
        state.providerMetadata = providerMetadata;
      }
      return void 0;
    default:
      return void 0;
  }
}

export { asNumber, asString, isReadableStream, sum, tapModelCallStream };
//# sourceMappingURL=util.js.map
