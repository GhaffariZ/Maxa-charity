Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const node = require('../../debug/src/node.js');
const index = require('../code-transformer/index.js');
const index$1 = require('../../module-details-from-path/index.js');
const require$$3 = require('node:url');
const node_worker_threads = require('node:worker_threads');
const getPackageVersion = require('./lib/get-package-version.js');
const diagnostics = require('./lib/diagnostics.js');
const require$$0 = require('node:fs');

const debug = node.default('@apm-js-collab/tracing-hooks:esm-hook');
let transformers = null;
let packages = null;
let instrumentator = null;

// On the main thread diagnostics go straight to the hook set via
// `setDiagnosticsHook`. When these hooks run on the `Module.register` loader
// thread, `initialize` swaps this for a function that posts back over the
// MessagePort supplied in `data.diagnosticsPort`.
let emit = diagnostics.diagnostics.emitDiagnostics;

/**
 * Creates a MessagePort that forwards diagnostics posted by the `Module.register`
 * loader thread to the hook set via `setDiagnosticsHook` on this thread. Pass the
 * returned port to `Module.register` in both `data.diagnosticsPort` and
 * `transferList`.
 */
function createDiagnosticsPort() {
  const { port1, port2 } = new node_worker_threads.MessageChannel();
  port1.on('message', diagnostics.diagnostics.emitDiagnostics);
  // The diagnostics channel must not keep the process alive.
  port1.unref();
  return port2
}

async function initialize(data = {}) {
  return initializeSync(data)
}
function initializeSync(data = {}) {
  const instrumentations = data?.instrumentations || [];
  instrumentator = index.codeTransformer.create(instrumentations);
  packages = new Set(instrumentations.map(i => i.module.name));
  transformers = new Map();
  emit = data?.diagnosticsPort ? createPortEmitter(data.diagnosticsPort) : diagnostics.diagnostics.emitDiagnostics;
}

function createPortEmitter(port) {
  return (diag) => {
    try {
      // Structured clone reliably carries Error instances but not arbitrary thrown
      // values, so flatten anything else to an Error rather than let postMessage
      // throw inside the load path.
      const error = diag.error === undefined || diag.error instanceof Error
        ? diag.error
        : new Error(String(diag.error));
      port.postMessage({ ...diag, error });
    } catch (err) {
      debug('failed to post diagnostics for %s: %o', diag.url, err);
    }
  }
}

async function resolve(specifier, context, nextResolve) {
  return resolveFromURL(await nextResolve(specifier, context))
}
function resolveFromURL(url) {
  const resolvedModule = index$1.default(url.url);
  if (resolvedModule && packages.has(resolvedModule.name)) {
    const path = require$$3.fileURLToPath(resolvedModule.basedir);
    const version = getPackageVersion.default(path);
    const transformer = instrumentator.getTransformer(resolvedModule.name, version, resolvedModule.path);
    if (transformer) {
      transformers.set(url.url, transformer);
    }
  }
  return url
}
function resolveSync(specifier, context, nextResolve) {
  return resolveFromURL(nextResolve(specifier, context))
}

async function load(url, context, nextLoad) {
  const result = await nextLoad(url, context);

  if (transformers.has(url) === false) {
    return result
  }

  if (result.format === 'commonjs') {
    // CommonJS is always left to the `Module.prototype._compile` patch
    // (`ModulePatch`), which these hooks are only ever registered alongside.
    // Returning `source` for a CommonJS module instead makes Node evaluate it on the
    // synchronous require(esm) bridge, which throws ERR_VM_MODULE_LINK_FAILURE on
    // Node < 24.12 when the module's top-level require() chain reaches an ES module
    // (https://github.com/nodejs/node/issues/59666). Handing the module back exactly
    // as Node produced it (`source` is null) sends it down the ordinary CommonJS
    // loader, where `_compile` transforms it.
    //
    // `resolve` has already put a transformer in the map for this URL and nothing
    // downstream will free it, so do that here.
    debug('deferring commonjs module to the _compile patch %s', url);
    const transformer = transformers.get(url);
    transformer.free();
    transformers.delete(url);
    return result
  }

  return loadResult(url, result)
}

// Unlike the async `load` hook above, this one must transform CommonJS: the sync hooks
// are never paired with a `_compile` patch, so they are the only thing that can, and
// they don't evaluate CommonJS on the require(esm) bridge.
function loadSync(url, context, nextLoad) {
  const result = nextLoad(url, context);

  if (transformers.has(url) === false) {
    return result
  }

  if (result.format === 'commonjs') {
    const parsedUrl = new URL(result.responseURL ?? url);
    result.source ??= require$$0.readFileSync(parsedUrl);
  }

  return loadResult(url, result)
}

function loadResult(url, result) {
  const code = result.source;
  if (code) {
    const transformer = transformers.get(url);
    try {
      const moduleType = result.format === 'module' ? 'esm' :
        result.format === 'commonjs' ? 'cjs' : 'unknown';
      // Node's synchronous hooks (`Module.registerHooks`) deliver `source` as a plain `Uint8Array`,
      // whereas the async loader delivers a `Buffer`. `Uint8Array.prototype.toString('utf8')` ignores
      // the encoding and returns comma-joined byte values instead of the decoded text, so decode via
      // `Buffer` for anything that isn't already a string.
      const source = typeof code === 'string' ? code : Buffer.from(code).toString('utf8');
      const transformedCode = transformer.transform(source, moduleType);
      result.source = transformedCode?.code;
      result.shortCircuit = true;
      emit({ url, moduleName: transformer.moduleName });
    } catch (err) {
      debug('Error transforming module %s: %o', url, err);
      emit({ url, moduleName: transformer.moduleName, error: err });
    } finally {
      transformer.free();
    }
  }

  return result
}

exports.createDiagnosticsPort = createDiagnosticsPort;
exports.initialize = initialize;
exports.initializeSync = initializeSync;
exports.load = load;
exports.loadResult = loadResult;
exports.loadSync = loadSync;
exports.resolve = resolve;
exports.resolveSync = resolveSync;
exports.setDiagnosticsHook = diagnostics.diagnostics.setDiagnosticsHook;
//# sourceMappingURL=hook.js.map
