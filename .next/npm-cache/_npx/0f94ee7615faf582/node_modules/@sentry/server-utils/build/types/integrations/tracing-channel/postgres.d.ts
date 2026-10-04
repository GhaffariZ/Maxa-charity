/**
 * EXPERIMENTAL: orchestrion-driven `pg` (node-postgres) integration.
 *
 * Subscribes to the `orchestrion:pg:query`/`:connect` and
 * `orchestrion:pg-pool:connect` diagnostics_channels that the orchestrion code
 * transform injects into `pg`'s `Client.prototype.query`/`connect`
 * and `pg-pool`'s `Pool.prototype.connect`. Requires the orchestrion runtime
 * hook or bundler plugin to be active.
 */
export declare const postgresChannelIntegration: (options?: {
    ignoreConnectSpans?: boolean;
} | undefined) => import("@sentry/core").Integration & {
    name: "Postgres";
};
//# sourceMappingURL=postgres.d.ts.map