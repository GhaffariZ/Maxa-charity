Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const instrumentation = require('./v3/instrumentation.js');
const serverUtils = require('@sentry/server-utils');

const INTEGRATION_NAME = "Fastify";
const instrumentFastifyV3 = nodeCore.generateInstrumentOnce(
  `${INTEGRATION_NAME}.v3`,
  () => new instrumentation.FastifyInstrumentationV3()
);
function getFastifyIntegration() {
  const client = core.getClient();
  if (!client) {
    return void 0;
  } else {
    return client.getIntegrationByName(INTEGRATION_NAME);
  }
}
const _fastifyIntegration = ((options) => {
  const parentIntegration = serverUtils.fastifyIntegration(options);
  return core.extendIntegration(parentIntegration, {
    setupOnce() {
      instrumentFastifyV3();
    }
  });
});
const fastifyIntegration = core.defineIntegration(
  (options = {}) => _fastifyIntegration(options)
);
function setupFastifyErrorHandler(fastify, options) {
  if (options?.shouldHandleError) {
    getFastifyIntegration()?.setShouldHandleError(options.shouldHandleError);
  }
  const plugin = Object.assign(
    function(fastify2, _options, done) {
      fastify2.addHook("onError", async (request, reply, error) => {
        serverUtils.handleFastifyError.call(serverUtils.handleFastifyError, error, request, reply, "onError-hook");
      });
      done();
    },
    {
      [/* @__PURE__ */ Symbol.for("skip-override")]: true,
      [/* @__PURE__ */ Symbol.for("fastify.display-name")]: "sentry-fastify-error-handler"
    }
  );
  fastify.register(plugin);
}

exports.instrumentFastify = serverUtils.instrumentFastify;
exports.fastifyIntegration = fastifyIntegration;
exports.instrumentFastifyV3 = instrumentFastifyV3;
exports.setupFastifyErrorHandler = setupFastifyErrorHandler;
//# sourceMappingURL=index.js.map
