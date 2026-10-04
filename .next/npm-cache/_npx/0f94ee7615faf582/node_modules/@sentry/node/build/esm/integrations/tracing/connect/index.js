import { ConnectInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration, captureException } from '@sentry/core';
import { generateInstrumentOnce, ensureIsWrapped } from '@sentry/node-core';

const INTEGRATION_NAME = "Connect";
const instrumentConnect = generateInstrumentOnce(INTEGRATION_NAME, () => new ConnectInstrumentation());
const _connectIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentConnect();
    }
  };
});
const connectIntegration = defineIntegration(_connectIntegration);
function connectErrorMiddleware(err, req, res, next) {
  captureException(err, {
    mechanism: {
      handled: false,
      type: "auto.middleware.connect"
    }
  });
  next(err);
}
const setupConnectErrorHandler = (app) => {
  app.use(connectErrorMiddleware);
  ensureIsWrapped(app.use, "connect");
};

export { connectIntegration, instrumentConnect, setupConnectErrorHandler };
//# sourceMappingURL=index.js.map
