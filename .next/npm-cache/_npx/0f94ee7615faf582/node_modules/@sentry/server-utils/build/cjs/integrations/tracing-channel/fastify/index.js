Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const instrumentation = require('./instrumentation.js');
const utils = require('./utils.js');
const errors = require('./errors.js');

const _fastifyIntegration = (({ shouldHandleError } = {}) => {
  let _shouldHandleError;
  return {
    name: utils.INTEGRATION_NAME,
    setupOnce() {
      _shouldHandleError = shouldHandleError || utils.defaultShouldHandleError;
      errors.subscribeToFastifyErrorChannel();
      instrumentation.instrumentFastify();
    },
    getShouldHandleError() {
      return _shouldHandleError;
    },
    setShouldHandleError(shouldHandleError2) {
      _shouldHandleError = shouldHandleError2;
    }
  };
});
const fastifyIntegration = core.defineIntegration(_fastifyIntegration);
const instrumentFastify = instrumentation.instrumentFastify;
const handleFastifyError = errors.handleFastifyError;

exports.fastifyIntegration = fastifyIntegration;
exports.handleFastifyError = handleFastifyError;
exports.instrumentFastify = instrumentFastify;
//# sourceMappingURL=index.js.map
