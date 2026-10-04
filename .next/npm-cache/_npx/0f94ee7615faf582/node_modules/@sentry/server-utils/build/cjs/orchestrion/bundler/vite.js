Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const vite = require('../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/vite.js');
const index = require('../config/index.js');
const options = require('./options.js');

function sentryOrchestrionPlugin(options$1 = {}) {
  return {
    ...vite.default(options.orchestrionTransformOptions(options$1)),
    applyToEnvironment(environment) {
      return environment.config.consumer === "server";
    },
    config() {
      return { ssr: { noExternal: index.instrumentedModuleNames(options$1.instrumentations) } };
    },
    configResolved(config) {
      const external = config.ssr?.external;
      if (!Array.isArray(external)) {
        return;
      }
      const moduleNames = index.instrumentedModuleNames(options$1.instrumentations);
      const externalizedModules = moduleNames.filter(
        (name) => external.some((entry) => options.externalEntryMatchesModule(entry, name))
      );
      if (externalizedModules.length > 0) {
        config.logger.warn(`[Sentry] ${options.externalizedModulesWarning(externalizedModules)}`);
      }
    }
  };
}

exports.sentryOrchestrionPlugin = sentryOrchestrionPlugin;
//# sourceMappingURL=vite.js.map
