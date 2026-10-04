Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const mongodbSpan = require('../../mongodb/mongodb-span.js');
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

const INTEGRATION_NAME = "Mongo";
const ORIGIN = "auto.db.orchestrion.mongo";
const V3_DEDICATED_COMMANDS = /* @__PURE__ */ new Set(["insert", "update", "delete", "find", "getMore", "killCursors"]);
const _mongodbChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        subscribeV4Command();
        subscribeV4Checkout();
        subscribeV3Wireprotocol();
      });
    }
  };
});
function subscribeV4Command() {
  tracingChannel.bindTracingChannelToSpan(
    diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.MONGODB_COMMAND),
    (data) => {
      const args = data.arguments ?? [];
      const ns = args[0];
      const cmd = args[1];
      if (!ns || !cmd || typeof cmd !== "object" || cmd.ismaster || cmd.hello) {
        return void 0;
      }
      const operation = Object.keys(cmd)[0];
      return mongodbSpan.startMongoSpan(mongodbSpan.getV4SpanAttributes(data.self, ns, cmd, operation, ORIGIN));
    },
    // Matches otel's `shouldSkipInstrumentation`: only trace when there is
    // an active parent span, to avoid emitting orphaned mongodb spans.
    { requiresParentSpan: true }
  );
}
function subscribeV4Checkout() {
  tracingChannel.bindTracingChannelToSpan(diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.MONGODB_CHECKOUT), () => void 0);
}
function subscribeV3Wireprotocol() {
  for (const operation of ["insert", "update", "remove"]) {
    const channel = operation === "insert" ? channels.CHANNELS.MONGODB_V3_INSERT : operation === "update" ? channels.CHANNELS.MONGODB_V3_UPDATE : channels.CHANNELS.MONGODB_V3_REMOVE;
    bindV3(channel, (args) => ({
      topology: args[0],
      ns: args[1],
      command: args[2]?.[0],
      operation
    }));
  }
  bindV3(channels.CHANNELS.MONGODB_V3_COMMAND, (args) => {
    const command = args[2];
    const type = command ? Object.keys(command)[0] : void 0;
    if (type && V3_DEDICATED_COMMANDS.has(type)) {
      return void 0;
    }
    return {
      topology: args[0],
      ns: args[1],
      command,
      operation: command ? mongodbSpan.getV3CommandOperation(command) : void 0
    };
  });
  bindV3(channels.CHANNELS.MONGODB_V3_QUERY, (args) => ({
    topology: args[0],
    ns: args[1],
    command: args[2],
    operation: "find"
  }));
  bindV3(channels.CHANNELS.MONGODB_V3_GET_MORE, (args) => ({
    topology: args[0],
    ns: args[1],
    command: args[2]?.cmd,
    operation: "getMore"
  }));
}
function bindV3(channelName, extract) {
  tracingChannel.bindTracingChannelToSpan(
    diagnosticsChannel__namespace.tracingChannel(channelName),
    (data) => {
      const args = data.arguments;
      if (!args) {
        return void 0;
      }
      const info = extract(args);
      if (!info || typeof info.ns !== "string") {
        return void 0;
      }
      return mongodbSpan.startMongoSpan(mongodbSpan.getV3SpanAttributes(info.ns, info.topology, info.command, info.operation, ORIGIN));
    },
    { requiresParentSpan: true }
  );
}
const mongodbChannelIntegration = core.defineIntegration(_mongodbChannelIntegration);

exports.mongodbChannelIntegration = mongodbChannelIntegration;
//# sourceMappingURL=mongodb.js.map
