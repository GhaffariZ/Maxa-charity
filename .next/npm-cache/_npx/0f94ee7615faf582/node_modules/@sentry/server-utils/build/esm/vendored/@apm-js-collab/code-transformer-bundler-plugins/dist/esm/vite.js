import { t as codeTransformerRollup } from './rollup-OTS4ktWb.js';

//#region src/vite.ts
function codeTransformerVite(options) {
	return {
		enforce: "pre",
		...codeTransformerRollup(options)
	};
}

export { codeTransformerVite as default };
//# sourceMappingURL=vite.js.map
