/**
 * EXPERIMENTAL: orchestrion-driven `dataloader` integration.
 *
 * Subscribes to the `orchestrion:dataloader:*` diagnostics_channels that the orchestrion code
 * transform injects into `dataloader`'s constructor and prototype methods. Requires the orchestrion
 * runtime hook or bundler plugin to be active.
 */
export declare const dataloaderChannelIntegration: () => import("@sentry/core").Integration & {
    name: "Dataloader";
};
//# sourceMappingURL=dataloader.d.ts.map