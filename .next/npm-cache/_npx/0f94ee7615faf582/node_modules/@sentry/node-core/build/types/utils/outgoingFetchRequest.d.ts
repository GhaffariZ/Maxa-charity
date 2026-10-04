import type { LRUMap, Span } from '@sentry/core';
import type { UndiciRequest, UndiciResponse } from '../integrations/node-fetch/types';
/**
 * Add trace propagation headers to an outgoing fetch/undici request.
 *
 * Checks if the request URL matches trace propagation targets,
 * then injects sentry-trace, traceparent, and baggage headers.
 *
 * When a `span` is passed (the outgoing `http.client` span), its trace data is propagated so downstream
 * services are parented to that span. Without a span, the active scope's trace data is used.
 *
 * Existing trace headers (e.g. set manually by the user via `getTraceData()`) always take precedence and
 * are de-duplicated rather than overwritten, so we never emit two `sentry-trace`/`baggage` entries.
 */
export declare function addTracePropagationHeadersToFetchRequest(request: UndiciRequest, propagationDecisionMap: LRUMap<string, boolean>, span?: Span): void;
/** Add a breadcrumb for an outgoing fetch/undici request. */
export declare function addFetchRequestBreadcrumb(request: UndiciRequest, response: UndiciResponse): void;
/** Get the absolute URL from an origin and path. */
export declare function getAbsoluteUrl(origin: string, path?: string): string;
//# sourceMappingURL=outgoingFetchRequest.d.ts.map