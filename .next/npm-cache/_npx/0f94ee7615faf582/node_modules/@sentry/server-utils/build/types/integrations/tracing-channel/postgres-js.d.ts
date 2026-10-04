import type { PostgresConnectionContext, Span } from '@sentry/core';
export interface PostgresJsChannelIntegrationOptions {
    /**
     * Only create spans when there's already an active parent span. Defaults to
     * `true`, matching the OTel `postgresJsIntegration`.
     */
    requireParentSpan?: boolean;
    /**
     * Hook to modify the query span before the query runs. Receives the span, the
     * sanitized SQL, and (when resolvable) the connection context.
     */
    requestHook?: (span: Span, sanitizedSqlQuery: string, postgresConnectionContext?: PostgresConnectionContext) => void;
}
/**
 * EXPERIMENTAL — orchestrion-driven postgres.js (`postgres` v3.x) integration.
 *
 * Subscribes to the `orchestrion:postgres:handle` / `:connection` / `:execute` /
 * `:connect` diagnostics channels injected into postgres.js' `Query.prototype.handle`
 * and `Connection`/`execute`/`connect` (in `src/*` and `cjs/src/*`) and creates db
 * spans matching the OTel `postgresJsIntegration`. Requires the orchestrion runtime
 * hook or bundler plugin.
 */
export declare const postgresJsChannelIntegration: (options?: PostgresJsChannelIntegrationOptions | undefined) => import("@sentry/core").Integration & {
    name: "PostgresJs";
};
//# sourceMappingURL=postgres-js.d.ts.map