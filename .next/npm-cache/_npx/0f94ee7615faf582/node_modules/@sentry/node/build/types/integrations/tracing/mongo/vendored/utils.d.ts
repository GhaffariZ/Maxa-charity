import type { Span, SpanAttributes } from '@sentry/core';
import { getV3CommandOperation, startMongoSpan } from '@sentry/server-utils';
export { getV3CommandOperation, startMongoSpan };
/** Determine a span's attributes from the v4 connection context (OTel origin). */
export declare function getV4SpanAttributes(connectionCtx: any, ns: any, command?: any, operation?: string): SpanAttributes;
/** Determine a span's attributes from the v3 topology (OTel origin). */
export declare function getV3SpanAttributes(ns: string, topology: any, command?: any, operation?: string): SpanAttributes;
/**
 * Wraps the result handler so it ends the span (with error status on failure) and runs the
 * original callback re-activated under the parent span — mongodb loses the async context when
 * it invokes the callback on a later tick.
 */
export declare function patchEnd(span: Span | undefined, resultHandler: Function): Function;
export declare function shouldSkipInstrumentation(): boolean;
//# sourceMappingURL=utils.d.ts.map