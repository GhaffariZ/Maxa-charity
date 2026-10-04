Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const require$$1 = require('node:module');
const index = require('../config/index.js');
const webpack = require('../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/webpack.js');
require('../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/core-dC9TN3Ev.js');
const instrumentationSerdeCXxvJj = require('../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/instrumentation-serde-C-Xxv-jj.js');
const options = require('./options.js');

const serializeInstrumentations = instrumentationSerdeCXxvJj.n;
function getOrchestrionRequire() {
  let nodeRequire;
  nodeRequire = require$$1.createRequire(__filename);
  return nodeRequire;
}
function getOrchestrionLoaderPath() {
  return getOrchestrionRequire().resolve("@sentry/server-utils/orchestrion/webpack-loader");
}
function resolveOrchestrionRuntimeRequest(request) {
  try {
    return getOrchestrionRequire().resolve(request);
  } catch {
    return void 0;
  }
}
function getSentryInstrumentations() {
  return index.SENTRY_INSTRUMENTATIONS;
}
function externalizedWebpackModules(externals, moduleNames) {
  const entries = Array.isArray(externals) ? externals : [externals];
  return moduleNames.filter(
    (name) => entries.some((entry) => {
      if (typeof entry === "string") {
        return options.externalEntryMatchesModule(entry, name);
      }
      if (entry instanceof RegExp) {
        return entry.test(name);
      }
      if (entry && typeof entry === "object") {
        return name in entry;
      }
      return false;
    })
  );
}
function fixupLoaderPath(compiler) {
  for (const rule of compiler.options.module?.rules ?? []) {
    if (!rule || typeof rule !== "object" || !("use" in rule) || !Array.isArray(rule.use)) {
      continue;
    }
    for (const use of rule.use) {
      if (use && typeof use === "object" && typeof use.loader === "string" && use.loader.endsWith("webpack-loader.cjs")) {
        use.loader = getOrchestrionLoaderPath();
      }
    }
  }
}
function sentryOrchestrionWebpackPlugin(options$1 = {}) {
  const plugin = webpack.default(options.orchestrionTransformOptions(options$1));
  const moduleNames = index.instrumentedModuleNames(options$1.instrumentations);
  const apply = plugin.apply.bind(plugin);
  plugin.apply = (compiler) => {
    const externalizedModules = externalizedWebpackModules(compiler.options.externals, moduleNames);
    if (externalizedModules.length > 0) {
      compiler.hooks.thisCompilation.tap("SentryOrchestrionExternalsCheck", (compilation) => {
        compilation.warnings.push(new compiler.webpack.WebpackError(options.externalizedModulesWarning(externalizedModules)));
      });
    }
    apply(compiler);
    fixupLoaderPath(compiler);
  };
  return plugin;
}

exports.getOrchestrionLoaderPath = getOrchestrionLoaderPath;
exports.getSentryInstrumentations = getSentryInstrumentations;
exports.resolveOrchestrionRuntimeRequest = resolveOrchestrionRuntimeRequest;
exports.sentryOrchestrionWebpackPlugin = sentryOrchestrionWebpackPlugin;
exports.serializeInstrumentations = serializeInstrumentations;
//# sourceMappingURL=webpack.js.map
