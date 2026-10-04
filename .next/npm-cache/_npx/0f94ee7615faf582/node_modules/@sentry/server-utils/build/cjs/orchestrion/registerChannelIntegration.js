Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');

function registerOrchestrionChannelIntegration(name, integrationFn) {
  var _a;
  const marker = (_a = core.GLOBAL_OBJ).__SENTRY_ORCHESTRION__ ?? (_a.__SENTRY_ORCHESTRION__ = {});
  (marker.integrations ?? (marker.integrations = /* @__PURE__ */ new Map())).set(name, integrationFn);
  core.getClient()?.addIntegration(integrationFn());
}

exports.registerOrchestrionChannelIntegration = registerOrchestrionChannelIntegration;
//# sourceMappingURL=registerChannelIntegration.js.map
