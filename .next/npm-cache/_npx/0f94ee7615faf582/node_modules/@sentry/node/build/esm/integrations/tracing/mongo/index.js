import { MongoDBInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';

const INTEGRATION_NAME = "Mongo";
const instrumentMongo = generateInstrumentOnce(INTEGRATION_NAME, () => new MongoDBInstrumentation());
const _mongoIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentMongo();
    }
  };
});
const mongoIntegration = defineIntegration(_mongoIntegration);

export { instrumentMongo, mongoIntegration };
//# sourceMappingURL=index.js.map
