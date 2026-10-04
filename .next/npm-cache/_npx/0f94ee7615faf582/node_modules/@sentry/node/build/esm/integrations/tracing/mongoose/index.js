import { MongooseInstrumentation } from './vendored/mongoose.js';
import { defineIntegration, extendIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { mongooseIntegration as mongooseIntegration$1 } from '@sentry/server-utils';

const INTEGRATION_NAME = "Mongoose";
const instrumentMongoose = generateInstrumentOnce(INTEGRATION_NAME, () => new MongooseInstrumentation());
const _mongooseIntegration = (() => {
  return extendIntegration(mongooseIntegration$1(), {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentMongoose();
    }
  });
});
const mongooseIntegration = defineIntegration(_mongooseIntegration);

export { instrumentMongoose, mongooseIntegration };
//# sourceMappingURL=index.js.map
