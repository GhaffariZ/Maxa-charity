import * as diagnosticsChannel from 'node:diagnostics_channel';
import { SERVER_PORT, SERVER_ADDRESS, DB_OPERATION_BATCH_SIZE, DB_SYSTEM_NAME, DB_STATEMENT, NET_PEER_PORT, NET_PEER_NAME, DB_SYSTEM } from '@sentry/conventions/attributes';
import { defineIntegration, debug, waitForTracingChannelBinding, getActiveSpan, withActiveSpan, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_OP, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SPAN_STATUS_ERROR, isObjectLike } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { defaultDbStatementSerializer } from '../../redis/redis-statement-serializer.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "RedisChannel";
const ORIGIN = "auto.db.orchestrion.redis";
const ATTR_DB_CONNECTION_STRING = "db.connection_string";
const DB_SYSTEM_VALUE_REDIS = "redis";
function endSpan(span, err) {
  if (err) {
    span.setStatus({ code: SPAN_STATUS_ERROR, message: err instanceof Error ? err.message : String(err) });
  }
  span.end();
}
function runResponseHook(hook, span, command, args, result) {
  if (!hook) {
    return;
  }
  try {
    hook(span, command, args, result);
  } catch {
  }
}
function stripCommandOptions(args) {
  const first = args[0];
  if (isObjectLike(first) && Object.getOwnPropertySymbols(first).length > 0) {
    return args.slice(1);
  }
  return args;
}
function removeCredentialsFromConnectionString(url) {
  if (typeof url !== "string" || !url) {
    return void 0;
  }
  try {
    const parsed = new URL(url);
    parsed.searchParams.delete("user_pwd");
    parsed.username = "";
    parsed.password = "";
    return parsed.href;
  } catch {
    return void 0;
  }
}
function nodeRedisAttributes(options) {
  return {
    [DB_SYSTEM]: DB_SYSTEM_VALUE_REDIS,
    [NET_PEER_NAME]: options?.socket?.host,
    [NET_PEER_PORT]: options?.socket?.port,
    [ATTR_DB_CONNECTION_STRING]: removeCredentialsFromConnectionString(options?.url),
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
  };
}
function startCommandSpan(commandName, commandArgs, attributes) {
  return startInactiveSpan({
    name: `redis-${commandName}`,
    kind: SPAN_KIND.CLIENT,
    attributes: {
      ...attributes,
      [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
      [DB_STATEMENT]: defaultDbStatementSerializer(commandName, commandArgs)
    }
  });
}
function subscribeLegacyRedisCommand(responseHook) {
  const channel = diagnosticsChannel.tracingChannel(CHANNELS.REDIS_COMMAND);
  const noop = () => {
  };
  channel.subscribe({
    end: noop,
    asyncStart: noop,
    asyncEnd: noop,
    start(data) {
      const command = data.arguments?.[0];
      if (!command || typeof command !== "object") {
        return;
      }
      const originalCallback = command.callback;
      if (typeof originalCallback !== "function") {
        return;
      }
      const client = data.self;
      const attributes = {
        [DB_SYSTEM]: DB_SYSTEM_VALUE_REDIS,
        [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
      };
      attributes[NET_PEER_NAME] = client?.connection_options?.host;
      attributes[NET_PEER_PORT] = client?.connection_options?.port;
      if (client?.address) {
        attributes[ATTR_DB_CONNECTION_STRING] = `redis://${client.address}`;
      }
      const span = startCommandSpan(command.command, command.args ?? [], attributes);
      data._sentrySpan = span;
      const parentSpan = getActiveSpan();
      command.callback = function(err, reply) {
        if (!err) {
          runResponseHook(responseHook, span, command.command, command.args ?? [], reply);
        }
        endSpan(span, err);
        const args = arguments;
        return withActiveSpan(parentSpan ?? null, () => originalCallback.apply(this, args));
      };
    },
    error(data) {
      const span = data._sentrySpan;
      if (span) {
        endSpan(span, data.error);
      }
    }
  });
}
function bindNodeRedisCommandChannel(channelName, getWireArgs, responseHook) {
  const channel = diagnosticsChannel.tracingChannel(channelName);
  bindTracingChannelToSpan(
    channel,
    (data) => {
      const wireArgs = getWireArgs(data);
      if (!wireArgs?.length) {
        return void 0;
      }
      const commandName = String(wireArgs[0]);
      const options = data.self?.options;
      return startCommandSpan(commandName, wireArgs.slice(1), nodeRedisAttributes(options));
    },
    {
      beforeSpanEnd(span, data) {
        if ("error" in data || !responseHook) {
          return;
        }
        const wireArgs = getWireArgs(data);
        if (wireArgs?.length) {
          runResponseHook(responseHook, span, String(wireArgs[0]), wireArgs.slice(1), data.result);
        }
      }
    }
  );
}
function getSendCommandArgs(data) {
  const args = data.arguments?.[0];
  return Array.isArray(args) ? args : void 0;
}
function getExecutorArgs(data) {
  const command = data.arguments?.[0];
  const jsArgs = data.arguments?.[1];
  if (typeof command?.transformArguments !== "function" || !Array.isArray(jsArgs)) {
    return void 0;
  }
  try {
    return command.transformArguments(...stripCommandOptions(jsArgs));
  } catch {
    return void 0;
  }
}
function bindNodeRedisConnectChannel() {
  const channel = diagnosticsChannel.tracingChannel(CHANNELS.NODE_REDIS_CONNECT);
  bindTracingChannelToSpan(channel, (data) => {
    const options = data.self?.options;
    return startInactiveSpan({
      name: "redis-connect",
      kind: SPAN_KIND.CLIENT,
      attributes: { ...nodeRedisAttributes(options), [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db" }
    });
  });
}
function bindNodeRedisBatchChannel(channelName, getOperation) {
  const channel = diagnosticsChannel.tracingChannel(channelName);
  bindTracingChannelToSpan(channel, (data) => {
    const commands = data.arguments?.[0];
    const size = Array.isArray(commands) ? commands.length : void 0;
    const socket = data.self?.options?.socket;
    return startInactiveSpan({
      name: getOperation(data),
      kind: SPAN_KIND.CLIENT,
      attributes: {
        [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        [SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db.redis",
        [DB_SYSTEM_NAME]: DB_SYSTEM_VALUE_REDIS,
        ...size && size > 1 ? { [DB_OPERATION_BATCH_SIZE]: size } : {},
        ...socket?.host != null ? { [SERVER_ADDRESS]: socket.host } : {},
        ...socket?.port != null ? { [SERVER_PORT]: socket.port } : {}
      }
    });
  });
}
const _redisChannelIntegration = ((options = {}) => {
  const responseHook = options.responseHook;
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log(`[orchestrion:redis] subscribing to "${CHANNELS.REDIS_COMMAND}" and node-redis channels`);
      subscribeLegacyRedisCommand(responseHook);
      waitForTracingChannelBinding(() => {
        bindNodeRedisCommandChannel(CHANNELS.NODE_REDIS_COMMAND, getSendCommandArgs, responseHook);
        bindNodeRedisCommandChannel(CHANNELS.NODE_REDIS_EXECUTOR, getExecutorArgs, responseHook);
        bindNodeRedisConnectChannel();
        bindNodeRedisBatchChannel(CHANNELS.NODE_REDIS_MULTI, () => "MULTI");
        bindNodeRedisBatchChannel(CHANNELS.NODE_REDIS_PIPELINE, () => "PIPELINE");
        bindNodeRedisBatchChannel(
          CHANNELS.NODE_REDIS_BATCH,
          (data) => data.arguments?.[2] !== void 0 ? "MULTI" : "PIPELINE"
        );
      });
    }
  };
});
const redisChannelIntegration = defineIntegration(_redisChannelIntegration);

export { redisChannelIntegration };
//# sourceMappingURL=redis.js.map
