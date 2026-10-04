Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const debugBuild = require('../../debug-build.js');
const channels = require('../../orchestrion/channels.js');
const redisStatementSerializer = require('../../redis/redis-statement-serializer.js');
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

const INTEGRATION_NAME = "RedisChannel";
const ORIGIN = "auto.db.orchestrion.redis";
const ATTR_DB_CONNECTION_STRING = "db.connection_string";
const DB_SYSTEM_VALUE_REDIS = "redis";
function endSpan(span, err) {
  if (err) {
    span.setStatus({ code: core.SPAN_STATUS_ERROR, message: err instanceof Error ? err.message : String(err) });
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
  if (core.isObjectLike(first) && Object.getOwnPropertySymbols(first).length > 0) {
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
    [attributes.DB_SYSTEM]: DB_SYSTEM_VALUE_REDIS,
    [attributes.NET_PEER_NAME]: options?.socket?.host,
    [attributes.NET_PEER_PORT]: options?.socket?.port,
    [ATTR_DB_CONNECTION_STRING]: removeCredentialsFromConnectionString(options?.url),
    [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
  };
}
function startCommandSpan(commandName, commandArgs, attributes$1) {
  return core.startInactiveSpan({
    name: `redis-${commandName}`,
    kind: core.SPAN_KIND.CLIENT,
    attributes: {
      ...attributes$1,
      [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
      [attributes.DB_STATEMENT]: redisStatementSerializer.defaultDbStatementSerializer(commandName, commandArgs)
    }
  });
}
function subscribeLegacyRedisCommand(responseHook) {
  const channel = diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.REDIS_COMMAND);
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
      const attributes$1 = {
        [attributes.DB_SYSTEM]: DB_SYSTEM_VALUE_REDIS,
        [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
      };
      attributes$1[attributes.NET_PEER_NAME] = client?.connection_options?.host;
      attributes$1[attributes.NET_PEER_PORT] = client?.connection_options?.port;
      if (client?.address) {
        attributes$1[ATTR_DB_CONNECTION_STRING] = `redis://${client.address}`;
      }
      const span = startCommandSpan(command.command, command.args ?? [], attributes$1);
      data._sentrySpan = span;
      const parentSpan = core.getActiveSpan();
      command.callback = function(err, reply) {
        if (!err) {
          runResponseHook(responseHook, span, command.command, command.args ?? [], reply);
        }
        endSpan(span, err);
        const args = arguments;
        return core.withActiveSpan(parentSpan ?? null, () => originalCallback.apply(this, args));
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
  const channel = diagnosticsChannel__namespace.tracingChannel(channelName);
  tracingChannel.bindTracingChannelToSpan(
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
  const channel = diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.NODE_REDIS_CONNECT);
  tracingChannel.bindTracingChannelToSpan(channel, (data) => {
    const options = data.self?.options;
    return core.startInactiveSpan({
      name: "redis-connect",
      kind: core.SPAN_KIND.CLIENT,
      attributes: { ...nodeRedisAttributes(options), [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db" }
    });
  });
}
function bindNodeRedisBatchChannel(channelName, getOperation) {
  const channel = diagnosticsChannel__namespace.tracingChannel(channelName);
  tracingChannel.bindTracingChannelToSpan(channel, (data) => {
    const commands = data.arguments?.[0];
    const size = Array.isArray(commands) ? commands.length : void 0;
    const socket = data.self?.options?.socket;
    return core.startInactiveSpan({
      name: getOperation(data),
      kind: core.SPAN_KIND.CLIENT,
      attributes: {
        [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db.redis",
        [attributes.DB_SYSTEM_NAME]: DB_SYSTEM_VALUE_REDIS,
        ...size && size > 1 ? { [attributes.DB_OPERATION_BATCH_SIZE]: size } : {},
        ...socket?.host != null ? { [attributes.SERVER_ADDRESS]: socket.host } : {},
        ...socket?.port != null ? { [attributes.SERVER_PORT]: socket.port } : {}
      }
    });
  });
}
const _redisChannelIntegration = ((options = {}) => {
  const responseHook = options.responseHook;
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:redis] subscribing to "${channels.CHANNELS.REDIS_COMMAND}" and node-redis channels`);
      subscribeLegacyRedisCommand(responseHook);
      core.waitForTracingChannelBinding(() => {
        bindNodeRedisCommandChannel(channels.CHANNELS.NODE_REDIS_COMMAND, getSendCommandArgs, responseHook);
        bindNodeRedisCommandChannel(channels.CHANNELS.NODE_REDIS_EXECUTOR, getExecutorArgs, responseHook);
        bindNodeRedisConnectChannel();
        bindNodeRedisBatchChannel(channels.CHANNELS.NODE_REDIS_MULTI, () => "MULTI");
        bindNodeRedisBatchChannel(channels.CHANNELS.NODE_REDIS_PIPELINE, () => "PIPELINE");
        bindNodeRedisBatchChannel(
          channels.CHANNELS.NODE_REDIS_BATCH,
          (data) => data.arguments?.[2] !== void 0 ? "MULTI" : "PIPELINE"
        );
      });
    }
  };
});
const redisChannelIntegration = core.defineIntegration(_redisChannelIntegration);

exports.redisChannelIntegration = redisChannelIntegration;
//# sourceMappingURL=redis.js.map
