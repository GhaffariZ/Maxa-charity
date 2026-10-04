import type { Span } from '@sentry/core';
/** Mirrors `@opentelemetry/instrumentation-redis`' response hook. Not called for failed commands. */
export type RedisResponseHook = (span: Span, command: string, args: Array<string | Buffer>, result: unknown) => void;
export interface RedisChannelIntegrationOptions {
    responseHook?: RedisResponseHook;
}
/**
 * EXPERIMENTAL — orchestrion-driven redis integration for `redis` v2-v3 and
 * node-redis v4/v5 `<5.12.0` (`@redis/client`). Covers single commands, `connect`,
 * and multi/pipeline batches, fully replacing `@opentelemetry/instrumentation-redis`.
 * Requires the orchestrion runtime hook or bundler plugin.
 */
export declare const redisChannelIntegration: (options?: RedisChannelIntegrationOptions | undefined) => import("@sentry/core").Integration & {
    name: "RedisChannel";
};
//# sourceMappingURL=redis.d.ts.map