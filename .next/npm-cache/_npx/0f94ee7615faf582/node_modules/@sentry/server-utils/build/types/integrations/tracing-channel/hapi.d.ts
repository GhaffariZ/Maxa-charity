/**
 * EXPERIMENTAL — orchestrion-driven hapi integration. Subscribes to the
 * `orchestrion:@hapi/hapi:route` / `:ext` channels injected into `@hapi/hapi`'s
 * `lib/server.js`. Requires the orchestrion runtime hook or bundler plugin.
 */
export declare const hapiChannelIntegration: () => import("@sentry/core").Integration & {
    name: "Hapi";
};
//# sourceMappingURL=hapi.d.ts.map