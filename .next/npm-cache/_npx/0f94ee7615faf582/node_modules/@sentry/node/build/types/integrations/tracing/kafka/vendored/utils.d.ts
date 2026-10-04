import type { Span, SpanAttributes, SpanLink } from '@sentry/core';
import type { KafkaMessage, Message } from './kafkajs-types';
export interface ConsumerSpanOptions {
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
 * Resolves once `sendPromise` settles, ending all `spans` and, on failure, marking them with the
 * error status and `error.type` before re-throwing.
 */
export declare function endSpansOnPromise<T>(spans: Span[], sendPromise: Promise<T>): Promise<T>;
//# sourceMappingURL=utils.d.ts.map