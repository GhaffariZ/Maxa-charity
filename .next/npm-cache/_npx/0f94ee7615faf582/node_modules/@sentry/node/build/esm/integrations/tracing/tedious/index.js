import { TediousInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';

const INTEGRATION_NAME = "Tedious";
const instrumentTedious = generateInstrumentOnce(INTEGRATION_NAME, () => new TediousInstrumentation({}));
const _tediousIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentTedious();
    }
  };
});
const tediousIntegration = defineIntegration(_tediousIntegration);

export { instrumentTedious, tediousIntegration };
//# sourceMappingURL=index.js.map
