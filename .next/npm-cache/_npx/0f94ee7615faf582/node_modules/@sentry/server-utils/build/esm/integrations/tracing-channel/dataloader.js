import * as diagnosticsChannel from 'node:diagnostics_channel';
import { CACHE_KEY } from '@sentry/conventions/attributes';
import { defineIntegration, debug, waitForTracingChannelBinding, startSpan, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

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
    kind: operation === "batch" ? void 0 : SPAN_KIND.CLIENT,
    op: isCacheGet ? CACHE_GET_OP : void 0,
    onlyIfParent: true,
    attributes: {
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
      [CACHE_KEY]: isCacheGet ? getCacheKey(keyArg) : void 0
    }
  };
}
const _dataloaderChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log("[orchestrion:dataloader] subscribing to dataloader tracing channels");
      waitForTracingChannelBinding(() => {
        subscribeConstruct();
        subscribeLoad();
        subscribeSimpleOperation(CHANNELS.DATALOADER_LOAD_MANY, "loadMany");
        subscribeSimpleOperation(CHANNELS.DATALOADER_PRIME, "prime");
        subscribeSimpleOperation(CHANNELS.DATALOADER_CLEAR, "clear");
        subscribeSimpleOperation(CHANNELS.DATALOADER_CLEAR_ALL, "clearAll");
      });
    }
  };
});
function subscribeConstruct() {
  diagnosticsChannel.tracingChannel(CHANNELS.DATALOADER_CONSTRUCT).start.subscribe((message) => {
    const data = message;
    const batchLoadFn = data.arguments[0];
    if (typeof batchLoadFn !== "function" || batchLoadFn[WRAPPED]) {
      return;
    }
    const original = batchLoadFn;
    const wrapped = function(...args) {
      return startSpan(
        { ...makeSpanOptions(this, "batch", args[0]), links: this._batch?.spanLinks },
        () => original.apply(this, args)
      );
    };
    wrapped[WRAPPED] = true;
    data.arguments[0] = wrapped;
  });
}
function subscribeLoad() {
  const channel = diagnosticsChannel.tracingChannel(CHANNELS.DATALOADER_LOAD);
  bindTracingChannelToSpan(channel, (data) => startInactiveSpanFor(data.self, "load", data.arguments[0]), {
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
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(channelName),
    (data) => startInactiveSpanFor(data.self, operation, data.arguments[0]),
    { requiresParentSpan: true }
  );
}
function startInactiveSpanFor(loader, operation, keyArg) {
  return startInactiveSpan(makeSpanOptions(loader, operation, keyArg));
}
const dataloaderChannelIntegration = defineIntegration(_dataloaderChannelIntegration);

export { dataloaderChannelIntegration };
//# sourceMappingURL=dataloader.js.map
