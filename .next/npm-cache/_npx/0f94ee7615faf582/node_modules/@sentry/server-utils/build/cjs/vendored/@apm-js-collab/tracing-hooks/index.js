Object.defineProperties(exports, { __esModule: { value: true }, [Symbol.toStringTag]: { value: 'Module' } });

const _commonjsHelpers = require('../../../_virtual/_commonjsHelpers.js');
const index = require('../code-transformer/index.js');
const require$$1 = require('node:module');
const index$1 = require('../../module-details-from-path/index.js');
const require$$3 = require('node:url');
const getPackageVersion$1 = require('./lib/get-package-version.js');
const diagnostics = require('./lib/diagnostics.js');
const node = require('../../debug/src/node.js');
const require$$7 = require('node:os');
const require$$1$1 = require('node:path');
const require$$0 = require('node:fs');

const { create } = index.codeTransformer;
const Module = require$$1;
const parse = index$1.moduleDetailsFromPath;
const { pathToFileURL } = require$$3;
const getPackageVersion = getPackageVersion$1.getPackageVersion_1;
const { emitDiagnostics } = diagnostics.diagnostics;
const debug = node.nodeExports("@apm-js-collab/tracing-hooks:module-patch");
class ModulePatch {
  constructor({ instrumentations = [] } = {}) {
    this.packages = new Set(instrumentations.map((i) => i.module.name));
    this.instrumentator = create(instrumentations);
    this.compile = Module.prototype._compile;
  }
  /**
   * Patches the Node.js module class method that is responsible for compiling code.
   * If a module is found that has an instrumentator, it will transform the code before compiling it
   * with tracing channel methods.
   */
  patch() {
    const self = this;
    Module.prototype._compile = function wrappedCompile(...args) {
      const [content, filename] = args;
      const resolvedModule = parse(filename);
      if (resolvedModule && self.packages.has(resolvedModule.name)) {
        debug("found resolved module, checking if there is a transformer %s", filename);
        const version = getPackageVersion(resolvedModule.basedir, resolvedModule.name);
        const transformer = self.instrumentator.getTransformer(resolvedModule.name, version, resolvedModule.path);
        if (transformer) {
          debug("transforming file %s", filename);
          try {
            const transformedCode = transformer.transform(content, "cjs");
            args[0] = transformedCode?.code;
            emitDiagnostics({ url: pathToFileURL(filename).href, moduleName: transformer.moduleName });
            if (process.env.TRACING_DUMP) {
              dump(args[0], filename);
            }
          } catch (error) {
            debug("Error transforming module %s: %o", filename, error);
            emitDiagnostics({ url: pathToFileURL(filename).href, moduleName: transformer.moduleName, error });
          } finally {
            transformer.free();
          }
        }
      }
      return self.compile.apply(this, args);
    };
  }
  /**
   * Restores the original Module.prototype._compile method
   * **Note**: This is intended to be used in testing only.
   */
  unpatch() {
    Module.prototype._compile = this.compile;
  }
}
function dump(code, filename) {
  const os = require$$7;
  const path = require$$1$1;
  const fs = require$$0;
  const base = process.env.TRACING_DUMP_DIR ?? os.tmpdir();
  const dirname = path.dirname(filename);
  const basename = path.basename(filename);
  const targetDir = path.join(base, dirname);
  const targetFile = path.join(targetDir, basename);
  debug("Dumping patched code to: %s", targetFile);
  fs.mkdirSync(targetDir, { recursive: true });
  fs.writeFileSync(targetFile, code);
}
var tracingHooks = ModulePatch;

const ModulePatch$1 = /*@__PURE__*/_commonjsHelpers.getDefaultExportFromCjs(tracingHooks);

exports.default = ModulePatch$1;
//# sourceMappingURL=index.js.map
