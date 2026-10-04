Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

//#region src/instrumentation-serde.ts
/**
* Converts any `RegExp` `module.filePath` into a plain `{ source, flags }`
* object so the configs can be passed where only JSON-serializable values are
* allowed (e.g. Turbopack loader options). Configs that are already
* serializable are returned unchanged.
*/
function serializeInstrumentations(configs) {
	return configs.map((config) => {
		const { filePath } = config.module;
		if (!(filePath instanceof RegExp)) return config;
		return {
			...config,
			module: {
				...config.module,
				filePath: {
					type: "RegExp",
					source: filePath.source,
					flags: filePath.flags
				}
			}
		};
	});
}

exports.n = serializeInstrumentations;
//# sourceMappingURL=instrumentation-serde-C-Xxv-jj.js.map
