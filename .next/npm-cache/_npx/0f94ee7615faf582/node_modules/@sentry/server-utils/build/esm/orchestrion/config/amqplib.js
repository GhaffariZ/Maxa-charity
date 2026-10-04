import { toSubscribeInjections } from './subscribe-injection.js';

const module$1 = { name: "amqplib", versionRange: ">=0.5.5 <2" };
const amqplibConfig = [
  // Producer span + trace-header injection. `sendToQueue` delegates to `publish`, so it's covered.
  {
    channelName: "publish",
    module: { ...module$1, filePath: "lib/channel_model.js" },
    functionQuery: { className: "Channel", methodName: "publish", kind: "Sync" }
  },
  // Confirm-channel producer span; the trailing broker-confirm callback ends the span when the
  // broker acks/nacks. It internally calls `super.publish`, so the subscriber guards against the
  // base `publish` channel double-instrumenting.
  {
    channelName: "confirmPublish",
    module: { ...module$1, filePath: "lib/channel_model.js" },
    functionQuery: { className: "ConfirmChannel", methodName: "publish", kind: "Callback" }
  },
  // Records `consumerTag -> { noAck, queue }` so the per-message dispatch hook knows how to name and
  // when to end the consumer span.
  {
    channelName: "consume",
    module: { ...module$1, filePath: "lib/channel_model.js" },
    functionQuery: { className: "Channel", methodName: "consume", kind: "Async" }
  },
  // Per delivered message: creates the consumer span and runs the user callback under it.
  {
    channelName: "dispatch",
    module: { ...module$1, filePath: "lib/channel.js" },
    functionQuery: { className: "BaseChannel", methodName: "dispatchMessage", kind: "Sync" }
  },
  // End the consumer span when the user settles the message.
  {
    channelName: "ack",
    module: { ...module$1, filePath: "lib/channel_model.js" },
    functionQuery: { className: "Channel", methodName: "ack", kind: "Sync" }
  },
  {
    channelName: "nack",
    module: { ...module$1, filePath: "lib/channel_model.js" },
    functionQuery: { className: "Channel", methodName: "nack", kind: "Sync" }
  },
  {
    channelName: "reject",
    module: { ...module$1, filePath: "lib/channel_model.js" },
    functionQuery: { className: "Channel", methodName: "reject", kind: "Sync" }
  },
  {
    channelName: "ackAll",
    module: { ...module$1, filePath: "lib/channel_model.js" },
    functionQuery: { className: "Channel", methodName: "ackAll", kind: "Sync" }
  },
  {
    channelName: "nackAll",
    module: { ...module$1, filePath: "lib/channel_model.js" },
    functionQuery: { className: "Channel", methodName: "nackAll", kind: "Sync" }
  },
  // Stashes connection attributes (url/host/port/protocol/server product) on the connection object
  // for span-time reads via `channel.connection`.
  {
    channelName: "connect",
    module: { ...module$1, filePath: "lib/connect.js" },
    functionQuery: { functionName: "connect", kind: "Callback" }
  }
];
const amqplibChannels = {
  AMQPLIB_PUBLISH: "orchestrion:amqplib:publish",
  AMQPLIB_CONFIRM_PUBLISH: "orchestrion:amqplib:confirmPublish",
  AMQPLIB_CONSUME: "orchestrion:amqplib:consume",
  AMQPLIB_DISPATCH: "orchestrion:amqplib:dispatch",
  AMQPLIB_ACK: "orchestrion:amqplib:ack",
  AMQPLIB_NACK: "orchestrion:amqplib:nack",
  AMQPLIB_REJECT: "orchestrion:amqplib:reject",
  AMQPLIB_ACK_ALL: "orchestrion:amqplib:ackAll",
  AMQPLIB_NACK_ALL: "orchestrion:amqplib:nackAll",
  AMQPLIB_CONNECT: "orchestrion:amqplib:connect"
};
const amqplibSubscribeInjection = toSubscribeInjections(amqplibConfig);

export { amqplibChannels, amqplibConfig, amqplibSubscribeInjection };
//# sourceMappingURL=amqplib.js.map
