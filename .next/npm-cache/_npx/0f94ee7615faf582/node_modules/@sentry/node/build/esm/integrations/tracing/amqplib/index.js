import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { AmqplibInstrumentation } from './vendored/instrumentation.js';

const INTEGRATION_NAME = "Amqplib";
const instrumentAmqplib = generateInstrumentOnce(INTEGRATION_NAME, () => new AmqplibInstrumentation());
const _amqplibIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentAmqplib();
    }
  };
});
const amqplibIntegration = defineIntegration(_amqplibIntegration);

export { amqplibIntegration, instrumentAmqplib };
//# sourceMappingURL=index.js.map
