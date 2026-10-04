Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('./vendored/instrumentation.js');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const debugBuild = require('../../../debug-build.js');

const INTEGRATION_NAME = "Hapi";
const instrumentHapi = nodeCore.generateInstrumentOnce(INTEGRATION_NAME, () => new instrumentation.HapiInstrumentation());
const _hapiIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentHapi();
    }
  };
});
const hapiIntegration = core.defineIntegration(_hapiIntegration);
function isErrorEvent(event) {
  return !!(event && typeof event === "object" && "error" in event && event.error);
}
function sendErrorToSentry(errorData) {
  core.captureException(errorData, {
    mechanism: {
      type: "auto.function.hapi",
      handled: false
    }
  });
}
const hapiErrorPlugin = {
  name: "SentryHapiErrorPlugin",
  version: core.SDK_VERSION,
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  register: async function(serverArg) {
    const server = serverArg;
    server.events.on({ name: "request", channels: ["error"] }, (request, event) => {
      if (core.getIsolationScope() !== core.getDefaultIsolationScope()) {
        const route = request.route;
        if (route.path) {
          core.getIsolationScope().setTransactionName(`${route.method.toUpperCase()} ${route.path}`);
        }
      } else {
        debugBuild.DEBUG_BUILD && core.debug.warn("Isolation scope is still the default isolation scope - skipping setting transactionName");
      }
      if (isErrorEvent(event)) {
        sendErrorToSentry(event.error);
      }
    });
  }
};
async function setupHapiErrorHandler(server) {
  await server.register(hapiErrorPlugin);
  nodeCore.ensureIsWrapped(server.register, "hapi");
}

exports.hapiErrorPlugin = hapiErrorPlugin;
exports.hapiIntegration = hapiIntegration;
exports.instrumentHapi = instrumentHapi;
exports.setupHapiErrorHandler = setupHapiErrorHandler;
//# sourceMappingURL=index.js.map
