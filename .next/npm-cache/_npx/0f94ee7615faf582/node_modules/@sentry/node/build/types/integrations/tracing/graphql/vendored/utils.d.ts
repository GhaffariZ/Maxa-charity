import type { DocumentNode, GraphQLFieldResolver, GraphQLObjectType, Location, Maybe } from './graphql-types';
import type { Span } from '@sentry/core';
import type { ObjectWithGraphQLData, OtelPatched } from './internal-types';
import type { GraphQLInstrumentationParsedConfig } from './types';
export declare const isPromise: (value: any) => value is Promise<unknown>;
export declare function addSpanSource(span: Span, loc?: Location, start?: number, end?: number): void;
export declare function endSpan(span: Span, error?: Error): void;
export declare function getOperation(document: DocumentNode, operationName?: Maybe<string>): DefinitionNodeLike | undefined;
type DefinitionNodeLike = DocumentNode['definitions'][number];
export declare function getSourceFromLocation(loc?: Location, inputStart?: number, inputEnd?: number): string;
export declare function wrapFields(type: Maybe<GraphQLObjectType & OtelPatched>, getConfig: () => GraphQLInstrumentationParsedConfig): void;
export declare function wrapFieldResolver<TSource = any, TContext = any, TArgs = any>(getConfig: () => GraphQLInstrumentationParsedConfig, fieldResolver: Maybe<GraphQLFieldResolver<TSource, TContext, TArgs> & OtelPatched>, isDefaultResolver?: boolean): GraphQLFieldResolver<TSource, TContext & ObjectWithGraphQLData, TArgs> & OtelPatched;
export {};
//# sourceMappingURL=utils.d.ts.map