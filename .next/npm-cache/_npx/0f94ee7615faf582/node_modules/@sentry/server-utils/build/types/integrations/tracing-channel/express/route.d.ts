import type { ExpressLayer, ExpressRequest } from './types';
/** Record the path pattern a layer was registered with. */
export declare function setLayerRegisteredPath(layer: ExpressLayer, path: string | undefined): void;
/** Read the path pattern a layer was registered with, if any. */
export declare function getLayerRegisteredPath(layer: ExpressLayer): string | undefined;
/** Push a layer's registered path onto the request's chain. */
export declare function pushLayerPath(req: ExpressRequest, path: string): void;
/** Pop the most recently pushed layer path off the request's chain. */
export declare function popLayerPath(req: ExpressRequest): void;
/**
 * The path pattern a `route`/`use` call registered, derived from its arguments.
 * A leading string/RegExp/number path becomes the pattern (arrays are joined
 * with `,`); a bare handler function yields `undefined`. Kept in sync with
 * `@sentry/core`'s Express `getLayerPath`.
 */
export declare function getLayerPath(args: unknown[]): string | undefined;
/**
 * Concatenate the stored layer paths into the full route pattern (parameters
 * preserved), e.g. `/api/:version/user`. Mirrors `@sentry/core`.
 */
export declare function getConstructedRoute(req: ExpressRequest): string;
/**
 * Validate the constructed route against the request URL, returning it only
 * when it plausibly corresponds to a real match (otherwise `undefined`). Mirrors
 * `@sentry/core`'s `getActualMatchedRoute` — used for the `http.route` attribute.
 */
export declare function getActualMatchedRoute(req: ExpressRequest, constructedRoute: string): string | undefined;
//# sourceMappingURL=route.d.ts.map