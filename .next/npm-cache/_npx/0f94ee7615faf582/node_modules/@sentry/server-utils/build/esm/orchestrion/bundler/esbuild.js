import codeTransformerEsbuild from '../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/esbuild.js';
import { escapeStringForRegex } from '@sentry/core';
import { instrumentedModuleNames } from '../config/index.js';
import { orchestrionTransformOptions, externalizedModulesWarning, externalEntryMatchesModule } from './options.js';

function matchesEsbuildExternal(entry, moduleName) {
  if (entry.includes("*")) {
    return new RegExp(`^${entry.split("*").map(escapeStringForRegex).join(".*")}$`).test(moduleName);
  }
  return externalEntryMatchesModule(entry, moduleName);
}
function sentryOrchestrionPlugin(options = {}) {
  const plugin = codeTransformerEsbuild(orchestrionTransformOptions(options));
  const moduleNames = instrumentedModuleNames(options.instrumentations);
  const setup = plugin.setup;
  return {
    ...plugin,
    setup(build) {
      const external = build.initialOptions.external || [];
      const externalizedModules = moduleNames.filter(
        (name) => external.some((entry) => matchesEsbuildExternal(entry, name))
      );
      if (externalizedModules.length > 0) {
        build.onStart(() => ({ warnings: [{ text: externalizedModulesWarning(externalizedModules) }] }));
      }
      return setup(build);
    }
  };
}

export { sentryOrchestrionPlugin };
//# sourceMappingURL=esbuild.js.map
