import type { EachBatchHandler, EachMessageHandler } from './types';
/** Whether `fn` is a callback this module already wrapped, so callers skip re-wrapping it. */
export declare function isWrappedConsumerCallback(fn: unknown): boolean;
/** Wraps `eachMessage` so each processed message becomes a consumer span parented to the message's producer. */
export declare function wrapEachMessage(original: EachMessageHandler): EachMessageHandler;
/** Wraps `eachBatch` so the batch pull becomes a fresh-root receiving span with a process span per message. */
export declare function wrapEachBatch(original: EachBatchHandler): EachBatchHandler;
//# sourceMappingURL=consumer.d.ts.map