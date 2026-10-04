import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { isOrchestrionInjected, dataloaderChannelIntegration } from '@sentry/server-utils/orchestrion';
import { DataloaderInstrumentation } from './vendored/instrumentation.js';

const INTEGRATION_NAME = "Dataloader";
const instrumentDataloader = generateInstrumentOnce(INTEGRATION_NAME, () => new DataloaderInstrumentation());
const _dataloaderIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (isOrchestrionInjected()) {
        dataloaderChannelIntegration().setupOnce?.();
      } else {
        instrumentDataloader();
      }
    }
  };
});
const dataloaderIntegration = defineIntegration(_dataloaderIntegration);

export { dataloaderIntegration, instrumentDataloader };
//# sourceMappingURL=index.js.map
