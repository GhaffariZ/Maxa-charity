import { instrumentUndici } from '@sentry/node-core';
import { defineIntegration, getClient, hasSpansEnabled } from '@sentry/core';

const _nativeNodeFetchIntegration = ((options = {}) => {
  return {
    name: "NodeFetch",
    setupOnce() {
      const clientOptions = getClient()?.getOptions();
      instrumentUndici({
        ...options,
        spans: _shouldInstrumentSpans(options, clientOptions)
      });
    }
  };
});
const nativeNodeFetchIntegration = defineIntegration(_nativeNodeFetchIntegration);
function _shouldInstrumentSpans(options, clientOptions = {}) {
  return options.spans ?? (!clientOptions.skipOpenTelemetrySetup && hasSpansEnabled(clientOptions));
}

export { nativeNodeFetchIntegration };
//# sourceMappingURL=node-fetch.js.map
