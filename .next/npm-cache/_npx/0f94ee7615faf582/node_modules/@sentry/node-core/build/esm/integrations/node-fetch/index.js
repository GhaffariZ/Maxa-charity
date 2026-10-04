import { defineIntegration } from '@sentry/core';
import { instrumentUndici } from './undici-instrumentation.js';

const _nativeNodeFetchIntegration = ((options = {}) => {
  return {
    name: "NodeFetch",
    setupOnce() {
      instrumentUndici(options);
    }
  };
});
const nativeNodeFetchIntegration = defineIntegration(_nativeNodeFetchIntegration);

export { nativeNodeFetchIntegration };
//# sourceMappingURL=index.js.map
