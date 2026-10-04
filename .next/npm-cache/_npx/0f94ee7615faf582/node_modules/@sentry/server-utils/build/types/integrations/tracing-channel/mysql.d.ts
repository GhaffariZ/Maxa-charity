/**
 * EXPERIMENTAL — orchestrion-driven mysql integration.
 *
 * Subscribes to the `orchestrion:mysql:query` diagnostics_channel that the
 * orchestrion code transform injects into `mysql/lib/Connection.js`'s
 * `Connection.prototype.query`. Requires the orchestrion runtime hook or
 * bundler plugin to be active — wire that up via `_experimentalSetupOrchestrion`.
 */
export declare const mysqlChannelIntegration: () => import("@sentry/core").Integration & {
    name: "Mysql";
};
//# sourceMappingURL=mysql.d.ts.map