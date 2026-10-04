Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('./vendored/instrumentation.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');

const INTEGRATION_NAME = "Mongo";
const instrumentMongo = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new instrumentation.MongoDBInstrumentation());
const _mongoIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentMongo();
    }
  };
});
const mongoIntegration = core.defineIntegration(_mongoIntegration);

exports.instrumentMongo = instrumentMongo;
exports.mongoIntegration = mongoIntegration;
//# sourceMappingURL=index.js.map
