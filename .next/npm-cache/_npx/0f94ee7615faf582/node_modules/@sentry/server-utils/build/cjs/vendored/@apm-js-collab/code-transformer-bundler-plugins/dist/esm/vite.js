Object.defineProperties(exports, { __esModule: { value: true }, [Symbol.toStringTag]: { value: 'Module' } });

const rollupOTS4ktWb = require('./rollup-OTS4ktWb.js');

//#region src/vite.ts
function codeTransformerVite(options) {
	return {
		enforce: "pre",
		...rollupOTS4ktWb.t(options)
	};
}

exports.default = codeTransformerVite;
//# sourceMappingURL=vite.js.map
