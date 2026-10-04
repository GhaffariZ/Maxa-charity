Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const mongooseDcSubscriber = require('../../mongoose/mongoose-dc-subscriber.js');
const mongooseLegacySpan = require('../../mongoose/mongoose-legacy-span.js');
const channels = require('../../orchestrion/channels.js');
const mongoose = require('../../orchestrion/config/mongoose.js');
const debugBuild = require('../../debug-build.js');
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

const INTEGRATION_NAME = "Mongoose";
const ORIGIN = "auto.db.orchestrion.mongoose";
const STORED_PARENT_SPAN = /* @__PURE__ */ new WeakMap();
let orchestrionSubscribed = false;
const _mongooseChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        mongooseDcSubscriber.subscribeMongooseDiagnosticChannels(diagnosticsChannel__namespace.tracingChannel);
        subscribeOrchestrionMongooseChannels();
      });
    }
  };
});
function subscribeOrchestrionMongooseChannels() {
  if (orchestrionSubscribed) {
    return;
  }
  orchestrionSubscribed = true;
  debugBuild.DEBUG_BUILD && core.debug.log("[orchestrion:mongoose] subscribing to injected channels");
  for (const channelName of mongoose.MONGOOSE_CONTEXT_CAPTURE_CHANNELS) {
    channel(channelName).subscribe({
      start(message) {
        stashParentSpan(message.self);
      }
    });
  }
  channel(channels.CHANNELS.MONGOOSE_MODEL_AGGREGATE).subscribe({
    end(message) {
      const result = message.result;
      if (result && typeof result === "object") {
        stashParentSpan(result);
      }
    }
  });
  bindExecSpan(channels.CHANNELS.MONGOOSE_QUERY_EXEC, (self) => {
    const query = self;
    return startSpan(
      query.mongooseCollection,
      query.model?.modelName,
      query.op ?? "exec",
      STORED_PARENT_SPAN.get(self)
    );
  });
  bindExecSpan(channels.CHANNELS.MONGOOSE_AGGREGATE_EXEC, (self) => {
    const model = self._model;
    return startSpan(model?.collection, model?.modelName, "aggregate", STORED_PARENT_SPAN.get(self));
  });
  bindExecSpan(channels.CHANNELS.MONGOOSE_MODEL_SAVE, (self) => {
    const ctor = self.constructor;
    return startSpan(ctor.collection, ctor.modelName, "save");
  });
  bindExecSpan(channels.CHANNELS.MONGOOSE_MODEL_REMOVE, (self) => {
    const ctor = self.constructor;
    return startSpan(ctor.collection, ctor.modelName, "remove");
  });
  bindExecSpan(channels.CHANNELS.MONGOOSE_MODEL_INSERT_MANY, (self) => {
    const model = self;
    return startSpan(model.collection, model.modelName, "insertMany");
  });
  bindExecSpan(channels.CHANNELS.MONGOOSE_MODEL_BULK_WRITE, (self) => {
    const model = self;
    return startSpan(model.collection, model.modelName, "bulkWrite");
  });
}
function startSpan(collection, modelName, operation, parentSpan) {
  return mongooseLegacySpan.startMongooseLegacySpan({ collection, modelName, operation, origin: ORIGIN, parentSpan });
}
function channel(channelName) {
  return diagnosticsChannel__namespace.tracingChannel(
    channelName
  );
}
function bindExecSpan(channelName, getSpan) {
  tracingChannel.bindTracingChannelToSpan(
    diagnosticsChannel__namespace.tracingChannel(channelName),
    (data) => {
      const self = data.self;
      if (!self) {
        return void 0;
      }
      return getSpan(self);
    }
  );
}
function stashParentSpan(self) {
  const active = core.getActiveSpan();
  if (self && active) {
    STORED_PARENT_SPAN.set(self, active);
  }
}
const mongooseChannelIntegration = core.defineIntegration(_mongooseChannelIntegration);

exports.mongooseChannelIntegration = mongooseChannelIntegration;
//# sourceMappingURL=mongoose.js.map
