import { defineIntegration, waitForTracingChannelBinding } from '@sentry/core';
import * as diagnosticsChannel from 'node:diagnostics_channel';
import { subscribeMysql2DiagnosticChannels } from './mysql2-dc-subscriber.js';

const _mysql2Integration = (() => {
  return {
    name: "Mysql2",
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeMysql2DiagnosticChannels(diagnosticsChannel.tracingChannel);
      });
    }
  };
});
const mysql2Integration = defineIntegration(_mysql2Integration);

export { mysql2Integration };
//# sourceMappingURL=index.js.map
