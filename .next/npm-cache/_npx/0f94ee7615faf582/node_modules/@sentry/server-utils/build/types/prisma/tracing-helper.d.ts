import type { Span } from '@sentry/core';
import type { EngineSpan, ExtendedSpanOptions, SpanCallback, TracingHelper } from './types';
type Options = {
    ignoreSpanTypes: (string | RegExp)[];
};
type V5EngineSpanEvent = {
    span: boolean;
    spans: V5EngineSpan[];
};
type V5EngineSpanKind = 'client' | 'internal';
type V5EngineSpan = {
    span: boolean;
    name: string;
    trace_id: string;
    span_id: string;
    parent_span_id: string;
    start_time: [number, number];
    end_time: [number, number];
    attributes?: Record<string, string>;
    links?: {
        trace_id: string;
        span_id: string;
    }[];
    kind: V5EngineSpanKind;
};
/**
 * This satisifes the TracingHelper interface for Prisma v5 and v6/v7.
 */
export declare class ActiveTracingHelper implements TracingHelper {
    private ignoreSpanTypes;
    constructor({ ignoreSpanTypes }: Options);
    isEnabled(): boolean;
    getTraceParent(span?: Span): string;
    dispatchEngineSpans(spans: EngineSpan[]): void;
    /**
     * Prisma v5 broke the tracing helper interface with the v6 major, replacing `createEngineSpan` with
     * `dispatchEngineSpans`. We implement the v6/v7 interface (`dispatchEngineSpans`) but also keep this
     * v5-only method so the same helper doesn't blow up in Prisma 5 users' faces, minting v5 engine spans
     * through Sentry's span APIs instead of crashing.
     */
    createEngineSpan(engineSpanEvent: V5EngineSpanEvent): void;
    getActiveContext(): Span | undefined;
    runInChildSpan<R>(nameOrOptions: string | ExtendedSpanOptions, callback: SpanCallback<R>): R;
}
export {};
//# sourceMappingURL=tracing-helper.d.ts.map