import { KnexInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { isOrchestrionInjected, knexChannelIntegration } from '@sentry/server-utils/orchestrion';

const INTEGRATION_NAME = "Knex";
const instrumentKnex = generateInstrumentOnce(INTEGRATION_NAME, () => new KnexInstrumentation());
const _knexIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (isOrchestrionInjected()) {
        knexChannelIntegration().setupOnce?.();
      } else {
        instrumentKnex();
      }
    }
  };
});
const knexIntegration = defineIntegration(_knexIntegration);

export { instrumentKnex, knexIntegration };
//# sourceMappingURL=index.js.map
