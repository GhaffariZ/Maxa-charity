Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('./vendored/instrumentation.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');

const INTEGRATION_NAME = "GenericPool";
const instrumentGenericPool = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new instrumentation.GenericPoolInstrumentation({}));
const _genericPoolIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentGenericPool();
    }
  };
});
const genericPoolIntegration = core.defineIntegration(_genericPoolIntegration);

exports.genericPoolIntegration = genericPoolIntegration;
exports.instrumentGenericPool = instrumentGenericPool;
//# sourceMappingURL=index.js.map
