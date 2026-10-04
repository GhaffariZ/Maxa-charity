import { GLOBAL_OBJ, debug } from '@sentry/core';

function isOrchestrionInjected() {
  return !!GLOBAL_OBJ.__SENTRY_ORCHESTRION__;
}
function detectOrchestrionSetup() {
  const { runtime, bundler } = GLOBAL_OBJ.__SENTRY_ORCHESTRION__ ?? {};
  if (!runtime && !bundler) {
    debug.warn(
      "[Sentry] No diagnostics-channel injection detected. Channel-based integrations will not record spans. Make sure the diagnostics channels are injected via the runtime `--import` hook or a bundler plugin before the instrumented modules load."
    );
    return;
  }
  debug.log(
    runtime ? `[Sentry] Runtime hook registered, injected libraries=${JSON.stringify(runtime)}` : "[Sentry] Runtime hook not registered"
  );
  debug.log(
    bundler ? `[Sentry] Bundler plugin ran, injected libraries=${JSON.stringify(bundler)}` : "[Sentry] Bundler plugin did not run"
  );
}

export { detectOrchestrionSetup, isOrchestrionInjected };
//# sourceMappingURL=detect.js.map
