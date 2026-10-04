Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const http = require('../integrations/http.js');
const nodeFetch = require('../integrations/node-fetch.js');
const index = require('../integrations/tracing/index.js');
const diagnosticsChannelInjection = require('./diagnosticsChannelInjection.js');
const initOtel = require('./initOtel.js');

function getDefaultIntegrationsWithoutPerformance() {
  const nodeCoreIntegrations = nodeCore.getDefaultIntegrations();
  return nodeCoreIntegrations.filter((integration) => integration.name !== "Http" && integration.name !== "NodeFetch").concat(http.httpIntegration(), nodeFetch.nativeNodeFetchIntegration());
}
function getDefaultIntegrations(options) {
  return [
    ...getDefaultIntegrationsWithoutPerformance(),
    // We only add performance integrations if tracing is enabled
    // Note that this means that without tracing enabled, e.g. `expressIntegration()` will not be added
    // This means that generally request isolation will work (because that is done by httpIntegration)
    // But `transactionName` will not be set automatically
    ...core.hasSpansEnabled(options) ? index.getAutoPerformanceIntegrations() : []
  ];
}
function applyDiagnosticsChannelInjectionIntegrations(integrations, options) {
  if (diagnosticsChannelInjection.isDiagnosticsChannelInjectionEnabled() && core.hasSpansEnabled(options)) {
    const diagnosticsChannelInjection$1 = diagnosticsChannelInjection.resolveDiagnosticsChannelInjection();
    if (diagnosticsChannelInjection$1) {
      const replaced = new Set(diagnosticsChannelInjection$1.replacedOtelIntegrationNames);
      return [...integrations.filter((i) => !replaced.has(i.name)), ...diagnosticsChannelInjection$1.integrations];
    }
  }
  return integrations;
}
function init(options = {}) {
  return _init(options, getDefaultIntegrations);
}
function _init(options = {}, getDefaultIntegrationsImpl) {
  core.applySdkMetadata(options, "node");
  const diagnosticsChannelInjection$1 = diagnosticsChannelInjection.isDiagnosticsChannelInjectionEnabled() && core.hasSpansEnabled(options) ? diagnosticsChannelInjection.resolveDiagnosticsChannelInjection() : void 0;
  if (diagnosticsChannelInjection$1) {
    diagnosticsChannelInjection$1.register();
  }
  let defaultIntegrations = options.defaultIntegrations ?? getDefaultIntegrationsImpl(options);
  if (diagnosticsChannelInjection$1 && Array.isArray(defaultIntegrations) && defaultIntegrations.length > 0) {
    const replaced = new Set(diagnosticsChannelInjection$1.replacedOtelIntegrationNames);
    defaultIntegrations = [
      ...defaultIntegrations.filter((integration) => !replaced.has(integration.name)),
      ...diagnosticsChannelInjection$1.integrations
    ];
  }
  const client = nodeCore.init({
    ...options,
    defaultIntegrations
  });
  if (client && !options.skipOpenTelemetrySetup) {
    initOtel.initOpenTelemetry(client, {
      spanProcessors: options.openTelemetrySpanProcessors
    });
    nodeCore.validateOpenTelemetrySetup();
  }
  if (diagnosticsChannelInjection$1) {
    diagnosticsChannelInjection$1.detect();
  }
  return client;
}
function initWithoutDefaultIntegrations(options = {}) {
  return _init(options, () => []);
}

exports.applyDiagnosticsChannelInjectionIntegrations = applyDiagnosticsChannelInjectionIntegrations;
exports.getDefaultIntegrations = getDefaultIntegrations;
exports.getDefaultIntegrationsWithoutPerformance = getDefaultIntegrationsWithoutPerformance;
exports.init = init;
exports.initWithoutDefaultIntegrations = initWithoutDefaultIntegrations;
//# sourceMappingURL=index.js.map
