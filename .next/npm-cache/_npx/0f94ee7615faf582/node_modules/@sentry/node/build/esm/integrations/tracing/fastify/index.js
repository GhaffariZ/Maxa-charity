import { defineIntegration, extendIntegration, getClient } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { FastifyInstrumentationV3 } from './v3/instrumentation.js';
import { fastifyIntegration as fastifyIntegration$1, handleFastifyError } from '@sentry/server-utils';
export { instrumentFastify } from '@sentry/server-utils';

const INTEGRATION_NAME = "Fastify";
const instrumentFastifyV3 = generateInstrumentOnce(
  `${INTEGRATION_NAME}.v3`,
  () => new FastifyInstrumentationV3()
);
function getFastifyIntegration() {
  const client = getClient();
  if (!client) {
    return void 0;
  } else {
    return client.getIntegrationByName(INTEGRATION_NAME);
  }
}
const _fastifyIntegration = ((options) => {
  const parentIntegration = fastifyIntegration$1(options);
  return extendIntegration(parentIntegration, {
    setupOnce() {
      instrumentFastifyV3();
    }
  });
});
const fastifyIntegration = defineIntegration(
  (options = {}) => _fastifyIntegration(options)
);
function setupFastifyErrorHandler(fastify, options) {
  if (options?.shouldHandleError) {
    getFastifyIntegration()?.setShouldHandleError(options.shouldHandleError);
  }
  const plugin = Object.assign(
    function(fastify2, _options, done) {
      fastify2.addHook("onError", async (request, reply, error) => {
        handleFastifyError.call(handleFastifyError, error, request, reply, "onError-hook");
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

export { fastifyIntegration, instrumentFastifyV3, setupFastifyErrorHandler };
//# sourceMappingURL=index.js.map
