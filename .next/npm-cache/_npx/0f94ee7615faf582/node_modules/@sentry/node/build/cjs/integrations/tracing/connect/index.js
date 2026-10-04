Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('./vendored/instrumentation.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');

const INTEGRATION_NAME = "Connect";
const instrumentConnect = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new instrumentation.ConnectInstrumentation());
const _connectIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentConnect();
    }
  };
});
const connectIntegration = core.defineIntegration(_connectIntegration);
function connectErrorMiddleware(err, req, res, next) {
  core.captureException(err, {
    mechanism: {
      handled: false,
      type: "auto.middleware.connect"
    }
  });
  next(err);
}
const setupConnectErrorHandler = (app) => {
  app.use(connectErrorMiddleware);
  nodeCore.ensureIsWrapped(app.use, "connect");
};

exports.connectIntegration = connectIntegration;
exports.instrumentConnect = instrumentConnect;
exports.setupConnectErrorHandler = setupConnectErrorHandler;
//# sourceMappingURL=index.js.map
