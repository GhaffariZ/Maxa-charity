import type { Span } from '@sentry/core';
import type { TracingChannel } from 'node:diagnostics_channel';
import { type StreamedModelCallResult } from './util';
/** Drop the per-operation `callId` maps once the owning top-level operation settles (success or error). */
export declare function clearOperationId(data: VercelAiChannelMessage): void;
/**
 * Drop the per-operation `callId` maps for a single id. The v6 orchestrion adapter uses this to clear a
 * `streamText` operation only after its lazily-run model call settles — the operation's own span ends
 * synchronously (when `streamText` returns) but the model call runs later as the stream is consumed, and
 * it still needs the operation's `operationId`/`isStream` entry to name itself `ai.streamText.doStream`.
 */
export declare function clearOperationCallId(callId: string): void;
/** The lifecycle event types the `ai:telemetry` channel can carry. */
export type ChannelEventType = 'generateText' | 'streamText' | 'generateObject' | 'step' | 'languageModelCall' | 'executeTool' | 'embed' | 'embedMany' | 'rerank';
/**
 * The context object the AI SDK passes through one tracing-channel call. It is the same object
 * identity across `start`/`end`/`asyncEnd`/`error`, and Node's `tracingChannel` attaches
 * `result`/`error` to it as the traced promise settles.
 */
export interface VercelAiChannelMessage {
    type: ChannelEventType;
    event: Record<string, unknown>;
    result?: unknown;
    error?: unknown;
}
/**
 * Platform-provided factory that returns a tracing channel for the given channel name. The factory
 * is responsible for, when `start` fires, calling `transformStart(data)` and storing the returned
 * span on `data._sentrySpan` so the subscriber's `asyncEnd`/`error` handlers can read it.
 *
 * Node passes `@sentry/opentelemetry/tracing-channel`, which uses `bindStore` to additionally make
 * the span the active OTel context for the duration of the traced operation. That is what makes
 * nested AI SDK operations (model calls, tool calls) become children of the enclosing span without
 * any manual parent bookkeeping here.
 */
export type VercelAiTracingChannelFactory = <T extends object>(name: string) => TracingChannel<T, T>;
/** Integration-level recording options, pinned at subscribe time so we never look the integration up per event. */
export interface VercelAiChannelOptions {
    recordInputs?: boolean;
    recordOutputs?: boolean;
    enableTruncation?: boolean;
}
/**
 * Subscribe Sentry span handlers to the `ai` SDK's native telemetry tracing channel (`ai:telemetry`,
 * available in `ai` >= 7) and emit fully-formed `gen_ai.*` spans directly — no OpenTelemetry span
 * post-processing involved.
 *
 * The integration passes its options in directly (rather than us looking the integration up on every
 * event); the global `dataCollection.genAI` default is still read from the client per event.
 *
 * Safe to always call: on `ai` versions that don't publish to the channel (e.g. < 7) nothing is
 * ever emitted and this is inert, so there is no double-instrumentation against the OTel-based
 * patcher. The integration's `setupOnce` guarantees this runs a single time.
 */
export declare function subscribeVercelAiTracingChannel(tracingChannel: VercelAiTracingChannelFactory, options?: VercelAiChannelOptions): void;
/** Map the tapped stream aggregate onto the `languageModelCall` result shape `enrichSpanOnEnd` reads. */
export declare function streamedResultToChannelResult(final: StreamedModelCallResult): Record<string, unknown>;
/**
 * Transform a channel `start` payload into the span that should be active for the operation. For
 * `step` we deliberately don't open a span (model calls and tool calls are siblings under the
 * invoke_agent span, matching the OTel-based output), so we reuse the active span and mark the
 * payload to skip ending it.
 */
export declare function createSpanFromMessage(data: VercelAiChannelMessage, channelOptions: VercelAiChannelOptions): Span | undefined;
/**
 * Best-effort enrichment from the resolved value the AI SDK attaches to the channel context.
 * Everything here is guarded: when a field is missing or the shape differs across `ai` versions,
 * we simply don't set the attribute rather than emit a malformed span.
 */
export declare function enrichSpanOnEnd(span: Span, data: VercelAiChannelMessage, channelOptions: VercelAiChannelOptions): void;
export declare function captureToolError(span: Span, data: VercelAiChannelMessage, error: unknown): void;
//# sourceMappingURL=vercel-ai-dc-subscriber.d.ts.map