import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, LANGGRAPH_INTEGRATION_NAME, resolveAIRecordingOptions, createLangChainCallbackHandler, waitForTracingChannelBinding, debug, startInactiveSpan, _INTERNAL_getLangGraphCreateAgentSpanOptions, wrapToolsWithSpans, extractAgentNameFromParams, extractLLMFromParams, instrumentCompiledGraphInvoke } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = LANGGRAPH_INTEGRATION_NAME;
let subscribed = false;
let insideCreateReactAgent = false;
const _langGraphChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      const resolvedOptions = resolveAIRecordingOptions(options);
      const sentryHandler = createLangChainCallbackHandler(resolvedOptions);
      waitForTracingChannelBinding(() => {
        DEBUG_BUILD && debug.log(`[orchestrion:langgraph] subscribing to channel "${CHANNELS.LANGGRAPH_STATE_GRAPH_COMPILE}"`);
        bindTracingChannelToSpan(
          diagnosticsChannel.tracingChannel(CHANNELS.LANGGRAPH_STATE_GRAPH_COMPILE),
          (data) => {
            if (insideCreateReactAgent) {
              return void 0;
            }
            const compileOptions = getFirstArgObject(data.arguments);
            const name = typeof compileOptions?.name === "string" ? compileOptions.name : void 0;
            return startInactiveSpan(_INTERNAL_getLangGraphCreateAgentSpanOptions(name));
          },
          {
            beforeSpanEnd: (_span, data) => {
              wrapCompiledGraphInvoke(
                data.result,
                getFirstArgObject(data.arguments) ?? {},
                resolvedOptions,
                null,
                sentryHandler
              );
            }
          }
        );
        DEBUG_BUILD && debug.log(`[orchestrion:langgraph] subscribing to channel "${CHANNELS.LANGGRAPH_CREATE_REACT_AGENT}"`);
        const reactAgentChannel = diagnosticsChannel.tracingChannel(
          CHANNELS.LANGGRAPH_CREATE_REACT_AGENT
        );
        reactAgentChannel.start.subscribe((message) => {
          insideCreateReactAgent = true;
          try {
            const { arguments: args } = message;
            const params = getFirstArgObject(args);
            if (params && Array.isArray(params.tools) && params.tools.length > 0) {
              wrapToolsWithSpans(params.tools, resolvedOptions, extractAgentNameFromParams(args) ?? void 0);
            }
          } catch (error) {
            DEBUG_BUILD && debug.error("[orchestrion:langgraph] failed to wrap createReactAgent tools", error);
          }
        });
        reactAgentChannel.end.subscribe((message) => {
          insideCreateReactAgent = false;
          const { arguments: args, result } = message;
          const agentName = extractAgentNameFromParams(args) ?? void 0;
          const compileOptions = agentName ? { name: agentName } : {};
          wrapCompiledGraphInvoke(result, compileOptions, resolvedOptions, extractLLMFromParams(args), sentryHandler);
        });
        reactAgentChannel.error.subscribe(() => {
          insideCreateReactAgent = false;
        });
      });
    }
  };
});
function getFirstArgObject(args) {
  const first = (args ?? [])[0];
  return typeof first === "object" && first !== null ? first : void 0;
}
function wrapCompiledGraphInvoke(graph, compileOptions, options, llm, sentryHandler) {
  if (!graph || typeof graph !== "object") {
    return;
  }
  const compiledGraph = graph;
  const originalInvoke = compiledGraph.invoke;
  if (typeof originalInvoke === "function") {
    compiledGraph.invoke = instrumentCompiledGraphInvoke(
      originalInvoke.bind(compiledGraph),
      compiledGraph,
      compileOptions,
      options,
      llm,
      sentryHandler
    );
  }
}
const langGraphChannelIntegration = defineIntegration(_langGraphChannelIntegration);

export { langGraphChannelIntegration };
//# sourceMappingURL=langgraph.js.map
