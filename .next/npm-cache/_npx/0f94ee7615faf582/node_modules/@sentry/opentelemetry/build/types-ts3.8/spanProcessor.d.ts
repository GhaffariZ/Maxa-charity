import { Context } from '@opentelemetry/api';
import { ReadableSpan, Span, SpanProcessor as SpanProcessorInterface } from '@opentelemetry/sdk-trace-base';
import { Client } from '@sentry/core';
/**
 * Converts OpenTelemetry Spans to Sentry Spans and sends them to Sentry via
 * the Sentry SDK.
 */
export declare class SentrySpanProcessor implements SpanProcessorInterface {
    private _exporter;
    private _client;
    private _unsubscribePreprocessSpan;
    constructor(options?: {
        timeout?: number;
        client?: Client;
    });
    /**
     * @inheritDoc
     */
    forceFlush(): Promise<void>;
    /**
     * @inheritDoc
     */
    shutdown(): Promise<void>;
    /**
     * @inheritDoc
     */
    onStart(span: Span, parentContext: Context): void;
    /** @inheritDoc */
    onEnd(span: Span & ReadableSpan): void;
}
//# sourceMappingURL=spanProcessor.d.ts.map
