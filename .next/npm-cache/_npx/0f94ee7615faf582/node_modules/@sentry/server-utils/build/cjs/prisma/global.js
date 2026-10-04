Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const majorVersion = "7";
const GLOBAL_INSTRUMENTATION_KEY = "PRISMA_INSTRUMENTATION";
const GLOBAL_VERSIONED_INSTRUMENTATION_KEY = `V${majorVersion}_PRISMA_INSTRUMENTATION`;
const globalThisWithPrismaInstrumentation = globalThis;
function setGlobalTracingHelper(helper) {
  const globalValue = { helper };
  globalThisWithPrismaInstrumentation[GLOBAL_VERSIONED_INSTRUMENTATION_KEY] = globalValue;
  globalThisWithPrismaInstrumentation[GLOBAL_INSTRUMENTATION_KEY] = globalValue;
}

exports.setGlobalTracingHelper = setGlobalTracingHelper;
//# sourceMappingURL=global.js.map
