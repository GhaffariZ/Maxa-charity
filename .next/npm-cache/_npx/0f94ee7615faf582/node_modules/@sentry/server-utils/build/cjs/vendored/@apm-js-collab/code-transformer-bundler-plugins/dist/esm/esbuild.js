Object.defineProperties(exports, { __esModule: { value: true }, [Symbol.toStringTag]: { value: 'Module' } });

const coreDC9TN3Ev = require('./core-dC9TN3Ev.js');
const require$$0 = require('node:path');
const require$$4 = require('node:fs');

//#region src/esbuild.ts
var filter = /\.(cjs|mjs|cts|mts|tsx|jsx|ts|js)$/;
var STDOUT = "<stdout>";
function shouldInjectOutput(path) {
	return path === STDOUT || coreDC9TN3Ev.i(path);
}
/**
* esbuild reports source paths with symlinks resolved, so entry point paths are
* canonicalised before comparison. Falls back to the resolved path for inputs
* that do not exist on disk, such as virtual modules from other plugins.
*/
function canonicalPath(base, path) {
	const resolved = require$$0.resolve(base, path);
	try {
		return require$$4.realpathSync(resolved);
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
	for (const [outputPath, output] of Object.entries(metafile.outputs)) if (output.entryPoint && entryPoints.has(canonicalPath(base, output.entryPoint))) paths.add(require$$0.resolve(base, outputPath));
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
				transformer = coreDC9TN3Ev.r(options);
			});
			build.onLoad({ filter }, (args) => {
				const code = require$$4.readFileSync(args.path, "utf8");
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
						if (file.path !== STDOUT && !entryPaths.has(require$$0.resolve(file.path))) continue;
						const code = decoder.decode(file.contents);
						file.contents = encoder.encode(injectCodeRaw + code);
					}
					return;
				}
				for (const outputPath of entryPaths) {
					if (!shouldInjectOutput(outputPath)) continue;
					require$$4.writeFileSync(outputPath, injectCodeRaw + require$$4.readFileSync(outputPath, "utf8"));
				}
			});
		}
	};
}

exports.default = codeTransformerEsbuild;
//# sourceMappingURL=esbuild.js.map
