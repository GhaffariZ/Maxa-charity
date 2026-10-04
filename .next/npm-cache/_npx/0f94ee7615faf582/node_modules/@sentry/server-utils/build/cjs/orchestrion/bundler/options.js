Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const index = require('../config/index.js');
const subscribeInjection = require('./subscribeInjection.js');

function externalEntryMatchesModule(entry, moduleName) {
  return entry === moduleName || entry.startsWith(`${moduleName}/`);
}
function externalizedModulesWarning(externalizedModules) {
  return `The following packages are marked as external in your bundler configuration but need to be bundled for Sentry instrumentation to work: ${externalizedModules.join(", ")}. Remove them from your bundler's "external" configuration, or use the Sentry Node SDK's runtime instrumentation instead.`;
}
function orchestrionTransformOptions(options) {
  const subscribeInjection$1 = options.injectChannelSubscribers ? subscribeInjection.subscribeInjectionOptions() : void 0;
  const instrumentations = [
    ...index.SENTRY_INSTRUMENTATIONS,
    ...options.instrumentations || [],
    ...subscribeInjection$1?.instrumentations || []
  ];
  const customTransforms = { ...options.customTransforms, ...subscribeInjection$1?.customTransforms };
  if (options.shouldInjectDiagnostics === false) {
    return {
      instrumentations,
      customTransforms
    };
  }
  return {
    instrumentations,
    customTransforms,
    injectDiagnostics: (diag) => {
      return `(globalThis.__SENTRY_ORCHESTRION__=globalThis.__SENTRY_ORCHESTRION__||{}).bundler=${JSON.stringify(diag.transformedModules)};`;
    }
  };
}

exports.externalEntryMatchesModule = externalEntryMatchesModule;
exports.externalizedModulesWarning = externalizedModulesWarning;
exports.orchestrionTransformOptions = orchestrionTransformOptions;
//# sourceMappingURL=options.js.map
