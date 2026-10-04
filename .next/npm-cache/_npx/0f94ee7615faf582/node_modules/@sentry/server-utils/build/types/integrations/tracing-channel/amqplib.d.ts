/**
 * EXPERIMENTAL: orchestrion-driven `amqplib` integration.
 *
 * Subscribes to the `orchestrion:amqplib:*` diagnostics_channels that the orchestrion code transform
 * injects into `amqplib`'s channel/connection methods. Requires the orchestrion runtime hook or
 * bundler plugin to be active.
 */
export declare const amqplibChannelIntegration: () => import("@sentry/core").Integration & {
    name: "Amqplib";
};
//# sourceMappingURL=amqplib.d.ts.map