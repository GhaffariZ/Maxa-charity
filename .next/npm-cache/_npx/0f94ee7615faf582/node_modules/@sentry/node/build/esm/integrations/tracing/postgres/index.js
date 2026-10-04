import { PgInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';

const INTEGRATION_NAME = "Postgres";
const instrumentPostgres = generateInstrumentOnce(
  INTEGRATION_NAME,
  PgInstrumentation,
  (options) => ({
    ignoreConnectSpans: options?.ignoreConnectSpans ?? false
  })
);
const _postgresIntegration = ((options) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentPostgres(options);
    }
  };
});
const postgresIntegration = defineIntegration(_postgresIntegration);

export { instrumentPostgres, postgresIntegration };
//# sourceMappingURL=index.js.map
