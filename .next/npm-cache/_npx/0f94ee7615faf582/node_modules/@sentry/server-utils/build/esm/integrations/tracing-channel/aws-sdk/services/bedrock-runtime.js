import { debug } from '@sentry/core';
import { GEN_AI_REQUEST_MODEL, GEN_AI_REQUEST_MAX_TOKENS, GEN_AI_REQUEST_TEMPERATURE, GEN_AI_REQUEST_TOP_P, GEN_AI_REQUEST_STOP_SEQUENCES, GEN_AI_USAGE_INPUT_TOKENS, GEN_AI_USAGE_OUTPUT_TOKENS, GEN_AI_RESPONSE_FINISH_REASONS, GEN_AI_OPERATION_NAME, GEN_AI_SYSTEM } from '@sentry/conventions/attributes';
import { DEBUG_BUILD } from '../../../../debug-build.js';
import { GEN_AI_OPERATION_NAME_VALUE_CHAT, GEN_AI_SYSTEM_VALUE_AWS_BEDROCK } from '../constants.js';

const textDecoder = new TextDecoder();
class BedrockRuntimeServiceExtension {
  requestPreSpanHook(request) {
    switch (request.commandName) {
      case "Converse":
        return this._requestPreSpanHookConverse(request, false);
      case "ConverseStream":
        return this._requestPreSpanHookConverse(request, true);
      case "InvokeModel":
        return this._requestPreSpanHookInvokeModel(request, false);
      case "InvokeModelWithResponseStream":
        return this._requestPreSpanHookInvokeModel(request, true);
    }
    return {};
  }
  responseHook(response, span) {
    const commandName = response.request.commandName;
    if (!span.isRecording()) {
      if (commandName === "ConverseStream" || commandName === "InvokeModelWithResponseStream") {
        span.end();
      }
      return;
    }
    switch (commandName) {
      case "Converse":
        return this._responseHookConverse(response, span);
      case "ConverseStream":
        return this._responseHookConverseStream(response, span);
      case "InvokeModel":
        return this._responseHookInvokeModel(response, span);
      case "InvokeModelWithResponseStream":
        return this._responseHookInvokeModelWithResponseStream(response, span);
    }
  }
  _requestPreSpanHookConverse(request, isStream) {
    let spanName = GEN_AI_OPERATION_NAME_VALUE_CHAT;
    const spanAttributes = {
      // oxlint-disable-next-line typescript/no-deprecated
      [GEN_AI_SYSTEM]: GEN_AI_SYSTEM_VALUE_AWS_BEDROCK,
      [GEN_AI_OPERATION_NAME]: GEN_AI_OPERATION_NAME_VALUE_CHAT
    };
    const modelId = request.commandInput.modelId;
    if (modelId) {
      spanAttributes[GEN_AI_REQUEST_MODEL] = modelId;
      if (spanName) {
        spanName += ` ${modelId}`;
      }
    }
    const inferenceConfig = request.commandInput.inferenceConfig;
    if (inferenceConfig) {
      const { maxTokens, temperature, topP, stopSequences } = inferenceConfig;
      if (maxTokens !== void 0) {
        spanAttributes[GEN_AI_REQUEST_MAX_TOKENS] = maxTokens;
      }
      if (temperature !== void 0) {
        spanAttributes[GEN_AI_REQUEST_TEMPERATURE] = temperature;
      }
      if (topP !== void 0) {
        spanAttributes[GEN_AI_REQUEST_TOP_P] = topP;
      }
      if (stopSequences !== void 0) {
        spanAttributes[GEN_AI_REQUEST_STOP_SEQUENCES] = stopSequences;
      }
    }
    return {
      spanName,
      isStream,
      spanAttributes
    };
  }
  _requestPreSpanHookInvokeModel(request, isStream) {
    const spanAttributes = {
      // oxlint-disable-next-line typescript/no-deprecated
      [GEN_AI_SYSTEM]: GEN_AI_SYSTEM_VALUE_AWS_BEDROCK
    };
    const modelId = request.commandInput?.modelId;
    if (modelId) {
      spanAttributes[GEN_AI_REQUEST_MODEL] = modelId;
    }
    if (request.commandInput?.body) {
      const requestBody = JSON.parse(request.commandInput.body);
      if (modelId.includes("amazon.titan")) {
        if (requestBody.textGenerationConfig?.temperature !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TEMPERATURE] = requestBody.textGenerationConfig.temperature;
        }
        if (requestBody.textGenerationConfig?.topP !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TOP_P] = requestBody.textGenerationConfig.topP;
        }
        if (requestBody.textGenerationConfig?.maxTokenCount !== void 0) {
          spanAttributes[GEN_AI_REQUEST_MAX_TOKENS] = requestBody.textGenerationConfig.maxTokenCount;
        }
        if (requestBody.textGenerationConfig?.stopSequences !== void 0) {
          spanAttributes[GEN_AI_REQUEST_STOP_SEQUENCES] = requestBody.textGenerationConfig.stopSequences;
        }
      } else if (modelId.includes("amazon.nova")) {
        if (requestBody.inferenceConfig?.temperature !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TEMPERATURE] = requestBody.inferenceConfig.temperature;
        }
        if (requestBody.inferenceConfig?.top_p !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TOP_P] = requestBody.inferenceConfig.top_p;
        }
        if (requestBody.inferenceConfig?.max_new_tokens !== void 0) {
          spanAttributes[GEN_AI_REQUEST_MAX_TOKENS] = requestBody.inferenceConfig.max_new_tokens;
        }
        if (requestBody.inferenceConfig?.stopSequences !== void 0) {
          spanAttributes[GEN_AI_REQUEST_STOP_SEQUENCES] = requestBody.inferenceConfig.stopSequences;
        }
      } else if (modelId.includes("anthropic.claude")) {
        if (requestBody.max_tokens !== void 0) {
          spanAttributes[GEN_AI_REQUEST_MAX_TOKENS] = requestBody.max_tokens;
        }
        if (requestBody.temperature !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TEMPERATURE] = requestBody.temperature;
        }
        if (requestBody.top_p !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TOP_P] = requestBody.top_p;
        }
        if (requestBody.stop_sequences !== void 0) {
          spanAttributes[GEN_AI_REQUEST_STOP_SEQUENCES] = requestBody.stop_sequences;
        }
      } else if (modelId.includes("meta.llama")) {
        if (requestBody.max_gen_len !== void 0) {
          spanAttributes[GEN_AI_REQUEST_MAX_TOKENS] = requestBody.max_gen_len;
        }
        if (requestBody.temperature !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TEMPERATURE] = requestBody.temperature;
        }
        if (requestBody.top_p !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TOP_P] = requestBody.top_p;
        }
      } else if (modelId.includes("cohere.command-r")) {
        if (requestBody.max_tokens !== void 0) {
          spanAttributes[GEN_AI_REQUEST_MAX_TOKENS] = requestBody.max_tokens;
        }
        if (requestBody.temperature !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TEMPERATURE] = requestBody.temperature;
        }
        if (requestBody.p !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TOP_P] = requestBody.p;
        }
        if (requestBody.message !== void 0) {
          spanAttributes[GEN_AI_USAGE_INPUT_TOKENS] = Math.ceil(requestBody.message.length / 6);
        }
        if (requestBody.stop_sequences !== void 0) {
          spanAttributes[GEN_AI_REQUEST_STOP_SEQUENCES] = requestBody.stop_sequences;
        }
      } else if (modelId.includes("cohere.command")) {
        if (requestBody.max_tokens !== void 0) {
          spanAttributes[GEN_AI_REQUEST_MAX_TOKENS] = requestBody.max_tokens;
        }
        if (requestBody.temperature !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TEMPERATURE] = requestBody.temperature;
        }
        if (requestBody.p !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TOP_P] = requestBody.p;
        }
        if (requestBody.prompt !== void 0) {
          spanAttributes[GEN_AI_USAGE_INPUT_TOKENS] = Math.ceil(requestBody.prompt.length / 6);
        }
        if (requestBody.stop_sequences !== void 0) {
          spanAttributes[GEN_AI_REQUEST_STOP_SEQUENCES] = requestBody.stop_sequences;
        }
      } else if (modelId.includes("mistral")) {
        if (requestBody.prompt !== void 0) {
          spanAttributes[GEN_AI_USAGE_INPUT_TOKENS] = Math.ceil(requestBody.prompt.length / 6);
        }
        if (requestBody.max_tokens !== void 0) {
          spanAttributes[GEN_AI_REQUEST_MAX_TOKENS] = requestBody.max_tokens;
        }
        if (requestBody.temperature !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TEMPERATURE] = requestBody.temperature;
        }
        if (requestBody.top_p !== void 0) {
          spanAttributes[GEN_AI_REQUEST_TOP_P] = requestBody.top_p;
        }
        if (requestBody.stop !== void 0) {
          spanAttributes[GEN_AI_REQUEST_STOP_SEQUENCES] = requestBody.stop;
        }
      }
    }
    return {
      isStream,
      spanAttributes
    };
  }
  _responseHookConverse(response, span) {
    const { stopReason, usage } = response.data;
    setStopReason(span, stopReason);
    setUsage(span, usage);
  }
  _responseHookConverseStream(response, span) {
    response.data.stream = wrapConverseStreamResponse(response.data.stream, span);
  }
  _responseHookInvokeModel(response, span) {
    const currentModelId = response.request.commandInput?.modelId;
    if (response.data?.body) {
      const decodedResponseBody = textDecoder.decode(response.data.body);
      const responseBody = JSON.parse(decodedResponseBody);
      if (currentModelId.includes("amazon.titan")) {
        if (responseBody.inputTextTokenCount !== void 0) {
          span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, responseBody.inputTextTokenCount);
        }
        if (responseBody.results?.[0]?.tokenCount !== void 0) {
          span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, responseBody.results[0].tokenCount);
        }
        if (responseBody.results?.[0]?.completionReason !== void 0) {
          span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [responseBody.results[0].completionReason]);
        }
      } else if (currentModelId.includes("amazon.nova")) {
        if (responseBody.usage !== void 0) {
          if (responseBody.usage.inputTokens !== void 0) {
            span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, responseBody.usage.inputTokens);
          }
          if (responseBody.usage.outputTokens !== void 0) {
            span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, responseBody.usage.outputTokens);
          }
        }
        if (responseBody.stopReason !== void 0) {
          span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [responseBody.stopReason]);
        }
      } else if (currentModelId.includes("anthropic.claude")) {
        if (responseBody.usage?.input_tokens !== void 0) {
          span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, responseBody.usage.input_tokens);
        }
        if (responseBody.usage?.output_tokens !== void 0) {
          span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, responseBody.usage.output_tokens);
        }
        if (responseBody.stop_reason !== void 0) {
          span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [responseBody.stop_reason]);
        }
      } else if (currentModelId.includes("meta.llama")) {
        if (responseBody.prompt_token_count !== void 0) {
          span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, responseBody.prompt_token_count);
        }
        if (responseBody.generation_token_count !== void 0) {
          span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, responseBody.generation_token_count);
        }
        if (responseBody.stop_reason !== void 0) {
          span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [responseBody.stop_reason]);
        }
      } else if (currentModelId.includes("cohere.command-r")) {
        if (responseBody.text !== void 0) {
          span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, Math.ceil(responseBody.text.length / 6));
        }
        if (responseBody.finish_reason !== void 0) {
          span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [responseBody.finish_reason]);
        }
      } else if (currentModelId.includes("cohere.command")) {
        if (responseBody.generations?.[0]?.text !== void 0) {
          span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, Math.ceil(responseBody.generations[0].text.length / 6));
        }
        if (responseBody.generations?.[0]?.finish_reason !== void 0) {
          span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [responseBody.generations[0].finish_reason]);
        }
      } else if (currentModelId.includes("mistral")) {
        if (responseBody.outputs?.[0]?.text !== void 0) {
          span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, Math.ceil(responseBody.outputs[0].text.length / 6));
        }
        if (responseBody.outputs?.[0]?.stop_reason !== void 0) {
          span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [responseBody.outputs[0].stop_reason]);
        }
      }
    }
  }
  _responseHookInvokeModelWithResponseStream(response, span) {
    const stream = response.data?.body;
    const modelId = response.request.commandInput?.modelId;
    if (!stream || !modelId) {
      return;
    }
    const recordAttributes = resolveStreamRecorder(modelId);
    response.data.body = (async function* () {
      try {
        for await (const chunk of stream) {
          if (recordAttributes) {
            const parsedChunk = parseChunk(chunk?.chunk?.bytes);
            if (parsedChunk) {
              recordAttributes(parsedChunk, span);
            }
          }
          yield chunk;
        }
      } finally {
        span.end();
      }
    })();
  }
}
function resolveStreamRecorder(modelId) {
  if (modelId.includes("amazon.titan")) return recordTitanAttributes;
  if (modelId.includes("anthropic.claude")) return recordClaudeAttributes;
  if (modelId.includes("amazon.nova")) return recordNovaAttributes;
  if (modelId.includes("meta.llama")) return recordLlamaAttributes;
  if (modelId.includes("cohere.command-r")) return recordCohereRAttributes;
  if (modelId.includes("cohere.command")) return recordCohereAttributes;
  if (modelId.includes("mistral")) return recordMistralAttributes;
  return void 0;
}
async function* wrapConverseStreamResponse(stream, span) {
  try {
    let usage;
    for await (const item of stream) {
      setStopReason(span, item.messageStop?.stopReason);
      usage = item.metadata?.usage;
      yield item;
    }
    setUsage(span, usage);
  } finally {
    span.end();
  }
}
function setStopReason(span, stopReason) {
  if (stopReason !== void 0) {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [stopReason]);
  }
}
function setUsage(span, usage) {
  if (usage) {
    const { inputTokens, outputTokens } = usage;
    if (inputTokens !== void 0) {
      span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, inputTokens);
    }
    if (outputTokens !== void 0) {
      span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, outputTokens);
    }
  }
}
function parseChunk(bytes) {
  if (!bytes || !(bytes instanceof Uint8Array)) {
    return null;
  }
  try {
    const str = Buffer.from(bytes).toString("utf-8");
    return JSON.parse(str);
  } catch (err) {
    DEBUG_BUILD && debug.warn("[orchestrion:aws-sdk] failed to parse streamed bedrock chunk", err);
    return null;
  }
}
function recordNovaAttributes(parsedChunk, span) {
  if (parsedChunk.metadata?.usage !== void 0) {
    if (parsedChunk.metadata?.usage.inputTokens !== void 0) {
      span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, parsedChunk.metadata.usage.inputTokens);
    }
    if (parsedChunk.metadata?.usage.outputTokens !== void 0) {
      span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, parsedChunk.metadata.usage.outputTokens);
    }
  }
  if (parsedChunk.messageStop?.stopReason !== void 0) {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [parsedChunk.messageStop.stopReason]);
  }
}
function recordClaudeAttributes(parsedChunk, span) {
  if (parsedChunk.message?.usage?.input_tokens !== void 0) {
    span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, parsedChunk.message.usage.input_tokens);
  }
  if (parsedChunk.message?.usage?.output_tokens !== void 0) {
    span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, parsedChunk.message.usage.output_tokens);
  }
  if (parsedChunk.delta?.stop_reason !== void 0) {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [parsedChunk.delta.stop_reason]);
  }
}
function recordTitanAttributes(parsedChunk, span) {
  if (parsedChunk.inputTextTokenCount !== void 0) {
    span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, parsedChunk.inputTextTokenCount);
  }
  if (parsedChunk.totalOutputTextTokenCount !== void 0) {
    span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, parsedChunk.totalOutputTextTokenCount);
  }
  if (parsedChunk.completionReason !== void 0) {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [parsedChunk.completionReason]);
  }
}
function recordLlamaAttributes(parsedChunk, span) {
  if (parsedChunk.prompt_token_count !== void 0) {
    span.setAttribute(GEN_AI_USAGE_INPUT_TOKENS, parsedChunk.prompt_token_count);
  }
  if (parsedChunk.generation_token_count !== void 0) {
    span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, parsedChunk.generation_token_count);
  }
  if (parsedChunk.stop_reason !== void 0) {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [parsedChunk.stop_reason]);
  }
}
function recordMistralAttributes(parsedChunk, span) {
  if (parsedChunk.outputs?.[0]?.text !== void 0) {
    span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, Math.ceil(parsedChunk.outputs[0].text.length / 6));
  }
  if (parsedChunk.outputs?.[0]?.stop_reason !== void 0) {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [parsedChunk.outputs[0].stop_reason]);
  }
}
function recordCohereAttributes(parsedChunk, span) {
  if (parsedChunk.generations?.[0]?.text !== void 0) {
    span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, Math.ceil(parsedChunk.generations[0].text.length / 6));
  }
  if (parsedChunk.generations?.[0]?.finish_reason !== void 0) {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [parsedChunk.generations[0].finish_reason]);
  }
}
function recordCohereRAttributes(parsedChunk, span) {
  if (parsedChunk.text !== void 0) {
    span.setAttribute(GEN_AI_USAGE_OUTPUT_TOKENS, Math.ceil(parsedChunk.text.length / 6));
  }
  if (parsedChunk.finish_reason !== void 0) {
    span.setAttribute(GEN_AI_RESPONSE_FINISH_REASONS, [parsedChunk.finish_reason]);
  }
}

export { BedrockRuntimeServiceExtension };
//# sourceMappingURL=bedrock-runtime.js.map
