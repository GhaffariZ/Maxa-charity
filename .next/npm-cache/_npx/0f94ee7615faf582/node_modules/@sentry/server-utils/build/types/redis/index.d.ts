import { type RedisDiagnosticChannelResponseHook } from './redis-dc-subscriber';
/** Options controlling the redis diagnostics-channel subscription. */
export interface RedisDiagnosticChannelsOptions {
    /**
     * Optional hook invoked once the redis command response arrives. Useful for attaching
     * response-derived attributes (e.g. cache hit/miss, payload size).
     */
    responseHook?: RedisDiagnosticChannelResponseHook;
}
/**
 * Auto-instrument the [redis](https://www.npmjs.com/package/redis) and
 * [ioredis](https://www.npmjs.com/package/ioredis) libraries via their native
 * `node:diagnostics_channel` tracing channels (node-redis >= 5.12.0, ioredis >= 5.11.0).
 */
export declare const redisIntegration: (options?: RedisDiagnosticChannelsOptions | undefined) => import("@sentry/core").Integration & {
    name: string;
};
//# sourceMappingURL=index.d.ts.map