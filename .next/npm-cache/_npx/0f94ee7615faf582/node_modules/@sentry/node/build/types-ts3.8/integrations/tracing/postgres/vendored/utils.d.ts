import { Span, SpanAttributes } from '@sentry/core';
import { PgClientExtended, PgParsedConnectionParams, PgPoolCallback, PgPoolOptionsParams, PostgresCallback } from './internal-types';
export declare const ORIGIN = "auto.db.otel.postgres";
/**
 * Helper function to get a low cardinality span name from whatever info we have
 * about the query.
 *
 * This is tricky, because we don't have most of the information (table name,
 * operation name, etc) the spec recommends using to build a low-cardinality
 * value w/o parsing. So, we use db.name and assume that, if the query's a named
 * prepared statement, those `name` values will be low cardinality. If we don't
 * have a named prepared statement, we try to parse an operation (despite the
 * spec's warnings).
 *
 * @params dbName The name of the db against which this query is being issued,
 *   which could be missing if no db name was given at the time that the
 *   connection was established.
 * @params queryConfig Information we have about the query being issued, typed
 *   to reflect only the validation we've actually done on the args to
 *   `client.query()`. This will be undefined if `client.query()` was called
 *   with invalid arguments.
 */
export declare function getQuerySpanName(dbName: string | undefined, queryConfig?: {
    text: string;
    name?: unknown;
}): string;
export declare function parseNormalizedOperationName(queryText: string): string;
export declare function parseAndMaskConnectionString(connectionString: string): string;
export declare function getConnectionString(params: PgParsedConnectionParams | PgPoolOptionsParams): string;
export declare function getSemanticAttributesFromConnection(params: PgParsedConnectionParams): SpanAttributes;
export declare function getSemanticAttributesFromPoolConnection(params: PgPoolOptionsParams): SpanAttributes;
/**
 * The SDK always requires a parent span (it sets `requireParentSpan: true`), so
 * we only instrument when there is an active span to parent the new span under.
 */
export declare function shouldSkipInstrumentation(): boolean;
export declare function handleConfigQuery(this: PgClientExtended, queryConfig?: {
    text: string;
    values?: unknown;
    name?: unknown;
}): Span;
export declare function patchCallback(span: Span, cb: PostgresCallback): PostgresCallback;
export declare function patchCallbackPGPool(span: Span, cb: PgPoolCallback): PgPoolCallback;
export declare function patchClientConnectCallback(span: Span, cb: (...args: unknown[]) => void): (...args: unknown[]) => void;
/**
 * Attempt to get a message string from a thrown value, while being quite
 * defensive, to recognize the fact that, in JS, any kind of value (even
 * primitives) can be thrown.
 */
export declare function getErrorMessage(e: unknown): string | undefined;
export declare function isObjectWithTextString(it: unknown): it is ObjectWithText;
export type ObjectWithText = {
    text: string;
    [k: string]: unknown;
};
//# sourceMappingURL=utils.d.ts.map
