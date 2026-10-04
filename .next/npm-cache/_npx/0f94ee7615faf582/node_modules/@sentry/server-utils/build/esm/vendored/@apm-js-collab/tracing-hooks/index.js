import { getDefaultExportFromCjs } from '../../../_virtual/_commonjsHelpers.js';
import { c as codeTransformer } from '../code-transformer/index.js';
import require$$1__default from 'node:module';
import { m as moduleDetailsFromPath } from '../../module-details-from-path/index.js';
import require$$3 from 'node:url';
import { g as getPackageVersion_1 } from './lib/get-package-version.js';
import { d as diagnostics } from './lib/diagnostics.js';
import { n as nodeExports } from '../../debug/src/node.js';
import require$$7 from 'node:os';
import require$$1 from 'node:path';
import require$$0 from 'node:fs';

const { create } = codeTransformer;
const Module = require$$1__default;
const parse = moduleDetailsFromPath;
const { pathToFileURL } = require$$3;
const getPackageVersion = getPackageVersion_1;
const { emitDiagnostics } = diagnostics;
const debug = nodeExports("@apm-js-collab/tracing-hooks:module-patch");
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
  const path = require$$1;
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

const ModulePatch$1 = /*@__PURE__*/getDefaultExportFromCjs(tracingHooks);

export { ModulePatch$1 as default };
//# sourceMappingURL=index.js.map
