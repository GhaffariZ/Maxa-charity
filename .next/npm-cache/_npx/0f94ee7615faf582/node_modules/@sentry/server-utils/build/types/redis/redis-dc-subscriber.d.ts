import type { TracingChannel } from 'node:diagnostics_channel';
import type { Span } from '@sentry/core';
export declare const REDIS_DC_CHANNEL_COMMAND = "node-redis:command";
export declare const REDIS_DC_CHANNEL_BATCH = "node-redis:batch";
export declare const REDIS_DC_CHANNEL_CONNECT = "node-redis:connect";
export declare const IOREDIS_DC_CHANNEL_COMMAND = "ioredis:command";
export declare const IOREDIS_DC_CHANNEL_CONNECT = "ioredis:connect";
/**
 * Shape of the `node-redis:command` channel payload published by node-redis.
 *
 * Both `command` and `args` are already redacted by node-redis itself (see
 * `sanitizeArgs` in @redis/client) using the OTel `redis-common` rules. The
 * arg array is `[<command>, <safe arg>, ..., '?', ...]` — `?` replaces any
 * value the library considers sensitive. Subscribers can emit `args` directly
 * as `db.query.text` without further serialization.
 */
export interface RedisCommandData {
    command: string;
    /** First arg is the command name itself; consumers should slice it off. */
    args: string[];
    database?: number;
    serverAddress?: string;
    serverPort?: number;
    result?: unknown;
    error?: Error;
}
/**
 * Shape of the `ioredis:command` channel payload published by ioredis >= 5.11.0.
 *
 * As with node-redis, args are already sanitized by ioredis (`sanitizeArgs` in
 * `lib/tracing.ts`) before publishing. Unlike node-redis, the command name is
 * NOT prepended to `args`.
 */
export interface IORedisCommandData {
    command: string;
    args: string[];
    batchMode?: 'MULTI';
    batchSize?: number;
    database?: number;
    serverAddress?: string;
    serverPort?: number;
    result?: unknown;
    error?: Error;
}
/** Shape of the `node-redis:batch` channel payload published by node-redis. */
export interface RedisBatchData {
    batchMode?: 'MULTI' | 'PIPELINE';
    batchSize?: number;
    database?: number;
    clientId?: string | number;
    serverAddress?: string;
    serverPort?: number;
    result?: unknown[];
    error?: Error;
}
/** Shape of the `*:connect` channel payload published by node-redis and ioredis. */
export interface RedisConnectData {
    serverAddress?: string;
    serverPort?: number;
    url?: string;
    error?: Error;
}
/**
 * Optional callback invoked once the redis command response arrives. Useful
 * for attaching response-derived attributes (e.g. cache hit/miss, payload size).
 *
 * Mirrors `@opentelemetry/instrumentation-ioredis`' response hook so existing
 * Sentry node code (`cacheResponseHook`) can be reused unchanged.
 */
export type RedisDiagnosticChannelResponseHook = (span: Span, cmdName: string, cmdArgs: string[], result: unknown) => void;
/**
 * Platform-provided factory that creates a native tracing channel for the given name. The
 * subscriber binds the span and its lifecycle onto the channel via `bindTracingChannelToSpan`,
 * which propagates the active span through the runtime's async context.
 *
 * Both Node and Deno pass `node:diagnostics_channel`'s `tracingChannel` directly.
 */
export type RedisTracingChannelFactory = <T extends object>(name: string) => TracingChannel<T, T>;
/**
 * Subscribe Sentry span handlers to node-redis and ioredis diagnostics-channel
 * events: `node-redis:command`/`:batch`/`:connect` (published by node-redis
 * >= 5.12.0) and `ioredis:command`/`:connect` (published by ioredis >= 5.11.0).
 *
 * On older client versions the channels are never published to, so subscribers
 * are inert — there is no double-instrumentation against any IITM-based
 * patcher gated to those older versions.
 */
export declare function subscribeRedisDiagnosticChannels(tracingChannel: RedisTracingChannelFactory, responseHook?: RedisDiagnosticChannelResponseHook): void;
//# sourceMappingURL=redis-dc-subscriber.d.ts.map