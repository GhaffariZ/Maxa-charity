import { GLOBAL_OBJ, getClient } from '@sentry/core';

function registerOrchestrionChannelIntegration(name, integrationFn) {
  var _a;
  const marker = (_a = GLOBAL_OBJ).__SENTRY_ORCHESTRION__ ?? (_a.__SENTRY_ORCHESTRION__ = {});
  (marker.integrations ?? (marker.integrations = /* @__PURE__ */ new Map())).set(name, integrationFn);
  getClient()?.addIntegration(integrationFn());
}

export { registerOrchestrionChannelIntegration };
//# sourceMappingURL=registerChannelIntegration.js.map
