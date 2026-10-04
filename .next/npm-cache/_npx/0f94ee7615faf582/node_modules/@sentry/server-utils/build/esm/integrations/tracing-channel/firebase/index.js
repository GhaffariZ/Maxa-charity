import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding } from '@sentry/core';
import { instrumentFirebase } from './instrumentation.js';

const INTEGRATION_NAME = "Firebase";
const _firebaseChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        instrumentFirebase();
      });
    }
  };
});
const firebaseChannelIntegration = defineIntegration(_firebaseChannelIntegration);

export { firebaseChannelIntegration };
//# sourceMappingURL=index.js.map
