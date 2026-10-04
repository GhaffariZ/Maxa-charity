import { type GraphqlDiagnosticChannelsOptions } from './graphql-dc-subscriber';
/**
 * Auto-instrument the [graphql](https://www.npmjs.com/package/graphql) library via its native
 * `node:diagnostics_channel` tracing channels (graphql >= 17).
 *
 * On older graphql versions the channels are never published to, so this integration is inert and
 * the vendored OTel instrumentation (gated to `< 17`) handles instrumentation instead.
 */
export declare const graphqlIntegration: (options?: GraphqlDiagnosticChannelsOptions | undefined) => import("@sentry/core").Integration & {
    name: "Graphql";
};
//# sourceMappingURL=index.d.ts.map