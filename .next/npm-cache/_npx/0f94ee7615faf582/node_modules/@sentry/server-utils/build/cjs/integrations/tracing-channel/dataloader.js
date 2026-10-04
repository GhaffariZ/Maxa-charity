Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const debugBuild = require('../../debug-build.js');
const channels = require('../../orchestrion/channels.js');
const tracingChannel = require('../../tracing-channel.js');

function _interopNamespaceDefault(e) {
  const n = Object.create(null, { [Symbol.toStringTag]: { value: 'Module' } });
  if (e) {
    for (const k in e) {
      n[k] = e[k];
    }
  }
  n.default = e;
  return n;
}

const diagnosticsChannel__namespace = /*#__PURE__*/_interopNamespaceDefault(diagnosticsChannel);

const INTEGRATION_NAME = "Dataloader";
const MODULE_NAME = "dataloader";
const ORIGIN = "auto.db.orchestrion.dataloader";
const CACHE_GET_OP = "cache.get";
const WRAPPED = /* @__PURE__ */ Symbol("sentry.dataloader.wrapped");
function getSpanName(loader, operation) {
  const name = loader?.name;
  return name ? `${MODULE_NAME}.${operation} ${name}` : `${MODULE_NAME}.${operation}`;
}
function getCacheKey(keyArg) {
  if (Array.isArray(keyArg)) {
    return keyArg.map((key) => String(key));
  }
  return keyArg == null ? void 0 : [String(keyArg)];
}
function makeSpanOptions(loader, operation, keyArg) {
  const isCacheGet = operation === "load" || operation === "loadMany" || operation === "batch";
  return {
    name: getSpanName(loader, operation),
    // Every direct operation (`load`/`loadMany`/`prime`/`clear`/`clearAll`) is a client call, matching
    // the vendored OTel instrumentation. The `batch` runs off a deferred tick with no obvious network
    // peer, so it gets no kind.
    kind: operation === "batch" ? void 0 : core.SPAN_KIND.CLIENT,
    op: isCacheGet ? CACHE_GET_OP : void 0,
    onlyIfParent: true,
    attributes: {
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
      [attributes.CACHE_KEY]: isCacheGet ? getCacheKey(keyArg) : void 0
    }
  };
}
const _dataloaderChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log("[orchestrion:dataloader] subscribing to dataloader tracing channels");
      core.waitForTracingChannelBinding(() => {
        subscribeConstruct();
        subscribeLoad();
        subscribeSimpleOperation(channels.CHANNELS.DATALOADER_LOAD_MANY, "loadMany");
        subscribeSimpleOperation(channels.CHANNELS.DATALOADER_PRIME, "prime");
        subscribeSimpleOperation(channels.CHANNELS.DATALOADER_CLEAR, "clear");
        subscribeSimpleOperation(channels.CHANNELS.DATALOADER_CLEAR_ALL, "clearAll");
      });
    }
  };
});
function subscribeConstruct() {
  diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.DATALOADER_CONSTRUCT).start.subscribe((message) => {
    const data = message;
    const batchLoadFn = data.arguments[0];
    if (typeof batchLoadFn !== "function" || batchLoadFn[WRAPPED]) {
      return;
    }
    const original = batchLoadFn;
    const wrapped = function(...args) {
      return core.startSpan(
        { ...makeSpanOptions(this, "batch", args[0]), links: this._batch?.spanLinks },
        () => original.apply(this, args)
      );
    };
    wrapped[WRAPPED] = true;
    data.arguments[0] = wrapped;
  });
}
function subscribeLoad() {
  const channel = diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.DATALOADER_LOAD);
  tracingChannel.bindTracingChannelToSpan(channel, (data) => startInactiveSpanFor(data.self, "load", data.arguments[0]), {
    requiresParentSpan: true
  });
  channel.end.subscribe((message) => {
    const data = message;
    const span = data._sentrySpan;
    const batch = data.self?._batch;
    if (span && batch && span.isRecording()) {
      (batch.spanLinks ?? (batch.spanLinks = [])).push({ context: span.spanContext() });
    }
  });
}
function subscribeSimpleOperation(channelName, operation) {
  tracingChannel.bindTracingChannelToSpan(
    diagnosticsChannel__namespace.tracingChannel(channelName),
    (data) => startInactiveSpanFor(data.self, operation, data.arguments[0]),
    { requiresParentSpan: true }
  );
}
function startInactiveSpanFor(loader, operation, keyArg) {
  return core.startInactiveSpan(makeSpanOptions(loader, operation, keyArg));
}
const dataloaderChannelIntegration = core.defineIntegration(_dataloaderChannelIntegration);

exports.dataloaderChannelIntegration = dataloaderChannelIntegration;
//# sourceMappingURL=dataloader.js.map
