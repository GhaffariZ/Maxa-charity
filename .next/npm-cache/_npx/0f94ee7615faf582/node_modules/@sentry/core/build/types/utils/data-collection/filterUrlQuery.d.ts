import type { CollectBehavior } from '../../types/datacollection';
/**
 * Applies a `CollectBehavior` to the query string of a full URL, leaving every other URL component
 * (scheme, host, path, fragment) untouched.
 *
 * The query is located by string offsets rather than by parsing, so the URL is returned byte-for-byte
 * apart from the query itself. This keeps relative URLs, non-HTTP schemes and unusual encodings intact,
 * none of which survive a `URL` round-trip.
 *
 * Returns the URL with its query filtered, or with the query removed entirely when collection is off.
 */
export declare function filterUrlQuery(url: string, behavior: CollectBehavior): string;
//# sourceMappingURL=filterUrlQuery.d.ts.map