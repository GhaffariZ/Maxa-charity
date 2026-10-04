Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');

function isOrchestrionInjected() {
  return !!core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__;
}
function detectOrchestrionSetup() {
  const { runtime, bundler } = core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__ ?? {};
  if (!runtime && !bundler) {
    core.debug.warn(
      "[Sentry] No diagnostics-channel injection detected. Channel-based integrations will not record spans. Make sure the diagnostics channels are injected via the runtime `--import` hook or a bundler plugin before the instrumented modules load."
    );
    return;
  }
  core.debug.log(
    runtime ? `[Sentry] Runtime hook registered, injected libraries=${JSON.stringify(runtime)}` : "[Sentry] Runtime hook not registered"
  );
  core.debug.log(
    bundler ? `[Sentry] Bundler plugin ran, injected libraries=${JSON.stringify(bundler)}` : "[Sentry] Bundler plugin did not run"
  );
}

exports.detectOrchestrionSetup = detectOrchestrionSetup;
exports.isOrchestrionInjected = isOrchestrionInjected;
//# sourceMappingURL=detect.js.map
