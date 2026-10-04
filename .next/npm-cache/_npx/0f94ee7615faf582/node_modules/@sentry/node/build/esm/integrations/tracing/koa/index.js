import { KoaInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration, captureException } from '@sentry/core';
import { generateInstrumentOnce, ensureIsWrapped } from '@sentry/node-core';

const INTEGRATION_NAME = "Koa";
const instrumentKoa = generateInstrumentOnce(
  INTEGRATION_NAME,
  KoaInstrumentation,
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
const koaIntegration = defineIntegration(_koaIntegration);
const setupKoaErrorHandler = (app) => {
  app.use(async (ctx, next) => {
    try {
      await next();
    } catch (error) {
      captureException(error, {
        mechanism: {
          handled: false,
          type: "auto.middleware.koa"
        }
      });
      throw error;
    }
  });
  ensureIsWrapped(app.use, "koa");
};

export { instrumentKoa, koaIntegration, setupKoaErrorHandler };
//# sourceMappingURL=index.js.map
