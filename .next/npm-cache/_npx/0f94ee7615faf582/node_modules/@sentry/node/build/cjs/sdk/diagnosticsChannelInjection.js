Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

let loader;
let cached;
function setDiagnosticsChannelInjectionLoader(load) {
  loader = load;
}
function isDiagnosticsChannelInjectionEnabled() {
  return !!loader;
}
function resolveDiagnosticsChannelInjection() {
  if (!loader) {
    return void 0;
  }
  return cached ?? (cached = loader());
}

exports.isDiagnosticsChannelInjectionEnabled = isDiagnosticsChannelInjectionEnabled;
exports.resolveDiagnosticsChannelInjection = resolveDiagnosticsChannelInjection;
exports.setDiagnosticsChannelInjectionLoader = setDiagnosticsChannelInjectionLoader;
//# sourceMappingURL=diagnosticsChannelInjection.js.map
