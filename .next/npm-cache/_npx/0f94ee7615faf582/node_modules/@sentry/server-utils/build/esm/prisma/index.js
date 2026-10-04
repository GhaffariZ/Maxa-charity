import { defineIntegration } from '@sentry/core';
import { setGlobalTracingHelper } from './global.js';
import { ActiveTracingHelper } from './tracing-helper.js';

const INTEGRATION_NAME = "Prisma";
function instrumentPrisma(options) {
  setGlobalTracingHelper(
    new ActiveTracingHelper({
      ignoreSpanTypes: options?.instrumentationConfig?.ignoreSpanTypes ?? []
    })
  );
}
const _prismaIntegration = ((options) => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentPrisma(options);
    }
  };
});
const prismaIntegration = defineIntegration(_prismaIntegration);

export { instrumentPrisma, prismaIntegration };
//# sourceMappingURL=index.js.map
