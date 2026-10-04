import { defineIntegration, waitForTracingChannelBinding } from '@sentry/core';
import * as diagnosticsChannel from 'node:diagnostics_channel';
import { subscribeGraphqlDiagnosticChannels } from './graphql-dc-subscriber.js';

const _graphqlIntegration = ((options = {}) => {
  return {
    name: "Graphql",
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeGraphqlDiagnosticChannels(diagnosticsChannel.tracingChannel, options);
      });
    }
  };
});
const graphqlIntegration = defineIntegration(_graphqlIntegration);

export { graphqlIntegration };
//# sourceMappingURL=index.js.map
