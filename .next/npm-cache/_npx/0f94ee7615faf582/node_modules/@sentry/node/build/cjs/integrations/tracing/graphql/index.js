Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('./vendored/instrumentation.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const serverUtils = require('@sentry/server-utils');

const INTEGRATION_NAME = "Graphql";
const instrumentGraphql = nodeCore.generateInstrumentOnce(
  INTEGRATION_NAME,
  instrumentation.GraphQLInstrumentation,
  (_options) => getOptionsWithDefaults(_options)
);
const _graphqlIntegration = ((options = {}) => {
  return core.extendIntegration(serverUtils.graphqlIntegration(getOptionsWithDefaults(options)), {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentGraphql(getOptionsWithDefaults(options));
    }
  });
});
const graphqlIntegration = core.defineIntegration(_graphqlIntegration);
function getOptionsWithDefaults(options) {
  return {
    ignoreResolveSpans: true,
    ignoreTrivialResolveSpans: true,
    useOperationNameForRootSpan: true,
    ...options
  };
}

exports.graphqlIntegration = graphqlIntegration;
exports.instrumentGraphql = instrumentGraphql;
//# sourceMappingURL=index.js.map
