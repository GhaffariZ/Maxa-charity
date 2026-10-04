import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, waitForTracingChannelBinding, debug, getActiveSpan } from '@sentry/core';
import { subscribeMongooseDiagnosticChannels } from '../../mongoose/mongoose-dc-subscriber.js';
import { startMongooseLegacySpan } from '../../mongoose/mongoose-legacy-span.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { MONGOOSE_CONTEXT_CAPTURE_CHANNELS } from '../../orchestrion/config/mongoose.js';
import { DEBUG_BUILD } from '../../debug-build.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "Mongoose";
const ORIGIN = "auto.db.orchestrion.mongoose";
const STORED_PARENT_SPAN = /* @__PURE__ */ new WeakMap();
let orchestrionSubscribed = false;
const _mongooseChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      waitForTracingChannelBinding(() => {
        subscribeMongooseDiagnosticChannels(diagnosticsChannel.tracingChannel);
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
  DEBUG_BUILD && debug.log("[orchestrion:mongoose] subscribing to injected channels");
  for (const channelName of MONGOOSE_CONTEXT_CAPTURE_CHANNELS) {
    channel(channelName).subscribe({
      start(message) {
        stashParentSpan(message.self);
      }
    });
  }
  channel(CHANNELS.MONGOOSE_MODEL_AGGREGATE).subscribe({
    end(message) {
      const result = message.result;
      if (result && typeof result === "object") {
        stashParentSpan(result);
      }
    }
  });
  bindExecSpan(CHANNELS.MONGOOSE_QUERY_EXEC, (self) => {
    const query = self;
    return startSpan(
      query.mongooseCollection,
      query.model?.modelName,
      query.op ?? "exec",
      STORED_PARENT_SPAN.get(self)
    );
  });
  bindExecSpan(CHANNELS.MONGOOSE_AGGREGATE_EXEC, (self) => {
    const model = self._model;
    return startSpan(model?.collection, model?.modelName, "aggregate", STORED_PARENT_SPAN.get(self));
  });
  bindExecSpan(CHANNELS.MONGOOSE_MODEL_SAVE, (self) => {
    const ctor = self.constructor;
    return startSpan(ctor.collection, ctor.modelName, "save");
  });
  bindExecSpan(CHANNELS.MONGOOSE_MODEL_REMOVE, (self) => {
    const ctor = self.constructor;
    return startSpan(ctor.collection, ctor.modelName, "remove");
  });
  bindExecSpan(CHANNELS.MONGOOSE_MODEL_INSERT_MANY, (self) => {
    const model = self;
    return startSpan(model.collection, model.modelName, "insertMany");
  });
  bindExecSpan(CHANNELS.MONGOOSE_MODEL_BULK_WRITE, (self) => {
    const model = self;
    return startSpan(model.collection, model.modelName, "bulkWrite");
  });
}
function startSpan(collection, modelName, operation, parentSpan) {
  return startMongooseLegacySpan({ collection, modelName, operation, origin: ORIGIN, parentSpan });
}
function channel(channelName) {
  return diagnosticsChannel.tracingChannel(
    channelName
  );
}
function bindExecSpan(channelName, getSpan) {
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(channelName),
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
  const active = getActiveSpan();
  if (self && active) {
    STORED_PARENT_SPAN.set(self, active);
  }
}
const mongooseChannelIntegration = defineIntegration(_mongooseChannelIntegration);

export { mongooseChannelIntegration };
//# sourceMappingURL=mongoose.js.map
