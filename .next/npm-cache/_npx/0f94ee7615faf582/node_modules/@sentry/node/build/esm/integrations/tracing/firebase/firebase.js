import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { FirebaseInstrumentation } from './otel/firebaseInstrumentation.js';

const INTEGRATION_NAME = "Firebase";
const instrumentFirebase = generateInstrumentOnce(INTEGRATION_NAME, () => new FirebaseInstrumentation());
const _firebaseIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentFirebase();
    }
  };
});
const firebaseIntegration = defineIntegration(_firebaseIntegration);

export { firebaseIntegration, instrumentFirebase };
//# sourceMappingURL=firebase.js.map
