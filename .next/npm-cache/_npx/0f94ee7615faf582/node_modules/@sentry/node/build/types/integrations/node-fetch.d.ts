import { type NodeFetchOptions } from '@sentry/node-core';
/**
 * Instrument outgoing fetch requests made through the native node `fetch` API.
 * This emits (depending on the integration options) spans and breadcrumbs, as well as injecting trace propagation headers into the request.
 */
export declare const nativeNodeFetchIntegration: (options?: NodeFetchOptions | undefined) => import("@sentry/core").Integration & {
    name: "NodeFetch";
};
//# sourceMappingURL=node-fetch.d.ts.map