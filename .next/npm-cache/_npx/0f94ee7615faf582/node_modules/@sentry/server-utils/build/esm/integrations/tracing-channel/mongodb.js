import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding } from '@sentry/core';
import { startMongoSpan, getV4SpanAttributes, getV3SpanAttributes, getV3CommandOperation } from '../../mongodb/mongodb-span.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "Mongo";
const ORIGIN = "auto.db.orchestrion.mongo";
const V3_DEDICATED_COMMANDS = /* @__PURE__ */ new Set(["insert", "update", "delete", "find", "getMore", "killCursors"]);
const _mongodbChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeV4Command();
        subscribeV4Checkout();
        subscribeV3Wireprotocol();
      });
    }
  };
});
function subscribeV4Command() {
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(CHANNELS.MONGODB_COMMAND),
    (data) => {
      const args = data.arguments ?? [];
      const ns = args[0];
      const cmd = args[1];
      if (!ns || !cmd || typeof cmd !== "object" || cmd.ismaster || cmd.hello) {
        return void 0;
      }
      const operation = Object.keys(cmd)[0];
      return startMongoSpan(getV4SpanAttributes(data.self, ns, cmd, operation, ORIGIN));
    },
    // Matches otel's `shouldSkipInstrumentation`: only trace when there is
    // an active parent span, to avoid emitting orphaned mongodb spans.
    { requiresParentSpan: true }
  );
}
function subscribeV4Checkout() {
  bindTracingChannelToSpan(diagnosticsChannel.tracingChannel(CHANNELS.MONGODB_CHECKOUT), () => void 0);
}
function subscribeV3Wireprotocol() {
  for (const operation of ["insert", "update", "remove"]) {
    const channel = operation === "insert" ? CHANNELS.MONGODB_V3_INSERT : operation === "update" ? CHANNELS.MONGODB_V3_UPDATE : CHANNELS.MONGODB_V3_REMOVE;
    bindV3(channel, (args) => ({
      topology: args[0],
      ns: args[1],
      command: args[2]?.[0],
      operation
    }));
  }
  bindV3(CHANNELS.MONGODB_V3_COMMAND, (args) => {
    const command = args[2];
    const type = command ? Object.keys(command)[0] : void 0;
    if (type && V3_DEDICATED_COMMANDS.has(type)) {
      return void 0;
    }
    return {
      topology: args[0],
      ns: args[1],
      command,
      operation: command ? getV3CommandOperation(command) : void 0
    };
  });
  bindV3(CHANNELS.MONGODB_V3_QUERY, (args) => ({
    topology: args[0],
    ns: args[1],
    command: args[2],
    operation: "find"
  }));
  bindV3(CHANNELS.MONGODB_V3_GET_MORE, (args) => ({
    topology: args[0],
    ns: args[1],
    command: args[2]?.cmd,
    operation: "getMore"
  }));
}
function bindV3(channelName, extract) {
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(channelName),
    (data) => {
      const args = data.arguments;
      if (!args) {
        return void 0;
      }
      const info = extract(args);
      if (!info || typeof info.ns !== "string") {
        return void 0;
      }
      return startMongoSpan(getV3SpanAttributes(info.ns, info.topology, info.command, info.operation, ORIGIN));
    },
    { requiresParentSpan: true }
  );
}
const mongodbChannelIntegration = defineIntegration(_mongodbChannelIntegration);

export { mongodbChannelIntegration };
//# sourceMappingURL=mongodb.js.map
