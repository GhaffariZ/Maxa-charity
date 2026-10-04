import type { SpanKindValue } from '@sentry/core';
export type CommandInput = Record<string, any>;
/**
 * These are normalized request and response. They organize the relevant data in one interface which
 * can be processed in a uniform manner in the per-service hooks.
 */
export interface NormalizedRequest {
    serviceName: string;
    commandName: string;
    commandInput: CommandInput;
    region?: string;
}
export interface NormalizedResponse {
    data: any;
    request: NormalizedRequest;
    requestId?: string;
}
/** Span metadata a per-service extension returns for the subscriber to build the span from. */
export interface RequestMetadata {
    isStream?: boolean;
    spanAttributes?: Record<string, unknown>;
    spanKind?: SpanKindValue;
    spanName?: string;
    spanOp?: string;
}
//# sourceMappingURL=types.d.ts.map