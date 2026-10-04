import type { Span, SpanAttributes, SpanLink } from '@sentry/core';
import type { KafkaMessage, Message } from './types';
interface ConsumerSpanOptions {
    topic: string;
    message: KafkaMessage | undefined;
    operationType: string;
    attributes: SpanAttributes;
    links?: SpanLink[];
}
/**
 * Reads a header value off a kafkajs message as a string. kafkajs delivers headers as `Buffer`s (or
 * arrays of them), so we normalize to a string before handing them to Sentry's trace helpers.
 */
export declare function getHeaderAsString(headers: KafkaMessage['headers'], key: string): string | undefined;
/**
 * Builds a span link to the producer span carried in the message headers, mirroring the upstream
 * behavior of linking each batch-processed message to its originating producer span.
 */
export declare function getLinksFromHeaders(headers: KafkaMessage['headers']): SpanLink[] | undefined;
/** Starts an inactive consumer (process/receive) span carrying the kafkajs messaging attributes. */
export declare function startConsumerSpan({ topic, message, operationType, links, attributes }: ConsumerSpanOptions): Span;
/** Starts an inactive producer span and propagates its trace into the message headers. */
export declare function startProducerSpan(topic: string, message: Message): Span;
/**
 * Marks all `spans` with the error status and `error.type` derived from a settle-time `reason`,
 * WITHOUT ending them. Mirrors the vendored `endSpansOnPromise` catch block. The producer path (which
 * receives discrete `error`/`asyncEnd` channel events) applies the error on `error` and ends on
 * `asyncEnd`; the consumer path folds this into `endSpansOnPromise`.
 */
export declare function applyErrorToSpans(spans: Span[], reason: unknown): void;
/**
 * Resolves once `promise` settles, applying the error status to all `spans` on failure and ending
 * them regardless. Used by the consumer path, which invokes the user callback directly (and so holds a
 * real promise). Verbatim from the vendored `endSpansOnPromise`.
 */
export declare function endSpansOnPromise<T>(spans: Span[], promise: Promise<T>): Promise<T>;
export {};
//# sourceMappingURL=spans.d.ts.map