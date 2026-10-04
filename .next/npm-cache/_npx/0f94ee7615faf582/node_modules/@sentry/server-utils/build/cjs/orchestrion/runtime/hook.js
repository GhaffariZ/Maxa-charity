Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const hook = require('../../vendored/@apm-js-collab/tracing-hooks/hook.js');
const diagnostics = require('../../vendored/@apm-js-collab/tracing-hooks/lib/diagnostics.js');



exports.createDiagnosticsPort = hook.createDiagnosticsPort;
exports.initialize = hook.initialize;
exports.initializeSync = hook.initializeSync;
exports.load = hook.load;
exports.loadResult = hook.loadResult;
exports.loadSync = hook.loadSync;
exports.resolve = hook.resolve;
exports.resolveSync = hook.resolveSync;
exports.setDiagnosticsHook = diagnostics.diagnostics.setDiagnosticsHook;
//# sourceMappingURL=hook.js.map
