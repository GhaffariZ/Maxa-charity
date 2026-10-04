import type { TracingChannel, TracingChannelSubscribers } from 'node:diagnostics_channel';
import type { ExclusiveEventHintOrCaptureContext, Span } from '@sentry/core';
export type TracingChannelPayloadWithSpan<TData extends object> = TData & {
    /**
     * The current active span for the traced call.
     */
    _sentrySpan?: Span;
    /**
     * The context's active store value, used to restore the context for asyncStart continuations for callback-based tracing.
     */
    _sentryCallerStore?: unknown;
    /** Set by Node's tracing channel when the traced operation failed. */
    error?: unknown;
};
export interface SentryTracingChannel<TData extends object = object> extends Omit<TracingChannel<TData, TracingChannelPayloadWithSpan<TData>>, 'subscribe' | 'unsubscribe'> {
    subscribe(subscribers: Partial<TracingChannelSubscribers<TracingChannelPayloadWithSpan<TData>>>): void;
    unsubscribe(subscribers: Partial<TracingChannelSubscribers<TracingChannelPayloadWithSpan<TData>>>): void;
}
export interface TracingChannelLifeCycleOptions<TData extends object = object> {
    /**
     * Invoked with the span and the channel context object once the traced operation completes
     * Use it to enrich the span from the result/error (branch on `'error' in data` / `'result' in data`) or to run cleanup.
     */
    beforeSpanEnd?: (span: Span, data: TracingChannelPayloadWithSpan<TData>) => void;
    /**
     * Whether a thrown error is captured as a Sentry event. The span is always marked with error status regardless. Defaults to `false`.
     * You can alternatively pass a function that sets the ExclusiveEventHintOrCaptureContext on the captured error.
     * Set `true` for instrumentations that own the error boundary, (e.g: route handlers)
     * For database drivers, it is not recommended to set this at all.
     */
    captureError?: boolean | ((e: unknown) => ExclusiveEventHintOrCaptureContext);
    /**
     * Take ownership of *when* the span ends: return `true` and the helper won't end it on
     * `end`/`asyncEnd`. For results that settle out-of-band — e.g. a streamed `EventEmitter` that
     * completes via its own `'end'`/`'error'` events.
     *
     * Call `end` when it settles — `end()` on success, `end(error)` on failure. `end` owns *how* the span
     * ends (error status/attributes, `captureError`, `beforeSpanEnd`) and is idempotent. Default `false`
     * lets the helper end the span as usual.
     */
    deferSpanEnd?: (args: {
        span: Span;
        data: TracingChannelPayloadWithSpan<TData>;
        /** Ends the span: `end()` on success, `end(error)` on failure (which marks `data` as errored). Idempotent. */
        end: (error?: unknown) => void;
    }) => boolean;
    /**
     * Apply span/scope binding only if there is a parent span, similar to returning `undefined` in the `getSpan` callback.
     * If you need to perform some conditional checking on the parent span before deciding, do it in the `getSpan` callback.
     */
    requiresParentSpan?: boolean;
}
/** Returned by {@link bindTracingChannelToSpan}: the bound channel plus a teardown handle. */
export interface TracingChannelBindingHandle<TData extends object = object> {
    /**
     * The tracing channel with the span bound into async context.
     */
    channel: SentryTracingChannel<TData>;
    /**
     * Tears down the binding: unsubscribes lifecycle handlers, when present, and unbinds the start store.
     * Idempotent, and a no-op when no async context binding was available.
     */
    unbind: () => void;
}
/**
 * Bind a span and its lifecycle to a tracing channel so the span becomes the active async context
 * for the traced operation and is ended when the operation completes.
 *
 * `getSpan` may return `undefined` to opt a payload out entirely: nothing is bound, no span is
 * tracked, and the active context is left untouched. Use it for events that ride the same channel
 * but should reuse the enclosing span instead of opening (and ending) their own — e.g. an agent
 * loop's per-step events, where ending a freshly opened span would close the parent prematurely.
 */
export declare function bindTracingChannelToSpan<TData extends object>(channel: TracingChannel<TData, TData>, getSpan: (data: TracingChannelPayloadWithSpan<TData>) => Span | undefined, opts?: TracingChannelLifeCycleOptions<TData>): TracingChannelBindingHandle<TData>;
//# sourceMappingURL=tracing-channel.d.ts.map