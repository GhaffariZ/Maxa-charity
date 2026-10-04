import type { CollectBehavior } from '../../types/datacollection';
/**
 * Filters a query parameter string according to a `CollectBehavior`.
 *
 * Parameter names are decoded for filtering, while the original encoding, order, and duplicate keys are preserved.
 */
export declare function filterQueryParams(queryString: string, behavior: CollectBehavior): string | undefined;
//# sourceMappingURL=filterQueryParams.d.ts.map