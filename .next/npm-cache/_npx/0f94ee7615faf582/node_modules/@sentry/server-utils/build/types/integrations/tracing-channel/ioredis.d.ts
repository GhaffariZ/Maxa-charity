import type { Span } from '@sentry/core';
/** Mirrors `@opentelemetry/instrumentation-ioredis`' response hook. Not called for failed commands. */
export type IORedisResponseHook = (span: Span, command: string, args: Array<string | Buffer>, result: unknown) => void;
export interface IORedisChannelIntegrationOptions {
    responseHook?: IORedisResponseHook;
}
interface RedisClientLike {
    options?: {
        host?: string;
        port?: number;
    };
}
interface IORedisCommandContext {
    arguments?: unknown[];
    self?: RedisClientLike;
    result?: unknown;
    error?: unknown;
}
/**
 * Builds the db span for an `orchestrion:ioredis:command` payload, or returns `undefined` to skip
 * it: for a non-command payload, or the offline-queue re-send of an already-traced command.
 *
 * Exported for unit testing.
 */
export declare function startIORedisCommandSpan(data: IORedisCommandContext): Span | undefined;
/**
 * EXPERIMENTAL — orchestrion-driven ioredis integration. Subscribes to
 * `orchestrion:ioredis:command` / `:connect` (injected into ioredis' `<5.11.0`
 * `sendCommand`/`connect`) and creates db spans matching
 * `@opentelemetry/instrumentation-ioredis`. Requires the orchestrion runtime hook
 * or bundler plugin.
 */
export declare const ioredisChannelIntegration: (options?: IORedisChannelIntegrationOptions | undefined) => import("@sentry/core").Integration & {
    name: "IORedis";
};
export {};
//# sourceMappingURL=ioredis.d.ts.map