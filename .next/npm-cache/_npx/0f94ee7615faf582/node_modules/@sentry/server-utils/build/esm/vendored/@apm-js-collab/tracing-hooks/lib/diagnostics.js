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

export { diagnostics as d };
//# sourceMappingURL=diagnostics.js.map
