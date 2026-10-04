import { defineIntegration } from '@sentry/core';
import { enableFsInstrumentation } from './vendored/instrumentation.js';

const INTEGRATION_NAME = "FileSystem";
const fsIntegration = defineIntegration((options = {}) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      enableFsInstrumentation(options);
    }
  };
});

export { fsIntegration };
//# sourceMappingURL=index.js.map
