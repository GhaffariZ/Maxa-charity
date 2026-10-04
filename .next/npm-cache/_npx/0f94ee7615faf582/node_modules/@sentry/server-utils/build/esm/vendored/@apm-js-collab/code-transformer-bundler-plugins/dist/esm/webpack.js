import { n as serializeInstrumentations } from './instrumentation-serde-C-Xxv-jj.js';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

//#region src/webpack.ts
var LOADER_PATH = resolve(dirname(fileURLToPath(import.meta.url)), "..", "cjs", "webpack-loader.cjs");
var DIAGNOSTICS_STATE_KEY = "__codeTransformerWebpackDiagnostics";
/**
* Asset names of the chunk holding each entry module. Deliberately not
* `entrypoint.getFiles()`, which also lists the initial chunks an entry
* depends on, and not `compilation.getAssets()`, which lists async chunks too.
*/
function entryAssetNames(compilation) {
	const names = /* @__PURE__ */ new Set();
	for (const entrypoint of compilation.entrypoints.values()) {
		const chunk = entrypoint.getEntrypointChunk?.();
		for (const file of chunk?.files ?? []) names.add(file);
	}
	return names;
}
var CodeTransformerWebpackPlugin = class {
	constructor(options) {
		this.options = options;
	}
	apply(compiler) {
		const webpack = compiler.webpack;
		compiler.options.module = compiler.options.module || { rules: [] };
		compiler.options.module.rules = compiler.options.module.rules || [];
		compiler.options.module.rules.unshift({
			test: /\.(c|m)?jsx?$|\.tsx?$/,
			enforce: "pre",
			use: [{
				loader: LOADER_PATH,
				options: {
					instrumentations: serializeInstrumentations(this.options.instrumentations),
					...this.options.dcModule ? { dcModule: this.options.dcModule } : {}
				}
			}]
		});
		if (this.options.injectDiagnostics) {
			const ConcatSource = webpack?.sources?.ConcatSource;
			if (ConcatSource && webpack?.Compilation) compiler.hooks.thisCompilation.tap("code-transformer", (compilation) => {
				compilation[DIAGNOSTICS_STATE_KEY] = {
					transformedModules: /* @__PURE__ */ new Set(),
					failedModules: /* @__PURE__ */ new Set()
				};
				compilation.hooks.processAssets.tap({
					name: "code-transformer",
					stage: webpack.Compilation.PROCESS_ASSETS_STAGE_SUMMARIZE
				}, () => {
					const state = compilation[DIAGNOSTICS_STATE_KEY];
					if (!state) return;
					const injectCode = this.options.injectDiagnostics?.({
						transformedModules: Array.from(state.transformedModules),
						failedModules: Array.from(state.failedModules)
					});
					if (!injectCode) return;
					for (const assetName of entryAssetNames(compilation)) {
						if (!/\.(js|ts|jsx|tsx|mjs|cjs)(\?[^?]*)?(#[^#]*)?$/.test(assetName)) continue;
						compilation.updateAsset(assetName, (source) => new ConcatSource(injectCode, source));
					}
				});
			});
		}
	}
};
function codeTransformerWebpack(options) {
	return new CodeTransformerWebpackPlugin(options);
}

export { codeTransformerWebpack as default };
//# sourceMappingURL=webpack.js.map
