import { GenericPoolInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';

const INTEGRATION_NAME = "GenericPool";
const instrumentGenericPool = generateInstrumentOnce(INTEGRATION_NAME, () => new GenericPoolInstrumentation({}));
const _genericPoolIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentGenericPool();
    }
  };
});
const genericPoolIntegration = defineIntegration(_genericPoolIntegration);

export { genericPoolIntegration, instrumentGenericPool };
//# sourceMappingURL=index.js.map
