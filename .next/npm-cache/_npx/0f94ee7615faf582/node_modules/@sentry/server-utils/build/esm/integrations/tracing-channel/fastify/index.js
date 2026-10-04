import { defineIntegration } from '@sentry/core';
import { instrumentFastify as instrumentFastify$1 } from './instrumentation.js';
import { INTEGRATION_NAME, defaultShouldHandleError } from './utils.js';
import { subscribeToFastifyErrorChannel, handleFastifyError as handleFastifyError$1 } from './errors.js';

const _fastifyIntegration = (({ shouldHandleError } = {}) => {
  let _shouldHandleError;
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      _shouldHandleError = shouldHandleError || defaultShouldHandleError;
      subscribeToFastifyErrorChannel();
      instrumentFastify$1();
    },
    getShouldHandleError() {
      return _shouldHandleError;
    },
    setShouldHandleError(shouldHandleError2) {
      _shouldHandleError = shouldHandleError2;
    }
  };
});
const fastifyIntegration = defineIntegration(_fastifyIntegration);
const instrumentFastify = instrumentFastify$1;
const handleFastifyError = handleFastifyError$1;

export { fastifyIntegration, handleFastifyError, instrumentFastify };
//# sourceMappingURL=index.js.map
