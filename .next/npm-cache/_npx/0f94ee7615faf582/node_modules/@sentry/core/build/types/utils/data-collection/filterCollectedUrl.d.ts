import type { Client } from '../../client';
/**
 * Applies `dataCollection.urlQueryParams` to a URL the SDK collected itself, for use as `url.full`.
 *
 * Call this at every site where instrumentation records a URL. Routing the SDK's own URLs through a
 * helper is what makes the filtering provenance-correct: a URL a user attaches themselves never passes
 * through here, and `dataCollection` is only meant to gate automatically collected data.
 *
 * Pass the `client` the URL belongs to whenever one is at hand — falling back to `getClient()` resolves
 * against the current scope, which is the wrong client in a multi-client setup.
 */
export declare function filterCollectedUrl(url: string, client?: Client): string;
export declare function filterCollectedUrl(url: string | undefined, client?: Client): string | undefined;
/**
 * Applies `dataCollection.urlQueryParams` to a query string the SDK collected itself, for use as
 * `url.query`. Returns `undefined` when the query must not be collected at all.
 *
 * See {@link filterCollectedUrl} for why this is a helper and why passing `client` is preferred.
 */
export declare function filterCollectedUrlQuery(query: string | undefined, client?: Client): string | undefined;
//# sourceMappingURL=filterCollectedUrl.d.ts.map