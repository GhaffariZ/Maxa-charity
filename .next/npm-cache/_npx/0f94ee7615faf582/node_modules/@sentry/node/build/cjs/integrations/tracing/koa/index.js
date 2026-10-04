Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('./vendored/instrumentation.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');

const INTEGRATION_NAME = "Koa";
const instrumentKoa = nodeCore.generateInstrumentOnce(
  INTEGRATION_NAME,
  instrumentation.KoaInstrumentation,
  (options = {}) => {
    return {
      ignoreLayersType: options.ignoreLayersType
    };
  }
);
const _koaIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentKoa(options);
    }
  };
});
const koaIntegration = core.defineIntegration(_koaIntegration);
const setupKoaErrorHandler = (app) => {
  app.use(async (ctx, next) => {
    try {
      await next();
    } catch (error) {
      core.captureException(error, {
        mechanism: {
          handled: false,
          type: "auto.middleware.koa"
        }
      });
      throw error;
    }
  });
  nodeCore.ensureIsWrapped(app.use, "koa");
};

exports.instrumentKoa = instrumentKoa;
exports.koaIntegration = koaIntegration;
exports.setupKoaErrorHandler = setupKoaErrorHandler;
//# sourceMappingURL=index.js.map
