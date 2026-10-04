import { SpanKind } from '@opentelemetry/api';
import type { Span } from '@sentry/core';
/**
 * Backfill a native Sentry span with the data the OpenTelemetry SDK pipeline would otherwise derive
 * from OTel semantic attributes: `sentry.op`, `sentry.source`, the span name, `otel.kind`, and status.
 *
 * On the OTel SDK provider this happens in the `SentrySpanProcessor`/`SentrySpanExporter` while
 * converting `ReadableSpan`s to Sentry payloads (via `parseSpanDescription` + `mapStatus`).
 * `SentryTracerProvider` creates native Sentry spans directly and never goes through that pipeline,
 * so the same inference has to run here instead — once at span start, and again at span end
 * (`finalizeStatus`, once attributes like `http.route` and the status code are available).
 */
export declare function applyOtelSpanData(span: Span, options?: {
    finalizeStatus?: boolean;
}): void;
/** Stash the OTel span kind on a Sentry span so {@link applyOtelSpanData} can read it. */
export declare function applyOtelSpanKind(span: Span, kind: SpanKind | undefined): void;
//# sourceMappingURL=applyOtelSpanData.d.ts.map