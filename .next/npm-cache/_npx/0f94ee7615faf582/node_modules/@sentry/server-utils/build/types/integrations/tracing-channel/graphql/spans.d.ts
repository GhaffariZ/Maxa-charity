import type { Span } from '@sentry/core';
import type { GraphqlResolvedConfig } from './types';
export declare function startParseSpan(): Span;
/** `documentAST` is the 2nd argument to `validate(schema, documentAST, …)`. */
export declare function startValidateSpan(documentAST: unknown): Span;
/** `result` is validation's return value: a (possibly empty) array of errors. */
export declare function finalizeValidateSpan(span: Span, result: unknown): void;
/**
 * Opens the execute span and, unless resolver spans are disabled, swaps the schema's field resolvers
 * (and the default field resolver) for span-creating proxies — mutating the live `arguments` in place
 * so the wrapped `execute` call runs with them. Always returns a span; the caller guards against
 * throws (see `safe` in `index.ts`).
 */
export declare function startExecuteSpan(argsArray: unknown[], self: unknown, config: GraphqlResolvedConfig, getConfig: () => GraphqlResolvedConfig): Span;
/** `result` is the settled `ExecutionResult`; GraphQL errors surface on `result.errors`, not a throw. */
export declare function finalizeExecuteSpan(span: Span, result: unknown): void;
//# sourceMappingURL=spans.d.ts.map