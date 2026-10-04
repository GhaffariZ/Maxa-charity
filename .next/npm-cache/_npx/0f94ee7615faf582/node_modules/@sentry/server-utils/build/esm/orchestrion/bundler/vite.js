import codeTransformerVite from '../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/vite.js';
import { instrumentedModuleNames } from '../config/index.js';
import { externalEntryMatchesModule, externalizedModulesWarning, orchestrionTransformOptions } from './options.js';

function sentryOrchestrionPlugin(options = {}) {
  return {
    ...codeTransformerVite(orchestrionTransformOptions(options)),
    applyToEnvironment(environment) {
      return environment.config.consumer === "server";
    },
    config() {
      return { ssr: { noExternal: instrumentedModuleNames(options.instrumentations) } };
    },
    configResolved(config) {
      const external = config.ssr?.external;
      if (!Array.isArray(external)) {
        return;
      }
      const moduleNames = instrumentedModuleNames(options.instrumentations);
      const externalizedModules = moduleNames.filter(
        (name) => external.some((entry) => externalEntryMatchesModule(entry, name))
      );
      if (externalizedModules.length > 0) {
        config.logger.warn(`[Sentry] ${externalizedModulesWarning(externalizedModules)}`);
      }
    }
  };
}

export { sentryOrchestrionPlugin };
//# sourceMappingURL=vite.js.map
