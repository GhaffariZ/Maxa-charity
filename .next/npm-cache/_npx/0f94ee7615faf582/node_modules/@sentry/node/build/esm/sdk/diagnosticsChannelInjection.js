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

export { isDiagnosticsChannelInjectionEnabled, resolveDiagnosticsChannelInjection, setDiagnosticsChannelInjectionLoader };
//# sourceMappingURL=diagnosticsChannelInjection.js.map
