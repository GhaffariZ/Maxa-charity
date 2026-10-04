/** Shared, state-free helpers for the Vercel AI (`ai`) channel subscribers, plus the streamed model-call tap. */
/** Narrow to a string, or `undefined` for anything else. */
export declare function asString(value: unknown): string | undefined;
/** Narrow to a finite number, or `undefined` for anything else (including `NaN`). */
export declare function asNumber(value: unknown): number | undefined;
/** Add two optional numbers, treating a missing operand as `0` but returning `undefined` when both are absent. */
export declare function sum(a: number | undefined, b: number | undefined): number | undefined;
/** The aggregate handed back once a streamed model call finishes, in the shape `enrichSpanOnEnd` expects. */
export interface StreamedModelCallResult {
    text?: string;
    toolCalls: Array<Record<string, unknown>>;
    usage?: unknown;
    finishReason?: unknown;
    responseId?: string;
    responseModel?: string;
    providerMetadata?: unknown;
}
/** A minimal structural check — the streamed model call exposes a web `ReadableStream` on `result.stream`. */
export declare function isReadableStream(value: unknown): value is ReadableStream<unknown>;
/**
 * Wrap a streamed model call's `ReadableStream` so its chunks are observed as the SDK consumes them,
 * without altering what the SDK sees. Returns a replacement stream to swap onto `result.stream`.
 *
 * `onFinal` runs exactly once when the stream drains cleanly; `onError` runs exactly once if it errors
 * or is cancelled. Reading one source chunk per `pull` preserves the SDK's backpressure. The
 * `try/catch` around every read guarantees the owning span is always ended — a leaked open span on a
 * mid-stream failure would be worse than a slightly-less-enriched one.
 */
export declare function tapModelCallStream(stream: ReadableStream<unknown>, onFinal: (result: StreamedModelCallResult) => void, onError: (error: unknown) => void): ReadableStream<unknown>;
//# sourceMappingURL=util.d.ts.map