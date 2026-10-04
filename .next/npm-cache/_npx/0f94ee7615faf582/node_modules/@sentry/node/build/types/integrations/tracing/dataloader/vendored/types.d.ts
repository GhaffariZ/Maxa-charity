import type { SpanLink } from '@sentry/core';
export type BatchLoadFn<K, V> = (keys: ReadonlyArray<K>) => PromiseLike<ArrayLike<V | Error>>;
/** A `DataLoader` instance. */
export interface DataLoader<K = unknown, V = unknown> {
    _batchLoadFn: BatchLoadFn<K, V>;
    _batch: {
        spanLinks?: SpanLink[];
    } | null;
    load(key: K): Promise<V>;
    loadMany(keys: ArrayLike<K>): Promise<Array<V | Error>>;
    prime(key: K, value: V | Error): this;
    clear(key: K): this;
    clearAll(): this;
    name: string | undefined;
    [key: string]: any;
}
/** The `DataLoader` class/constructor. */
export interface DataLoaderConstructor {
    new <K, V>(batchLoadFn: BatchLoadFn<K, V>, options?: any): DataLoader<K, V>;
    prototype: DataLoader<unknown, unknown>;
}
//# sourceMappingURL=types.d.ts.map