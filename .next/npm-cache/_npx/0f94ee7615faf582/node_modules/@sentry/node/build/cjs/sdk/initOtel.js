Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const api = require('@opentelemetry/api');
const sdkTraceBase = require('@opentelemetry/sdk-trace-base');
const core = require('@sentry/core');
const nodeCore = require('@sentry/node-core');
const opentelemetry = require('@sentry/opentelemetry');
const debugBuild = require('../debug-build.js');
const index = require('../integrations/tracing/index.js');

const MAX_MAX_SPAN_WAIT_DURATION = 1e6;
const OTEL_API_GLOBAL_KEY = /* @__PURE__ */ Symbol.for("opentelemetry.js.api.1");
function registerGlobalTracerProvider(provider) {
  if (api.trace.setGlobalTracerProvider(provider)) {
    return true;
  }
  const otelGlobal = globalThis;
  const registry = otelGlobal[OTEL_API_GLOBAL_KEY];
  if (registry && !registry.trace) {
    debugBuild.DEBUG_BUILD && core.debug.warn(
      "Replaced a pre-existing OpenTelemetry API registry that was created by a different @opentelemetry/api version and would have blocked tracing. If you want to manage OpenTelemetry yourself, set `skipOpenTelemetrySetup: true` in `Sentry.init()`."
    );
    otelGlobal[OTEL_API_GLOBAL_KEY] = void 0;
    if (!api.trace.setGlobalTracerProvider(provider)) {
      return false;
    }
    const recreatedRegistry = otelGlobal[OTEL_API_GLOBAL_KEY];
    if (recreatedRegistry) {
      const { propagation: _propagation, context: _context, ...carriedOverSlots } = registry;
      otelGlobal[OTEL_API_GLOBAL_KEY] = { ...carriedOverSlots, ...recreatedRegistry };
    }
    return true;
  }
  return false;
}
function initOpenTelemetry(client, options = {}) {
  if (client.getOptions().debug) {
    nodeCore.setupOpenTelemetryLogger();
  }
  const [provider, asyncLocalStorageLookup] = setupOtel(client, options);
  client.traceProvider = provider;
  client.asyncLocalStorageLookup = asyncLocalStorageLookup;
}
function preloadOpenTelemetry(options = {}) {
  const { debug } = options;
  if (debug) {
    core.debug.enable();
  }
  nodeCore.initializeEsmLoader();
  getPreloadMethods(options.integrations).forEach((fn) => {
    fn();
    if (debug) {
      core.debug.log(`[Sentry] Preloaded ${fn.id} instrumentation`);
    }
  });
}
function getPreloadMethods(integrationNames) {
  const instruments = index.getOpenTelemetryInstrumentationToPreload();
  if (!integrationNames) {
    return instruments;
  }
  return instruments.filter((instrumentation) => {
    const id = instrumentation.id;
    return integrationNames.some((integrationName) => id === integrationName || id.startsWith(`${integrationName}.`));
  });
}
function setupOtel(client, options = {}) {
  const shouldUseBasicTracerProvider = client.getOptions().openTelemetryBasicTracerProvider || !!options.spanProcessors?.length;
  if (!shouldUseBasicTracerProvider) {
    return setupSentryTracerProvider(client);
  }
  const provider = new sdkTraceBase.BasicTracerProvider({
    sampler: new opentelemetry.SentrySampler(client),
    resource: opentelemetry.getSentryResource("node"),
    forceFlushTimeoutMillis: 500,
    spanProcessors: [
      new opentelemetry.SentrySpanProcessor({
        timeout: _clampSpanProcessorTimeout(client.getOptions().maxSpanWaitDuration),
        client
      }),
      ...options.spanProcessors || []
    ]
  });
  registerGlobalTracerProvider(provider);
  api.propagation.setGlobalPropagator(new opentelemetry.SentryPropagator());
  const ctxManager = new nodeCore.SentryContextManager();
  api.context.setGlobalContextManager(ctxManager);
  return [provider, ctxManager.getAsyncLocalStorageLookup()];
}
function setupSentryTracerProvider(client) {
  const provider = new opentelemetry.SentryTracerProvider({ resource: opentelemetry.getSentryResource("node") });
  if (!registerGlobalTracerProvider(provider)) {
    debugBuild.DEBUG_BUILD && core.debug.warn(
      "Could not register SentryTracerProvider because another OpenTelemetry tracer provider is already registered."
    );
    return [void 0, void 0];
  }
  opentelemetry.setIsSetup("SentryTracerProvider");
  api.propagation.setGlobalPropagator(new opentelemetry.SentryPropagator());
  const ctxManager = new nodeCore.SentryContextManager();
  api.context.setGlobalContextManager(ctxManager);
  client.on("spanEnd", (span) => {
    opentelemetry.applyOtelSpanData(span, { finalizeStatus: true });
  });
  if (core.hasSpanStreamingEnabled(client)) {
    client.on("preprocessSpan", opentelemetry.backfillStreamedSpanDataFromOtel);
  }
  client.on("preprocessEvent", (event) => {
    if (event.type !== "transaction") {
      return;
    }
    event.contexts = {
      ...event.contexts,
      otel: {
        resource: provider.resource?.attributes,
        ...event.contexts?.otel
      }
    };
  });
  return [provider, ctxManager.getAsyncLocalStorageLookup()];
}
function _clampSpanProcessorTimeout(maxSpanWaitDuration) {
  if (maxSpanWaitDuration == null) {
    return void 0;
  }
  if (maxSpanWaitDuration > MAX_MAX_SPAN_WAIT_DURATION) {
    debugBuild.DEBUG_BUILD && core.debug.warn(`\`maxSpanWaitDuration\` is too high, using the maximum value of ${MAX_MAX_SPAN_WAIT_DURATION}`);
    return MAX_MAX_SPAN_WAIT_DURATION;
  } else if (maxSpanWaitDuration <= 0 || Number.isNaN(maxSpanWaitDuration)) {
    debugBuild.DEBUG_BUILD && core.debug.warn("`maxSpanWaitDuration` must be a positive number, using default value instead.");
    return void 0;
  }
  return maxSpanWaitDuration;
}

exports._clampSpanProcessorTimeout = _clampSpanProcessorTimeout;
exports.initOpenTelemetry = initOpenTelemetry;
exports.preloadOpenTelemetry = preloadOpenTelemetry;
exports.setupOtel = setupOtel;
//# sourceMappingURL=initOtel.js.map
