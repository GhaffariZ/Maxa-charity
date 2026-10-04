import { r as createCodeTransformer, i as isJsFile } from './core-dC9TN3Ev.js';
import { resolve } from 'node:path';
import { readFileSync, writeFileSync, realpathSync } from 'node:fs';

//#region src/esbuild.ts
var filter = /\.(cjs|mjs|cts|mts|tsx|jsx|ts|js)$/;
var STDOUT = "<stdout>";
function shouldInjectOutput(path) {
	return path === STDOUT || isJsFile(path);
}
/**
* esbuild reports source paths with symlinks resolved, so entry point paths are
* canonicalised before comparison. Falls back to the resolved path for inputs
* that do not exist on disk, such as virtual modules from other plugins.
*/
function canonicalPath(base, path) {
	const resolved = resolve(base, path);
	try {
		return realpathSync(resolved);
	} catch (_) {
		return resolved;
	}
}
/** Canonical paths of the entry points the build was configured with. */
function configuredEntryPoints(initialOptions) {
	const { entryPoints, absWorkingDir } = initialOptions;
	const base = absWorkingDir ?? process.cwd();
	const paths = /* @__PURE__ */ new Set();
	if (Array.isArray(entryPoints)) for (const entry of entryPoints) paths.add(canonicalPath(base, typeof entry === "string" ? entry : entry.in));
	else if (entryPoints) for (const entry of Object.values(entryPoints)) paths.add(canonicalPath(base, entry));
	return paths;
}
/**
* Absolute paths of the outputs that correspond to a configured entry point.
*
* A metafile output carries an `entryPoint` for dynamically imported chunks as
* well as for real entry points, so the field alone would also match async
* chunks. Comparing against the configured entry points excludes those.
*
* Metafile keys and `entryPoint` values are relative to the working directory
* while `outputFiles[].path` is absolute, so everything is resolved first.
*/
function entryOutputPaths(metafile, initialOptions) {
	const entryPoints = configuredEntryPoints(initialOptions);
	const base = initialOptions.absWorkingDir ?? process.cwd();
	const paths = /* @__PURE__ */ new Set();
	for (const [outputPath, output] of Object.entries(metafile.outputs)) if (output.entryPoint && entryPoints.has(canonicalPath(base, output.entryPoint))) paths.add(resolve(base, outputPath));
	return paths;
}
function codeTransformerEsbuild(options) {
	const decoder = new TextDecoder();
	const encoder = new TextEncoder();
	return {
		name: "code-transformer",
		setup(build) {
			let transformer;
			build.onStart(() => {
				transformer = createCodeTransformer(options);
			});
			build.onLoad({ filter }, (args) => {
				const code = readFileSync(args.path, "utf8");
				const result = transformer.transform(code, args.path);
				if (!result) return null;
				return {
					contents: result.code,
					loader: "default"
				};
			});
			if (!options.injectDiagnostics) return;
			build.initialOptions.metafile = true;
			build.onEnd((result) => {
				if (result.errors.length > 0) return;
				if (!result.metafile) return;
				const injectCodeRaw = transformer.getCodeToInject();
				if (!injectCodeRaw) return;
				const entryPaths = entryOutputPaths(result.metafile, build.initialOptions);
				if (result.outputFiles) {
					for (const file of result.outputFiles) {
						if (!shouldInjectOutput(file.path)) continue;
						if (file.path !== STDOUT && !entryPaths.has(resolve(file.path))) continue;
						const code = decoder.decode(file.contents);
						file.contents = encoder.encode(injectCodeRaw + code);
					}
					return;
				}
				for (const outputPath of entryPaths) {
					if (!shouldInjectOutput(outputPath)) continue;
					writeFileSync(outputPath, injectCodeRaw + readFileSync(outputPath, "utf8"));
				}
			});
		}
	};
}

export { codeTransformerEsbuild as default };
//# sourceMappingURL=esbuild.js.map
