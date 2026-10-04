Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const firebaseInstrumentation = require('./otel/firebaseInstrumentation.js');

const INTEGRATION_NAME = "Firebase";
const instrumentFirebase = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new firebaseInstrumentation.FirebaseInstrumentation());
const _firebaseIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentFirebase();
    }
  };
});
const firebaseIntegration = core.defineIntegration(_firebaseIntegration);

exports.firebaseIntegration = firebaseIntegration;
exports.instrumentFirebase = instrumentFirebase;
//# sourceMappingURL=firebase.js.map
