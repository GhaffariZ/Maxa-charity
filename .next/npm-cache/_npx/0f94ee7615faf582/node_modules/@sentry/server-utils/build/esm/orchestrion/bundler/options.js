import { SENTRY_INSTRUMENTATIONS } from '../config/index.js';
import { subscribeInjectionOptions } from './subscribeInjection.js';

function externalEntryMatchesModule(entry, moduleName) {
  return entry === moduleName || entry.startsWith(`${moduleName}/`);
}
function externalizedModulesWarning(externalizedModules) {
  return `The following packages are marked as external in your bundler configuration but need to be bundled for Sentry instrumentation to work: ${externalizedModules.join(", ")}. Remove them from your bundler's "external" configuration, or use the Sentry Node SDK's runtime instrumentation instead.`;
}
function orchestrionTransformOptions(options) {
  const subscribeInjection = options.injectChannelSubscribers ? subscribeInjectionOptions() : void 0;
  const instrumentations = [
    ...SENTRY_INSTRUMENTATIONS,
    ...options.instrumentations || [],
    ...subscribeInjection?.instrumentations || []
  ];
  const customTransforms = { ...options.customTransforms, ...subscribeInjection?.customTransforms };
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

export { externalEntryMatchesModule, externalizedModulesWarning, orchestrionTransformOptions };
//# sourceMappingURL=options.js.map
