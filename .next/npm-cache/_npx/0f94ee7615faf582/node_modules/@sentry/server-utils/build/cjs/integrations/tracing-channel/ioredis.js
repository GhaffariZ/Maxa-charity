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

const INTEGRATION_NAME = "IORedis";
const ORIGIN = "auto.db.orchestrion.redis";
const ATTR_DB_CONNECTION_STRING = "db.connection_string";
function getConnectionOptions(self) {
  return { host: self?.options?.host, port: self?.options?.port };
}
function connectionAttributes(host, port) {
  return {
    [attributes.DB_SYSTEM]: "redis",
    [ATTR_DB_CONNECTION_STRING]: `redis://${host}:${port}`,
    [attributes.NET_PEER_NAME]: host,
    [attributes.NET_PEER_PORT]: port,
    [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
  };
}
const tracedCommands = /* @__PURE__ */ new WeakSet();
function startIORedisCommandSpan(data) {
  const command = data.arguments?.[0];
  if (!command || typeof command !== "object") {
    return void 0;
  }
  if (tracedCommands.has(command)) {
    return void 0;
  }
  tracedCommands.add(command);
  const { host, port } = getConnectionOptions(data.self);
  const statement = redisStatementSerializer.defaultDbStatementSerializer(command.name, command.args ?? []);
  return core.startInactiveSpan({
    name: statement,
    op: "db",
    attributes: { ...connectionAttributes(host, port), [attributes.DB_STATEMENT]: statement }
  });
}
const _ioredisChannelIntegration = ((options = {}) => {
  const responseHook = options.responseHook;
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:ioredis] subscribing to "${channels.CHANNELS.IOREDIS_COMMAND}"/"${channels.CHANNELS.IOREDIS_CONNECT}"`);
      const commandChannel = diagnosticsChannel__namespace.tracingChannel(
        channels.CHANNELS.IOREDIS_COMMAND
      );
      const connectChannel = diagnosticsChannel__namespace.tracingChannel(
        channels.CHANNELS.IOREDIS_CONNECT
      );
      core.waitForTracingChannelBinding(() => {
        tracingChannel.bindTracingChannelToSpan(commandChannel, startIORedisCommandSpan, {
          // ioredis' `requireParentSpan` default: only create a span under an active span.
          requiresParentSpan: true,
          beforeSpanEnd(span, data) {
            if ("error" in data || !responseHook) {
              return;
            }
            const command = data.arguments?.[0];
            if (command) {
              runResponseHook(responseHook, span, command, data.result);
            }
          }
        });
        tracingChannel.bindTracingChannelToSpan(
          connectChannel,
          (data) => {
            const { host, port } = getConnectionOptions(data.self);
            return core.startInactiveSpan({
              name: "connect",
              op: "db",
              attributes: { ...connectionAttributes(host, port), [attributes.DB_STATEMENT]: "connect" }
            });
          },
          { requiresParentSpan: true }
        );
      });
    }
  };
});
function runResponseHook(hook, span, command, result) {
  try {
    hook(span, command.name, command.args, result);
  } catch {
  }
}
const ioredisChannelIntegration = core.defineIntegration(_ioredisChannelIntegration);

exports.ioredisChannelIntegration = ioredisChannelIntegration;
exports.startIORedisCommandSpan = startIORedisCommandSpan;
//# sourceMappingURL=ioredis.js.map
