/**
 * Set up the Fastify (>= 3.21.0 < 6) instrumentation by subscribing to the `fastify.initialization`
 * diagnostics channel and registering the span-creating plugin on every Fastify instance.
 *
 * Idempotent and exposes an `id` so it can participate in the OpenTelemetry preload list.
 */
export declare const instrumentFastify: (() => void) & {
    id: string;
};
//# sourceMappingURL=instrumentation.d.ts.map