Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

let diagnosticsHook;
function setDiagnosticsHook(hook) {
  diagnosticsHook = hook;
}
function emitDiagnostics(diag) {
  if (diagnosticsHook) {
    diagnosticsHook(diag);
  }
}
var diagnostics = { setDiagnosticsHook, emitDiagnostics };

exports.diagnostics = diagnostics;
//# sourceMappingURL=diagnostics.js.map
