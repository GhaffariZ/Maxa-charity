Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const index = require('../../../semifies/index.js');
const transformer = require('./transformer.js');

var __typeError = (msg) => {
  throw TypeError(msg);
};
var __accessCheck = (obj, member, msg) => member.has(obj) || __typeError("Cannot " + msg);
var __privateGet = (obj, member, getter) => (__accessCheck(obj, member, "read from private field"), getter ? getter.call(obj) : member.get(obj));
var __privateAdd = (obj, member, value) => member.has(obj) ? __typeError("Cannot add the same private member more than once") : member instanceof WeakSet ? member.add(obj) : member.set(obj, value);
var __privateSet = (obj, member, value, setter) => (__accessCheck(obj, member, "write to private field"), member.set(obj, value), value);
var _configs, _dcModule, _transformers, _customTransforms;
const semifies = index.semifies;
const { Transformer } = transformer.transformer;
class InstrumentationMatcher {
  /**
   * @param {object[]} configs - Array of instrumentation configuration objects.
   * @param {string} [dcModule] - The diagnostics_channel module specifier to inject.
   *   Defaults to `'diagnostics_channel'`.
   */
  constructor(configs, dcModule) {
    __privateAdd(this, _configs, []);
    __privateAdd(this, _dcModule, null);
    __privateAdd(this, _transformers, {});
    __privateAdd(this, _customTransforms, {});
    __privateSet(this, _configs, configs);
    __privateSet(this, _dcModule, dcModule || "diagnostics_channel");
  }
  /** Releases all cached transformers, freeing any associated resources. */
  free() {
    __privateSet(this, _transformers, {});
  }
  /**
   * Registers a custom transform function under the given operator name.
   *
   * Custom transforms override built-in ones when an instrumentation config
   * specifies the same `transform` value.
   *
   * @param {string} name - Operator name (e.g. `'traceSync'`).
   * @param {Function} fn - Transform function `(state, node, parent, ancestry) => void`.
   */
  addTransform(name, fn) {
    __privateGet(this, _customTransforms)[name] = fn;
  }
  /**
   * Returns a {@link Transformer} for the given module/file/version, or `undefined`
   * if no registered config matches.
   *
   * Results are cached by a `moduleName/filePath@version` key.
   *
   * @param {string} moduleName - The npm package name (e.g. `'express'`).
   * @param {string} version - The installed semver version string.
   * @param {string} filePath - The relative file path within the package.
   * @returns {import('./transformer').Transformer|undefined}
   */
  getTransformer(moduleName, version, filePath) {
    filePath = filePath.replace(/\\/g, "/");
    const id = `${moduleName}/${filePath}@${version}`;
    if (__privateGet(this, _transformers)[id]) return __privateGet(this, _transformers)[id];
    const configs = __privateGet(this, _configs).filter(
      ({ module: mod }) => mod.name === moduleName && (typeof mod.filePath === "string" ? mod.filePath === filePath : mod.filePath.test(filePath)) && semifies(version, mod.versionRange)
    );
    if (configs.length === 0) return;
    __privateGet(this, _transformers)[id] = new Transformer(
      moduleName,
      version,
      filePath,
      configs,
      __privateGet(this, _dcModule),
      __privateGet(this, _customTransforms)
    );
    return __privateGet(this, _transformers)[id];
  }
}
_configs = new WeakMap();
_dcModule = new WeakMap();
_transformers = new WeakMap();
_customTransforms = new WeakMap();
var matcher = { InstrumentationMatcher };

exports.matcher = matcher;
//# sourceMappingURL=matcher.js.map
