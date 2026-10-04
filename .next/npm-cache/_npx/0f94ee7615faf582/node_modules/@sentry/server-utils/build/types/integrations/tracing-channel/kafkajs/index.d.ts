/**
 * EXPERIMENTAL — orchestrion-driven kafkajs integration.
 *
 * Subscribes to the `orchestrion:kafkajs:*` diagnostics_channels that the orchestrion code transform
 * injects into `kafkajs`'s `producer/messageProducer.js` (`sendBatch`) and `consumer/index.js` (`run`).
 * Requires the orchestrion runtime hook or bundler plugin to be active — wire that up via
 * `experimentalUseDiagnosticsChannelInjection`.
 *
 * Known limitation vs. the OTel integration it replaces: the wrapping producer-`transaction` span is
 * not emitted (the transformer can't replace `transaction()`'s return value to patch commit/abort).
 * Transactional `send`/`sendBatch` calls still produce producer spans, since they route through the
 * same instrumented `sendBatch`.
 */
export declare const kafkajsChannelIntegration: () => import("@sentry/core").Integration & {
    name: "Kafka";
};
//# sourceMappingURL=index.d.ts.map