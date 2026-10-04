import type { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase } from '@opentelemetry/instrumentation';
import type { ClientRequest, IncomingMessage, ServerResponse } from 'node:http';
import type { HttpClientRequest, HttpIncomingMessage, Span } from '@sentry/core';
import * as http from 'node:http';
interface OutgoingHttpRequestInstrumentationOptions {
    /**
     * Whether breadcrumbs should be recorded for outgoing requests.
     *
     * @default `true`
     */
    breadcrumbs?: boolean;
    /**
     * Whether to create spans for outgoing requests (user preference).
     * This only takes effect if `createSpansForOutgoingRequests` is not disabled.
     * If `createSpansForOutgoingRequests` is explicitly set false, this option is ignored.
     *
     * @default `true`
     */
    spans?: boolean;
    /**
     * Whether to propagate Sentry trace headers in outgoing requests.
     *
     * @default `true`
     */
    propagateTraceInOutgoingRequests?: boolean;
    /**
     * @deprecated Use spans option instead.
     *
     * @default `true`
     */
    createSpansForOutgoingRequests?: boolean;
    /**
     * Do not instrument outgoing HTTP requests to URLs where the given callback returns `true`.
     * When it returns `true`, the request is skipped entirely: no breadcrumb and no span are created,
     * and no trace headers are propagated for that request.
     *
     * @param url Contains the entire URL, including query string (if any), protocol, host, etc. of the outgoing request.
     * @param request Contains the {@type RequestOptions} object used to make the outgoing request.
     */
    ignoreOutgoingRequests?: (url: string, request: http.RequestOptions) => boolean;
    /**
     * Hooks for outgoing request spans, only called when spans are created for outgoing requests
     * (i.e. when `spans` is enabled).
     * These mirror the OTEL HttpInstrumentation hooks for backwards compatibility.
     */
    outgoingRequestHook?: (span: Span, request: ClientRequest | HttpClientRequest) => void;
    outgoingResponseHook?: (span: Span, response: IncomingMessage | HttpIncomingMessage) => void;
    outgoingRequestApplyCustomAttributes?: (span: Span, request: HttpClientRequest, response: HttpIncomingMessage) => void;
}
export type SentryHttpInstrumentationOptions = InstrumentationConfig & OutgoingHttpRequestInstrumentationOptions & {
    /**
     * @deprecated This no longer does anything.
     */
    extractIncomingTraceFromHeader?: boolean;
    /**
     * @deprecated This no longer does anything.
     */
    ignoreStaticAssets?: boolean;
    /**
     * @deprecated This no longer does anything.
     */
    disableIncomingRequestSpans?: boolean;
    /**
     * @deprecated This no longer does anything.
     */
    ignoreSpansForIncomingRequests?: (urlPath: string, request: IncomingMessage) => boolean;
    /**
     * @deprecated This no longer does anything.
     */
    ignoreIncomingRequestBody?: (url: string, request: http.RequestOptions) => boolean;
    /**
     * @deprecated This no longer does anything.
     */
    maxIncomingRequestBodySize?: 'none' | 'small' | 'medium' | 'always';
    /**
     * @deprecated This no longer does anything.
     */
    trackIncomingRequestsAsSessions?: boolean;
    /**
     * @deprecated This no longer does anything.
     */
    instrumentation?: {
        requestHook?: (span: Span, req: ClientRequest | IncomingMessage) => void;
        responseHook?: (span: Span, response: IncomingMessage | ServerResponse) => void;
        applyCustomAttributesOnSpan?: (span: Span, request: ClientRequest | IncomingMessage, response: IncomingMessage | ServerResponse) => void;
    };
    /**
     * @deprecated This no longer does anything.
     */
    sessionFlushingDelayMS?: number;
};
/**
 * This instruments the http modules for outgoing requests.
 * It uses the diagnostics channel if available, otherwise it falls back to monkey-patching.
 *
 * The instrumentation will start spans, create breadcrumbs, and propagate trace headers in outgoing requests (depending on the settings).
 * This can be called multiple times, where the last invocation will have the current options.
 *
 * @TODO Cleanup options in v11
 */
export declare function instrumentHttpOutgoingRequests(instrumentationOptions?: OutgoingHttpRequestInstrumentationOptions): void;
/**
 * This custom HTTP instrumentation handles outgoing HTTP requests.
 *
 * It provides:
 * - Breadcrumbs for all outgoing requests
 * - Trace propagation headers (when enabled)
 * - Span creation for outgoing requests (when createSpansForOutgoingRequests is enabled)
 *
 * Span creation requires Node 22+ and uses diagnostic channels to avoid monkey-patching.
 * By default, this is only enabled in the node SDK, not in node-core or other runtime SDKs.
 *
 * Important note: Contrary to other OTEL instrumentation, this one cannot be unwrapped.
 *
 * This is heavily inspired & adapted from:
 * https://github.com/open-telemetry/opentelemetry-js/blob/f8ab5592ddea5cba0a3b33bf8d74f27872c0367f/experimental/packages/opentelemetry-instrumentation-http/src/http.ts
 *
 * @deprecated This will be removed in v11. Use instrumentHttpOutgoingRequests() instead.
 */
export declare class SentryHttpInstrumentation extends InstrumentationBase<SentryHttpInstrumentationOptions> {
    constructor(config?: SentryHttpInstrumentationOptions);
    /** @inheritdoc */
    init(): void;
}
export {};
//# sourceMappingURL=SentryHttpInstrumentation.d.ts.map