import { defineIntegration, waitForTracingChannelBinding } from '@sentry/core';
import * as diagnosticsChannel from 'node:diagnostics_channel';
import { subscribeMongooseDiagnosticChannels } from './mongoose-dc-subscriber.js';

const _mongooseIntegration = (() => {
  return {
    name: "Mongoose",
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeMongooseDiagnosticChannels(diagnosticsChannel.tracingChannel);
      });
    }
  };
});
const mongooseIntegration = defineIntegration(_mongooseIntegration);

export { mongooseIntegration };
//# sourceMappingURL=index.js.map
