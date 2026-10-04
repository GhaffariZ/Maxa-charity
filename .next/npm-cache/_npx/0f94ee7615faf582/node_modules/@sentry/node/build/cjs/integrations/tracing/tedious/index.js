Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('./vendored/instrumentation.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');

const INTEGRATION_NAME = "Tedious";
const instrumentTedious = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new instrumentation.TediousInstrumentation({}));
const _tediousIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentTedious();
    }
  };
});
const tediousIntegration = core.defineIntegration(_tediousIntegration);

exports.instrumentTedious = instrumentTedious;
exports.tediousIntegration = tediousIntegration;
//# sourceMappingURL=index.js.map
