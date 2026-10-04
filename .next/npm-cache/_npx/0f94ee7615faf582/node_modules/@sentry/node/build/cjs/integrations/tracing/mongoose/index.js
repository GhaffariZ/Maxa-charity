Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const mongoose = require('./vendored/mongoose.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const serverUtils = require('@sentry/server-utils');

const INTEGRATION_NAME = "Mongoose";
const instrumentMongoose = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new mongoose.MongooseInstrumentation());
const _mongooseIntegration = (() => {
  return core.extendIntegration(serverUtils.mongooseIntegration(), {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentMongoose();
    }
  });
});
const mongooseIntegration = core.defineIntegration(_mongooseIntegration);

exports.instrumentMongoose = instrumentMongoose;
exports.mongooseIntegration = mongooseIntegration;
//# sourceMappingURL=index.js.map
