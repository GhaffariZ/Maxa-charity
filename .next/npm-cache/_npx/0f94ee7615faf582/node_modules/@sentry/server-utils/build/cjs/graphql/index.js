Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const diagnosticsChannel = require('node:diagnostics_channel');
const graphqlDcSubscriber = require('./graphql-dc-subscriber.js');

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

const _graphqlIntegration = ((options = {}) => {
  return {
    name: "Graphql",
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        graphqlDcSubscriber.subscribeGraphqlDiagnosticChannels(diagnosticsChannel__namespace.tracingChannel, options);
      });
    }
  };
});
const graphqlIntegration = core.defineIntegration(_graphqlIntegration);

exports.graphqlIntegration = graphqlIntegration;
//# sourceMappingURL=index.js.map
