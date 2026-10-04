/**
 * EXPERIMENTAL — orchestrion-driven generic-pool integration. Subscribes to
 * `orchestrion:generic-pool:acquire` (injected into `generic-pool/lib/Pool.js`'s
 * `Pool.prototype.acquire`). Creates a `generic-pool.acquire` span for each
 * acquisition. Requires the orchestrion runtime hook or bundler plugin.
 */
export declare const genericPoolChannelIntegration: () => import("@sentry/core").Integration & {
    name: "GenericPool";
};
//# sourceMappingURL=generic-pool.d.ts.map