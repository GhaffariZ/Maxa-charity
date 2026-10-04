import type { GraphqlDiagnosticChannelsOptions } from '../../../graphql/graphql-dc-subscriber';
/**
 * EXPERIMENTAL — orchestrion-driven graphql integration for graphql v14–16 (v17 publishes native
 * `diagnostics_channel` events handled by `@sentry/server-utils`'s graphql integration instead).
 *
 * Subscribes to the `orchestrion:graphql:{parse,validate,execute}` channels the orchestrion code
 * transform injects into `graphql`'s `language/parser.js`, `validation/validate.js` and
 * `execution/execute.js`, emitting spans identical to the native path. Requires the orchestrion
 * runtime hook or bundler plugin — wire it up via `experimentalUseDiagnosticsChannelInjection()`.
 *
 * @experimental
 */
export declare const graphqlChannelIntegration: (options?: GraphqlDiagnosticChannelsOptions | undefined) => import("@sentry/core").Integration & {
    name: "Graphql";
};
/**
 * The complete graphql diagnostics-channel integration: the native subscriber (graphql v17) composed
 * with the orchestrion subscriber (v14–16), so opting into injection instruments every supported
 * version via diagnostics channels without the OTel patcher. Reuses the OTel `Graphql` name so
 * enabling injection swaps this in for it.
 */
export declare const graphqlDiagnosticsChannelIntegration: (options?: GraphqlDiagnosticChannelsOptions) => Omit<import("@sentry/core").Integration & {
    name: "Graphql";
}, "name" | "setupOnce"> & {
    name: "Graphql";
    setupOnce: () => void | undefined;
};
//# sourceMappingURL=index.d.ts.map