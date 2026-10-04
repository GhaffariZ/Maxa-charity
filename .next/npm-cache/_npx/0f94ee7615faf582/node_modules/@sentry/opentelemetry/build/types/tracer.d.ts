import type { Context, Span as OpenTelemetrySpan, SpanOptions, Tracer } from '@opentelemetry/api';
export declare class SentryTracer implements Tracer {
    /** @inheritdoc */
    startSpan(name: string, options?: SpanOptions, ctx?: Context): OpenTelemetrySpan;
    /** @inheritdoc */
    startActiveSpan<F extends (span: OpenTelemetrySpan) => unknown>(name: string, fn: F): ReturnType<F>;
    startActiveSpan<F extends (span: OpenTelemetrySpan) => unknown>(name: string, options: SpanOptions, fn: F): ReturnType<F>;
    startActiveSpan<F extends (span: OpenTelemetrySpan) => unknown>(name: string, options: SpanOptions, ctx: Context, fn: F): ReturnType<F>;
    private _startSentrySpan;
    private _startRootSpanWithRemoteParent;
    private _createNonRecordingSpan;
}
//# sourceMappingURL=tracer.d.ts.map