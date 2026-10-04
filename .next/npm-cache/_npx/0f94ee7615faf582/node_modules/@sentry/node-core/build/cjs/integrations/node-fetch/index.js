Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const undiciInstrumentation = require('./undici-instrumentation.js');

const _nativeNodeFetchIntegration = ((options = {}) => {
  return {
    name: "NodeFetch",
    setupOnce() {
      undiciInstrumentation.instrumentUndici(options);
    }
  };
});
const nativeNodeFetchIntegration = core.defineIntegration(_nativeNodeFetchIntegration);

exports.nativeNodeFetchIntegration = nativeNodeFetchIntegration;
//# sourceMappingURL=index.js.map
