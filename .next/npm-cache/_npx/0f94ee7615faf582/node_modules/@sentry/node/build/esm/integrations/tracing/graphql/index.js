import { GraphQLInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration, extendIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { graphqlIntegration as graphqlIntegration$1 } from '@sentry/server-utils';

const INTEGRATION_NAME = "Graphql";
const instrumentGraphql = generateInstrumentOnce(
  INTEGRATION_NAME,
  GraphQLInstrumentation,
  (_options) => getOptionsWithDefaults(_options)
);
const _graphqlIntegration = ((options = {}) => {
  return extendIntegration(graphqlIntegration$1(getOptionsWithDefaults(options)), {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentGraphql(getOptionsWithDefaults(options));
    }
  });
});
const graphqlIntegration = defineIntegration(_graphqlIntegration);
function getOptionsWithDefaults(options) {
  return {
    ignoreResolveSpans: true,
    ignoreTrivialResolveSpans: true,
    useOperationNameForRootSpan: true,
    ...options
  };
}

export { graphqlIntegration, instrumentGraphql };
//# sourceMappingURL=index.js.map
