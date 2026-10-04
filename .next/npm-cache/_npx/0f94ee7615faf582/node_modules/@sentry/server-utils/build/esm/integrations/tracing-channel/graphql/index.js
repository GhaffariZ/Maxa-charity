import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding, debug, extendIntegration } from '@sentry/core';
import { DEBUG_BUILD } from '../../../debug-build.js';
import { graphqlIntegration } from '../../../graphql/index.js';
import { CHANNELS } from '../../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../../tracing-channel.js';
import { startParseSpan, finalizeValidateSpan, startValidateSpan, finalizeExecuteSpan, startExecuteSpan } from './spans.js';

const INTEGRATION_NAME = "Graphql";
function getOptionsWithDefaults(options) {
  return {
    ignoreResolveSpans: options.ignoreResolveSpans !== false,
    ignoreTrivialResolveSpans: options.ignoreTrivialResolveSpans !== false,
    useOperationNameForRootSpan: options.useOperationNameForRootSpan !== false
  };
}
function safe(fn) {
  try {
    return fn();
  } catch (error) {
    DEBUG_BUILD && debug.warn("[orchestrion:graphql] error building span", error);
    return void 0;
  }
}
const _graphqlChannelIntegration = ((options = {}) => {
  const config = getOptionsWithDefaults(options);
  const getConfig = () => config;
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        bindTracingChannelToSpan(
          diagnosticsChannel.tracingChannel(CHANNELS.GRAPHQL_PARSE),
          () => safe(() => startParseSpan())
        );
        bindTracingChannelToSpan(
          diagnosticsChannel.tracingChannel(CHANNELS.GRAPHQL_VALIDATE),
          (data) => safe(() => startValidateSpan(data.arguments[1])),
          { beforeSpanEnd: (span, data) => void safe(() => finalizeValidateSpan(span, data.result)) }
        );
        bindTracingChannelToSpan(
          diagnosticsChannel.tracingChannel(CHANNELS.GRAPHQL_EXECUTE),
          (data) => safe(() => startExecuteSpan(data.arguments, data.self, config, getConfig)),
          { beforeSpanEnd: (span, data) => void safe(() => finalizeExecuteSpan(span, data.result)) }
        );
      });
    }
  };
});
const graphqlChannelIntegration = defineIntegration(_graphqlChannelIntegration);
const graphqlDiagnosticsChannelIntegration = (options) => {
  const orchestrion = graphqlChannelIntegration(options);
  return extendIntegration(graphqlIntegration(options), {
    name: INTEGRATION_NAME,
    setupOnce: () => orchestrion.setupOnce?.()
  });
};

export { graphqlChannelIntegration, graphqlDiagnosticsChannelIntegration };
//# sourceMappingURL=index.js.map
