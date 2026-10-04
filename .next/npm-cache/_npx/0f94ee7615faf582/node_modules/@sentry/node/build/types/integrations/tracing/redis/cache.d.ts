import type { IORedisResponseCustomAttributeFunction } from './vendored/types';
export interface RedisOptions {
    /**
     * Define cache prefixes for cache keys that should be captured as a cache span.
     *
     * Setting this to, for example, `['user:']` will capture cache keys that start with `user:`.
     */
    cachePrefixes?: string[];
    /**
     * Maximum length of the cache key added to the span description. If the key exceeds this length, it will be truncated.
     *
     * Passing `0` will use the full cache key without truncation.
     *
     * By default, the full cache key is used.
     */
    maxCacheKeyLength?: number;
}
export declare let _redisOptions: RedisOptions;
/** Set the options consumed by {@link cacheResponseHook}. */
export declare function setRedisOptions(options: RedisOptions): void;
export declare const cacheResponseHook: IORedisResponseCustomAttributeFunction;
//# sourceMappingURL=cache.d.ts.map