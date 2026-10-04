import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding } from '@sentry/core';
import { instrumentExpress } from './instrumentation.js';

const INTEGRATION_NAME = "Express";
const _expressChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        instrumentExpress(options, diagnosticsChannel.tracingChannel);
      });
    },
    // Read back by `expressErrorHandler` in `@sentry/core`, which is what captures Express errors.
    getShouldHandleError() {
      return options.shouldHandleError;
    }
  };
});
const expressChannelIntegration = defineIntegration(_expressChannelIntegration);

export { expressChannelIntegration };
//# sourceMappingURL=index.js.map
