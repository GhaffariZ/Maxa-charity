import type { DefinitionNode, DocumentNode, GraphQLFieldResolver, GraphQLObjectType, GraphqlResolvedConfig, Maybe, Patched } from './types';
/**
 * Walks the query/mutation type tree and swaps each field's `resolve` for a span-creating proxy.
 * Idempotent per type via {@link GRAPHQL_PATCHED_SYMBOL}.
 */
export declare function wrapFields(type: Maybe<GraphQLObjectType & Patched>, getConfig: () => GraphqlResolvedConfig): void;
export declare function wrapFieldResolver(getConfig: () => GraphqlResolvedConfig, fieldResolver: Maybe<GraphQLFieldResolver & Patched>, isDefaultResolver?: boolean): GraphQLFieldResolver & Patched;
/**
 * Returns the operation definition for `operationName` (or the first operation) from a parsed
 * document, or `undefined` for schema documents / when no operation is present.
 */
export declare function getOperation(document: DocumentNode, operationName?: Maybe<string>): DefinitionNode | undefined;
//# sourceMappingURL=resolvers.d.ts.map