Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const nodeCore = require('@sentry/node-core');
const core = require('@sentry/core');

const _nativeNodeFetchIntegration = ((options = {}) => {
  return {
    name: "NodeFetch",
    setupOnce() {
      const clientOptions = core.getClient()?.getOptions();
      nodeCore.instrumentUndici({
        ...options,
        spans: _shouldInstrumentSpans(options, clientOptions)
      });
    }
  };
});
const nativeNodeFetchIntegration = core.defineIntegration(_nativeNodeFetchIntegration);
function _shouldInstrumentSpans(options, clientOptions = {}) {
  return options.spans ?? (!clientOptions.skipOpenTelemetrySetup && core.hasSpansEnabled(clientOptions));
}

exports.nativeNodeFetchIntegration = nativeNodeFetchIntegration;
//# sourceMappingURL=node-fetch.js.map
