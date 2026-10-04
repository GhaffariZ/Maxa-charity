import type { Span } from '@sentry/core';
import type { GRAPHQL_DATA_SYMBOL, GRAPHQL_PATCHED_SYMBOL } from './constants';
import type { DocumentNode } from './graphql-types';
export type * from './graphql-types';
/** Bookkeeping we attach to `contextValue` to parent resolver spans under the execute span. */
interface GraphQLSpanData {
    source?: DocumentNode;
    span: Span;
    fields: Record<string, {
        span: Span;
    } | undefined>;
}
export interface ObjectWithGraphQLData {
    [GRAPHQL_DATA_SYMBOL]?: GraphQLSpanData;
}
export interface Patched {
    [GRAPHQL_PATCHED_SYMBOL]?: boolean;
}
/** Resolved integration config (defaults applied), shared by the span + resolver builders. */
export interface GraphqlResolvedConfig {
    ignoreResolveSpans: boolean;
    ignoreTrivialResolveSpans: boolean;
    useOperationNameForRootSpan: boolean;
}
//# sourceMappingURL=types.d.ts.map