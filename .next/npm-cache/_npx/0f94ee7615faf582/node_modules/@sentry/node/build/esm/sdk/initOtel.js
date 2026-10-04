import { propagation, context, trace } from '@opentelemetry/api';
import { BasicTracerProvider } from '@opentelemetry/sdk-trace-base';
import { debug, hasSpanStreamingEnabled } from '@sentry/core';
import { setupOpenTelemetryLogger, SentryContextManager, initializeEsmLoader } from '@sentry/node-core';
import { SentrySpanProcessor, SentrySampler, getSentryResource, SentryPropagator, SentryTracerProvider, setIsSetup, applyOtelSpanData, backfillStreamedSpanDataFromOtel } from '@sentry/opentelemetry';
import { DEBUG_BUILD } from '../debug-build.js';
import { getOpenTelemetryInstrumentationToPreload } from '../integrations/tracing/index.js';

const MAX_MAX_SPAN_WAIT_DURATION = 1e6;
const OTEL_API_GLOBAL_KEY = /* @__PURE__ */ Symbol.for("opentelemetry.js.api.1");
function registerGlobalTracerProvider(provider) {
  if (trace.setGlobalTracerProvider(provider)) {
    return true;
  }
  const otelGlobal = globalThis;
  const registry = otelGlobal[OTEL_API_GLOBAL_KEY];
  if (registry && !registry.trace) {
    DEBUG_BUILD && debug.warn(
      "Replaced a pre-existing OpenTelemetry API registry that was created by a different @opentelemetry/api version and would have blocked tracing. If you want to manage OpenTelemetry yourself, set `skipOpenTelemetrySetup: true` in `Sentry.init()`."
    );
    otelGlobal[OTEL_API_GLOBAL_KEY] = void 0;
    if (!trace.setGlobalTracerProvider(provider)) {
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
    setupOpenTelemetryLogger();
  }
  const [provider, asyncLocalStorageLookup] = setupOtel(client, options);
  client.traceProvider = provider;
  client.asyncLocalStorageLookup = asyncLocalStorageLookup;
}
function preloadOpenTelemetry(options = {}) {
  const { debug: debug$1 } = options;
  if (debug$1) {
    debug.enable();
  }
  initializeEsmLoader();
  getPreloadMethods(options.integrations).forEach((fn) => {
    fn();
    if (debug$1) {
      debug.log(`[Sentry] Preloaded ${fn.id} instrumentation`);
    }
  });
}
function getPreloadMethods(integrationNames) {
  const instruments = getOpenTelemetryInstrumentationToPreload();
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
  const provider = new BasicTracerProvider({
    sampler: new SentrySampler(client),
    resource: getSentryResource("node"),
    forceFlushTimeoutMillis: 500,
    spanProcessors: [
      new SentrySpanProcessor({
        timeout: _clampSpanProcessorTimeout(client.getOptions().maxSpanWaitDuration),
        client
      }),
      ...options.spanProcessors || []
    ]
  });
  registerGlobalTracerProvider(provider);
  propagation.setGlobalPropagator(new SentryPropagator());
  const ctxManager = new SentryContextManager();
  context.setGlobalContextManager(ctxManager);
  return [provider, ctxManager.getAsyncLocalStorageLookup()];
}
function setupSentryTracerProvider(client) {
  const provider = new SentryTracerProvider({ resource: getSentryResource("node") });
  if (!registerGlobalTracerProvider(provider)) {
    DEBUG_BUILD && debug.warn(
      "Could not register SentryTracerProvider because another OpenTelemetry tracer provider is already registered."
    );
    return [void 0, void 0];
  }
  setIsSetup("SentryTracerProvider");
  propagation.setGlobalPropagator(new SentryPropagator());
  const ctxManager = new SentryContextManager();
  context.setGlobalContextManager(ctxManager);
  client.on("spanEnd", (span) => {
    applyOtelSpanData(span, { finalizeStatus: true });
  });
  if (hasSpanStreamingEnabled(client)) {
    client.on("preprocessSpan", backfillStreamedSpanDataFromOtel);
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
    DEBUG_BUILD && debug.warn(`\`maxSpanWaitDuration\` is too high, using the maximum value of ${MAX_MAX_SPAN_WAIT_DURATION}`);
    return MAX_MAX_SPAN_WAIT_DURATION;
  } else if (maxSpanWaitDuration <= 0 || Number.isNaN(maxSpanWaitDuration)) {
    DEBUG_BUILD && debug.warn("`maxSpanWaitDuration` must be a positive number, using default value instead.");
    return void 0;
  }
  return maxSpanWaitDuration;
}

export { _clampSpanProcessorTimeout, initOpenTelemetry, preloadOpenTelemetry, setupOtel };
//# sourceMappingURL=initOtel.js.map
