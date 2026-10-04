import type { SpanContext } from '@opentelemetry/api';
import type { Client, Span } from '@sentry/core';
/**
 * OpenTelemetry only knows about SAMPLED or NONE decision,
 * but for us it is important to differentiate between unset and unsampled.
 *
 * Both of these are identified as `traceFlags === TracegFlags.NONE`,
 * but we additionally look at a special trace state to differentiate between them.
 */
export declare function getSamplingDecision(spanContext: SpanContext): boolean | undefined;
/**
 * Resolve a span's sampling decision for trace propagation, also handling native Sentry spans.
 *
 * Prefer the OpenTelemetry trace state via {@link getSamplingDecision}. Native Sentry spans (created
 * by the `SentryTracerProvider`) don't carry that trace state, so when it's absent we fall back to the
 * span's own decision via `spanIsSampled` — but only for an *explicit* decision. An explicit decision
 * originates at a real `SentrySpan` root (a negatively sampled root, or a child of one) or an ignored
 * segment root. Other non-recording placeholder roots (orphan/suppressed spans or TwP placeholders)
 * and remote spans have a *deferred* decision that lives elsewhere (the scope, or the incoming trace
 * state), so we return `undefined` rather than wrongly asserting `-0`.
 *
 * TODO(v11): Once the OTel SDK provider is gone and every local span is a native Sentry span, the
 * trace-state lookup only matters for remote (incoming) spans; the local path always reads the span's
 * own decision, so the "native-vs-OTel-SDK span" framing can be dropped (local → span, remote → trace state).
 */
export declare function getSampledForPropagation(span: Span, client: Client | undefined): boolean | undefined;
//# sourceMappingURL=getSamplingDecision.d.ts.map