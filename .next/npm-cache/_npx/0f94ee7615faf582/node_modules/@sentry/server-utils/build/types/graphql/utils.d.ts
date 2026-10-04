import type { Span } from '@sentry/core';
/** Minimal shape of a graphql-js lexer token, enough to locate literal spans for redaction. */
interface GraphqlToken {
    kind: string;
    start: number;
    end: number;
    next?: GraphqlToken | null;
}
/** Minimal shape of a parsed graphql-js `DocumentNode`, enough to read its source and tokens. */
export interface GraphqlDocumentNode {
    loc?: {
        startToken?: GraphqlToken;
        source?: {
            body?: string;
        };
    };
}
/**
 * Rename the enclosing root span to include the operation name(s), e.g. `GET /graphql (query GetUser)`.
 * Mirrors the legacy OTel `useOperationNameForRootSpan` behavior; `parseSpanDescription` reads the same
 * `sentry.graphql.operation` attribute on the OTel export path.
 */
export declare function renameRootSpanWithOperation(span: Span, operationType: string, operationName?: string): void;
/**
 * Span name follows the GraphQL semantic conventions: `<operation.type> <operation.name>` when both
 * are available, `<operation.type>` when only the type is, otherwise a static fallback.
 */
export declare function getOperationSpanName(operationType: string | undefined, operationName: string | undefined, fallbackName: string): string;
/** Whether a graphql execution result carries GraphQL errors (returned on `result.errors`). */
export declare function hasResultErrors(result: unknown): boolean;
/**
 * Returns the redacted document if `dataCollection.graphQL.document` is enabled, `undefined` otherwise.
 */
export declare function collectGraphqlDocument(document: GraphqlDocumentNode | undefined): string | undefined;
export {};
//# sourceMappingURL=utils.d.ts.map