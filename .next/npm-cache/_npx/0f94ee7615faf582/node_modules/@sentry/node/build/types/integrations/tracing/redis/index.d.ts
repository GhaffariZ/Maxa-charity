import { type RedisOptions } from './cache';
export { _redisOptions, cacheResponseHook } from './cache';
/**
 * To be able to preload all Redis OTel instrumentations with just one ID
 * ("Redis"), all the instrumentations are generated in this one function
 */
export declare const instrumentRedis: (() => void) & {
    id: "Redis";
};
/**
 * Adds Sentry tracing instrumentation for the [redis](https://www.npmjs.com/package/redis) and
 * [ioredis](https://www.npmjs.com/package/ioredis) libraries.
 *
 * For more information, see the [`redisIntegration` documentation](https://docs.sentry.io/platforms/javascript/guides/node/configuration/integrations/redis/).
 *
 * @example
 * ```javascript
 * const Sentry = require('@sentry/node');
 *
 * Sentry.init({
 *  integrations: [Sentry.redisIntegration()],
 * });
 * ```
 */
export declare const redisIntegration: (options?: RedisOptions | undefined) => import("@sentry/core").Integration & {
    name: "Redis";
};
//# sourceMappingURL=index.d.ts.map