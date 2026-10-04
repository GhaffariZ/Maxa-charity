Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const global = require('./global.js');
const tracingHelper = require('./tracing-helper.js');

const INTEGRATION_NAME = "Prisma";
function instrumentPrisma(options) {
  global.setGlobalTracingHelper(
    new tracingHelper.ActiveTracingHelper({
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
const prismaIntegration = core.defineIntegration(_prismaIntegration);

exports.instrumentPrisma = instrumentPrisma;
exports.prismaIntegration = prismaIntegration;
//# sourceMappingURL=index.js.map
