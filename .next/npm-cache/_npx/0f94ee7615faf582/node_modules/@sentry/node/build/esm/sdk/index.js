import { applySdkMetadata, hasSpansEnabled } from '@sentry/core';
import { init as init$1, validateOpenTelemetrySetup, getDefaultIntegrations as getDefaultIntegrations$1 } from '@sentry/node-core';
import { httpIntegration } from '../integrations/http.js';
import { nativeNodeFetchIntegration } from '../integrations/node-fetch.js';
import { getAutoPerformanceIntegrations } from '../integrations/tracing/index.js';
import { isDiagnosticsChannelInjectionEnabled, resolveDiagnosticsChannelInjection } from './diagnosticsChannelInjection.js';
import { initOpenTelemetry } from './initOtel.js';

function getDefaultIntegrationsWithoutPerformance() {
  const nodeCoreIntegrations = getDefaultIntegrations$1();
  return nodeCoreIntegrations.filter((integration) => integration.name !== "Http" && integration.name !== "NodeFetch").concat(httpIntegration(), nativeNodeFetchIntegration());
}
function getDefaultIntegrations(options) {
  return [
    ...getDefaultIntegrationsWithoutPerformance(),
    // We only add performance integrations if tracing is enabled
    // Note that this means that without tracing enabled, e.g. `expressIntegration()` will not be added
    // This means that generally request isolation will work (because that is done by httpIntegration)
    // But `transactionName` will not be set automatically
    ...hasSpansEnabled(options) ? getAutoPerformanceIntegrations() : []
  ];
}
function applyDiagnosticsChannelInjectionIntegrations(integrations, options) {
  if (isDiagnosticsChannelInjectionEnabled() && hasSpansEnabled(options)) {
    const diagnosticsChannelInjection = resolveDiagnosticsChannelInjection();
    if (diagnosticsChannelInjection) {
      const replaced = new Set(diagnosticsChannelInjection.replacedOtelIntegrationNames);
      return [...integrations.filter((i) => !replaced.has(i.name)), ...diagnosticsChannelInjection.integrations];
    }
  }
  return integrations;
}
function init(options = {}) {
  return _init(options, getDefaultIntegrations);
}
function _init(options = {}, getDefaultIntegrationsImpl) {
  applySdkMetadata(options, "node");
  const diagnosticsChannelInjection = isDiagnosticsChannelInjectionEnabled() && hasSpansEnabled(options) ? resolveDiagnosticsChannelInjection() : void 0;
  if (diagnosticsChannelInjection) {
    diagnosticsChannelInjection.register();
  }
  let defaultIntegrations = options.defaultIntegrations ?? getDefaultIntegrationsImpl(options);
  if (diagnosticsChannelInjection && Array.isArray(defaultIntegrations) && defaultIntegrations.length > 0) {
    const replaced = new Set(diagnosticsChannelInjection.replacedOtelIntegrationNames);
    defaultIntegrations = [
      ...defaultIntegrations.filter((integration) => !replaced.has(integration.name)),
      ...diagnosticsChannelInjection.integrations
    ];
  }
  const client = init$1({
    ...options,
    defaultIntegrations
  });
  if (client && !options.skipOpenTelemetrySetup) {
    initOpenTelemetry(client, {
      spanProcessors: options.openTelemetrySpanProcessors
    });
    validateOpenTelemetrySetup();
  }
  if (diagnosticsChannelInjection) {
    diagnosticsChannelInjection.detect();
  }
  return client;
}
function initWithoutDefaultIntegrations(options = {}) {
  return _init(options, () => []);
}

export { applyDiagnosticsChannelInjectionIntegrations, getDefaultIntegrations, getDefaultIntegrationsWithoutPerformance, init, initWithoutDefaultIntegrations };
//# sourceMappingURL=index.js.map
