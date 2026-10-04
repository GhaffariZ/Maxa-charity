import type { Tracer, TracerOptions, TracerProvider } from '@opentelemetry/api';
import type { SpanAttributes } from '@sentry/core';
/**
 * A minimal OpenTelemetry TracerProvider which creates native Sentry spans.
 */
export declare class SentryTracerProvider implements TracerProvider {
    readonly resource?: {
        attributes: SpanAttributes;
    };
    private readonly _tracers;
    constructor(options?: {
        resource?: {
            attributes: SpanAttributes;
        };
    });
    /** @inheritdoc */
    getTracer(name: string, version?: string, options?: TracerOptions): Tracer;
    /** Compatibility with SDK tracer providers. */
    forceFlush(): Promise<void>;
    /** Compatibility with SDK tracer providers. */
    shutdown(): Promise<void>;
}
//# sourceMappingURL=tracerProvider.d.ts.map