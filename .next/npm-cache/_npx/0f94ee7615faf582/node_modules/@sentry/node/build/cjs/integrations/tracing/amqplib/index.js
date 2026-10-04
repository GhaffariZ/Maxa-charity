Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const instrumentation = require('./vendored/instrumentation.js');

const INTEGRATION_NAME = "Amqplib";
const instrumentAmqplib = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new instrumentation.AmqplibInstrumentation());
const _amqplibIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentAmqplib();
    }
  };
});
const amqplibIntegration = core.defineIntegration(_amqplibIntegration);

exports.amqplibIntegration = amqplibIntegration;
exports.instrumentAmqplib = instrumentAmqplib;
//# sourceMappingURL=index.js.map
