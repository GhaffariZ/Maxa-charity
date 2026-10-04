Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const instrumentation = require('./instrumentation.js');

function _interopNamespaceDefault(e) {
  const n = Object.create(null, { [Symbol.toStringTag]: { value: 'Module' } });
  if (e) {
    for (const k in e) {
      n[k] = e[k];
    }
  }
  n.default = e;
  return n;
}

const diagnosticsChannel__namespace = /*#__PURE__*/_interopNamespaceDefault(diagnosticsChannel);

const INTEGRATION_NAME = "Express";
const _expressChannelIntegration = ((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        instrumentation.instrumentExpress(options, diagnosticsChannel__namespace.tracingChannel);
      });
    },
    // Read back by `expressErrorHandler` in `@sentry/core`, which is what captures Express errors.
    getShouldHandleError() {
      return options.shouldHandleError;
    }
  };
});
const expressChannelIntegration = core.defineIntegration(_expressChannelIntegration);

exports.expressChannelIntegration = expressChannelIntegration;
//# sourceMappingURL=index.js.map
