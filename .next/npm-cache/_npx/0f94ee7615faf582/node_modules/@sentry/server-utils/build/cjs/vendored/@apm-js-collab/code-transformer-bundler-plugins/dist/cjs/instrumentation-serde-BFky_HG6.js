const instrumentationSerdeBFky_HG6 = require('../../../../../_virtual/instrumentation-serde-BFky_HG6.js');

(function (exports) {
	//#region src/instrumentation-serde.ts
	function isSerializedRegExp(value) {
		return typeof value === "object" && value !== null && value.type === "RegExp" && typeof value.source === "string" && typeof value.flags === "string";
	}
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
	/**
	* Revives serialized `{ source, flags }` file paths back into `RegExp`
	* instances. Accepts a mix of serialized and native configs so callers can
	* pass user-supplied options straight through.
	*/
	function deserializeInstrumentations(configs) {
		return configs.map((config) => {
			const { filePath } = config.module;
			if (typeof filePath === "string" || filePath instanceof RegExp) return config;
			if (!isSerializedRegExp(filePath)) throw new Error(`Invalid instrumentation config for module '${config.module.name}': 'filePath' must be a string, a RegExp, or a { type: 'RegExp', source, flags } object`);
			return {
				...config,
				module: {
					...config.module,
					filePath: new RegExp(filePath.source, filePath.flags)
				}
			};
		});
	}
	//#endregion
	Object.defineProperty(exports, "deserializeInstrumentations", {
		enumerable: true,
		get: function() {
			return deserializeInstrumentations;
		}
	});
	Object.defineProperty(exports, "serializeInstrumentations", {
		enumerable: true,
		get: function() {
			return serializeInstrumentations;
		}
	});

	
} (instrumentationSerdeBFky_HG6.__exports));
//# sourceMappingURL=instrumentation-serde-BFky_HG6.js.map
