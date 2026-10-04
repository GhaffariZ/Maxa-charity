import * as diagnosticsChannel from 'node:diagnostics_channel';
import { DB_STATEMENT, NET_PEER_PORT, NET_PEER_NAME, DB_SYSTEM } from '@sentry/conventions/attributes';
import { defineIntegration, debug, waitForTracingChannelBinding, startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { defaultDbStatementSerializer } from '../../redis/redis-statement-serializer.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "IORedis";
const ORIGIN = "auto.db.orchestrion.redis";
const ATTR_DB_CONNECTION_STRING = "db.connection_string";
function getConnectionOptions(self) {
  return { host: self?.options?.host, port: self?.options?.port };
}
function connectionAttributes(host, port) {
  return {
    [DB_SYSTEM]: "redis",
    [ATTR_DB_CONNECTION_STRING]: `redis://${host}:${port}`,
    [NET_PEER_NAME]: host,
    [NET_PEER_PORT]: port,
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
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
  const statement = defaultDbStatementSerializer(command.name, command.args ?? []);
  return startInactiveSpan({
    name: statement,
    op: "db",
    attributes: { ...connectionAttributes(host, port), [DB_STATEMENT]: statement }
  });
}
const _ioredisChannelIntegration = ((options = {}) => {
  const responseHook = options.responseHook;
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log(`[orchestrion:ioredis] subscribing to "${CHANNELS.IOREDIS_COMMAND}"/"${CHANNELS.IOREDIS_CONNECT}"`);
      const commandChannel = diagnosticsChannel.tracingChannel(
        CHANNELS.IOREDIS_COMMAND
      );
      const connectChannel = diagnosticsChannel.tracingChannel(
        CHANNELS.IOREDIS_CONNECT
      );
      waitForTracingChannelBinding(() => {
        bindTracingChannelToSpan(commandChannel, startIORedisCommandSpan, {
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
        bindTracingChannelToSpan(
          connectChannel,
          (data) => {
            const { host, port } = getConnectionOptions(data.self);
            return startInactiveSpan({
              name: "connect",
              op: "db",
              attributes: { ...connectionAttributes(host, port), [DB_STATEMENT]: "connect" }
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
const ioredisChannelIntegration = defineIntegration(_ioredisChannelIntegration);

export { ioredisChannelIntegration, startIORedisCommandSpan };
//# sourceMappingURL=ioredis.js.map
