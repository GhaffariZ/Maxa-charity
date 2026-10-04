import { createRequire } from 'node:module';
import { SENTRY_INSTRUMENTATIONS, instrumentedModuleNames } from '../config/index.js';
import codeTransformerWebpack from '../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/webpack.js';
import '../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/core-dC9TN3Ev.js';
import { n as serializeInstrumentations$1 } from '../../vendored/@apm-js-collab/code-transformer-bundler-plugins/dist/esm/instrumentation-serde-C-Xxv-jj.js';
import { orchestrionTransformOptions, externalizedModulesWarning, externalEntryMatchesModule } from './options.js';

const serializeInstrumentations = serializeInstrumentations$1;
function getOrchestrionRequire() {
  let nodeRequire;
  nodeRequire = createRequire(import.meta.url);
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
  return SENTRY_INSTRUMENTATIONS;
}
function externalizedWebpackModules(externals, moduleNames) {
  const entries = Array.isArray(externals) ? externals : [externals];
  return moduleNames.filter(
    (name) => entries.some((entry) => {
      if (typeof entry === "string") {
        return externalEntryMatchesModule(entry, name);
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
function sentryOrchestrionWebpackPlugin(options = {}) {
  const plugin = codeTransformerWebpack(orchestrionTransformOptions(options));
  const moduleNames = instrumentedModuleNames(options.instrumentations);
  const apply = plugin.apply.bind(plugin);
  plugin.apply = (compiler) => {
    const externalizedModules = externalizedWebpackModules(compiler.options.externals, moduleNames);
    if (externalizedModules.length > 0) {
      compiler.hooks.thisCompilation.tap("SentryOrchestrionExternalsCheck", (compilation) => {
        compilation.warnings.push(new compiler.webpack.WebpackError(externalizedModulesWarning(externalizedModules)));
      });
    }
    apply(compiler);
    fixupLoaderPath(compiler);
  };
  return plugin;
}

export { getOrchestrionLoaderPath, getSentryInstrumentations, resolveOrchestrionRuntimeRequest, sentryOrchestrionWebpackPlugin, serializeInstrumentations };
//# sourceMappingURL=webpack.js.map
