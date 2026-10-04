/**
 * EXPERIMENTAL - orchestrion-driven knex integration.
 *
 * Subscribes to the `orchestrion:knex:*` diagnostics_channels that the orchestrion code transform
 * injects into knex's `Runner.query` (span) and `Client.queryBuilder`/`schemaBuilder`/`raw` (parent-span
 * bookkeeping). Requires the orchestrion runtime hook or bundler plugin to be active.
 */
export declare const knexChannelIntegration: () => import("@sentry/core").Integration & {
    name: "Knex";
};
//# sourceMappingURL=knex.d.ts.map