import type { TracingChannel } from 'node:diagnostics_channel';
import type { GraphqlDocumentNode } from './utils';
export declare const GRAPHQL_DC_CHANNEL_PARSE = "graphql:parse";
export declare const GRAPHQL_DC_CHANNEL_VALIDATE = "graphql:validate";
export declare const GRAPHQL_DC_CHANNEL_EXECUTE = "graphql:execute";
export declare const GRAPHQL_DC_CHANNEL_SUBSCRIBE = "graphql:subscribe";
export declare const GRAPHQL_DC_CHANNEL_RESOLVE = "graphql:resolve";
/** Context published on the sync-only `graphql:parse` channel. */
export interface GraphqlParseData {
    source: string | {
        body?: string;
    };
    result?: GraphqlDocumentNode;
    error?: unknown;
}
/** Context published on the sync-only `graphql:validate` channel. */
export interface GraphqlValidateData {
    document: GraphqlDocumentNode;
    /** Validation errors returned by validation; an empty array means the document is valid. */
    result?: ReadonlyArray<unknown>;
    error?: unknown;
}
/**
 * Context published on the `graphql:execute` and `graphql:subscribe` channels.
 *
 * `result` carries an `ExecutionResult` (or, for subscriptions, an async generator); GraphQL errors
 * collected during execution surface on `result.errors` rather than as the channel's `error`
 * lifecycle event, which only fires on an abrupt throw.
 */
export interface GraphqlOperationData {
    document: GraphqlDocumentNode;
    operationName?: string;
    operationType?: string;
    result?: unknown;
    error?: unknown;
}
/**
 * Context published on the per-field `graphql:resolve` channel.
 *
 * A resolver throw or rejection publishes the `error` lifecycle event here; the same failure also
 * surfaces in the enclosing execution result.
 */
export interface GraphqlResolveData {
    fieldName: string;
    parentType: string;
    fieldType: string;
    fieldPath: string;
    /** Whether the field is handled by graphql's default property resolver (vs. a user resolver). */
    isDefaultResolver: boolean;
    alias?: string;
    args?: unknown;
    result?: unknown;
    error?: unknown;
}
/** Options controlling which graphql channels the subscriber emits spans for. */
export interface GraphqlDiagnosticChannelsOptions {
    /**
     * Do not create spans for resolvers. Resolver spans are per-field and can be very high volume.
     * Defaults to `true`.
     */
    ignoreResolveSpans?: boolean;
    /**
     * When resolver spans are enabled, do not create them for graphql's default property resolver
     * (fields without a user-defined resolver), which are rarely interesting. Defaults to `true`.
     */
    ignoreTrivialResolveSpans?: boolean;
    /**
     * Rename the enclosing root span to include the operation name(s), e.g.
     * `GET /graphql` -> `GET /graphql (query GetUser)`. Defaults to `true`.
     */
    useOperationNameForRootSpan?: boolean;
}
/**
 * Platform-provided factory that creates a native tracing channel for the given name. The
 * subscriber binds the span and its lifecycle onto the channel via `bindTracingChannelToSpan`,
 * which propagates the active span through the runtime's async context.
 *
 * Node passes `node:diagnostics_channel`'s `tracingChannel` directly.
 */
export type GraphqlTracingChannelFactory = <T extends object>(name: string) => TracingChannel<T, T>;
/**
 * Subscribe Sentry span handlers to graphql's diagnostics-channel events
 * (`graphql:parse`, `:validate`, `:execute`, `:subscribe`), published by graphql >= 17.0.0.
 *
 * On older graphql versions the channels are never published to, so the subscribers are inert —
 * there is no double-instrumentation against the vendored OTel patcher, which is gated to `< 17`.
 *
 * The per-field `graphql:resolve` channel is only subscribed when `ignoreResolveSpans` is `false`:
 * resolver spans are per-field and can be extremely high-volume, so they are off by default (matching
 * the legacy OTel path). When enabled, `ignoreTrivialResolveSpans` (default `true`) additionally skips
 * graphql's default property resolver.
 */
export declare function subscribeGraphqlDiagnosticChannels(tracingChannel: GraphqlTracingChannelFactory, options?: GraphqlDiagnosticChannelsOptions): void;
//# sourceMappingURL=graphql-dc-subscriber.d.ts.map