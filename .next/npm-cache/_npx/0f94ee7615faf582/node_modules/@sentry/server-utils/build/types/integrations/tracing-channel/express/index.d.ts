import type { ExpressIntegrationOptions } from './types';
/**
 * EXPERIMENTAL — orchestrion-driven Express integration.
 *
 * Subscribes to the `orchestrion:express:handle` (Express v4) and
 * `orchestrion:router:handle` (Express v5, via the `router` package)
 * diagnostics_channels that the orchestrion code transform injects into the
 * routing layer's request handler (`Layer.prototype.handle_request` /
 * `handleRequest`). One span is opened per layer invocation — producing the
 * same spans as the OTel Express instrumentation.
 *
 * Requires the orchestrion runtime hook or bundler plugin to be active — wire
 * that up via `experimentalUseDiagnosticsChannelInjection()`.
 */
export declare const expressChannelIntegration: (options?: ExpressIntegrationOptions | undefined) => import("@sentry/core").Integration & {
    name: "Express";
};
//# sourceMappingURL=index.d.ts.map