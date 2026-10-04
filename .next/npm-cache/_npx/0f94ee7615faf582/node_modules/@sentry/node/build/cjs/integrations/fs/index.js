Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const instrumentation = require('./vendored/instrumentation.js');

const INTEGRATION_NAME = "FileSystem";
const fsIntegration = core.defineIntegration((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentation.enableFsInstrumentation(options);
    }
  };
});

exports.fsIntegration = fsIntegration;
//# sourceMappingURL=index.js.map
