Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const rollupOTS4ktWb = require('../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/rollup-OTS4ktWb.js');
const index = require('../config/index.js');
const options = require('./options.js');

function sentryOrchestrionPlugin(options$1 = {}) {
  const moduleNames = index.instrumentedModuleNames(options$1.instrumentations);
  return {
    ...rollupOTS4ktWb.t(options.orchestrionTransformOptions(options$1)),
    buildStart(rollupOptions) {
      const externalizedModules = moduleNames.filter((name) => rollupOptions.external(name, void 0, false));
      if (externalizedModules.length > 0) {
        this.warn(options.externalizedModulesWarning(externalizedModules));
      }
    }
  };
}

exports.sentryOrchestrionPlugin = sentryOrchestrionPlugin;
//# sourceMappingURL=rollup.js.map
