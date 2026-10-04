import { SERVER_PORT, SERVER_ADDRESS, DB_QUERY_TEXT, DB_SYSTEM_NAME, DB_OPERATION_BATCH_SIZE } from '@sentry/conventions/attributes';
import { startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_OP, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { bindTracingChannelToSpan } from '../tracing-channel.js';

const REDIS_DC_CHANNEL_COMMAND = "node-redis:command";
const REDIS_DC_CHANNEL_BATCH = "node-redis:batch";
const REDIS_DC_CHANNEL_CONNECT = "node-redis:connect";
const IOREDIS_DC_CHANNEL_COMMAND = "ioredis:command";
const IOREDIS_DC_CHANNEL_CONNECT = "ioredis:connect";
const ORIGIN = "auto.db.redis.diagnostic_channel";
const DB_SYSTEM_NAME_VALUE_REDIS = "redis";
function subscribeRedisDiagnosticChannels(tracingChannel, responseHook) {
  setupCommandChannel(
    tracingChannel,
    REDIS_DC_CHANNEL_COMMAND,
    (data) => data.args.slice(1),
    responseHook
  );
  setupBatchChannel(
    tracingChannel,
    REDIS_DC_CHANNEL_BATCH,
    (data) => data.batchMode === "PIPELINE" ? "PIPELINE" : "MULTI"
  );
  setupConnectChannel(tracingChannel, REDIS_DC_CHANNEL_CONNECT);
  setupCommandChannel(tracingChannel, IOREDIS_DC_CHANNEL_COMMAND, (data) => data.args, responseHook);
  setupConnectChannel(tracingChannel, IOREDIS_DC_CHANNEL_CONNECT);
}
function setupCommandChannel(tracingChannel, channelName, getCommandArgs, responseHook) {
  bindTracingChannelToSpan(
    tracingChannel(channelName),
    (data) => {
      const args = getCommandArgs(data);
      const statement = args.length ? `${data.command} ${args.join(" ")}` : data.command;
      return startInactiveSpan({
        name: `redis-${data.command}`,
        attributes: {
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db.redis",
          [DB_SYSTEM_NAME]: DB_SYSTEM_NAME_VALUE_REDIS,
          [DB_QUERY_TEXT]: statement,
          ...data.serverAddress != null ? { [SERVER_ADDRESS]: data.serverAddress } : {},
          ...data.serverPort != null ? { [SERVER_PORT]: data.serverPort } : {}
        }
      });
    },
    {
      beforeSpanEnd(span, data) {
        if ("error" in data) return;
        runResponseHook(responseHook, span, data.command, getCommandArgs(data), data.result);
      }
    }
  );
}
function setupBatchChannel(tracingChannel, channelName, getOperationName) {
  bindTracingChannelToSpan(tracingChannel(channelName), (data) => {
    return startInactiveSpan({
      name: getOperationName(data),
      attributes: {
        [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db.redis",
        [DB_SYSTEM_NAME]: DB_SYSTEM_NAME_VALUE_REDIS,
        // should only include batch size greater than 1,
        // or else it isn't properly considered a "batch"
        ...Number(data.batchSize) > 1 ? { [DB_OPERATION_BATCH_SIZE]: data.batchSize } : {},
        ...data.serverAddress != null ? { [SERVER_ADDRESS]: data.serverAddress } : {},
        ...data.serverPort != null ? { [SERVER_PORT]: data.serverPort } : {}
      }
    });
  });
}
function setupConnectChannel(tracingChannel, channelName) {
  bindTracingChannelToSpan(tracingChannel(channelName), (data) => {
    return startInactiveSpan({
      name: "redis-connect",
      attributes: {
        [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db.redis.connect",
        [DB_SYSTEM_NAME]: DB_SYSTEM_NAME_VALUE_REDIS,
        ...data.serverAddress != null ? { [SERVER_ADDRESS]: data.serverAddress } : {},
        ...data.serverPort != null ? { [SERVER_PORT]: data.serverPort } : {}
      }
    });
  });
}
function runResponseHook(hook, span, command, args, result) {
  if (!hook) return;
  try {
    hook(span, command, args, result);
  } catch {
  }
}

export { IOREDIS_DC_CHANNEL_COMMAND, IOREDIS_DC_CHANNEL_CONNECT, REDIS_DC_CHANNEL_BATCH, REDIS_DC_CHANNEL_COMMAND, REDIS_DC_CHANNEL_CONNECT, subscribeRedisDiagnosticChannels };
//# sourceMappingURL=redis-dc-subscriber.js.map
