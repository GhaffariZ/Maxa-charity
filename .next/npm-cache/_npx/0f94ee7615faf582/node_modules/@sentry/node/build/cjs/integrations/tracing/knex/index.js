Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('./vendored/instrumentation.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const orchestrion = require('@sentry/server-utils/orchestrion');

const INTEGRATION_NAME = "Knex";
const instrumentKnex = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new instrumentation.KnexInstrumentation());
const _knexIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (orchestrion.isOrchestrionInjected()) {
        orchestrion.knexChannelIntegration().setupOnce?.();
      } else {
        instrumentKnex();
      }
    }
  };
});
const knexIntegration = core.defineIntegration(_knexIntegration);

exports.instrumentKnex = instrumentKnex;
exports.knexIntegration = knexIntegration;
//# sourceMappingURL=index.js.map
