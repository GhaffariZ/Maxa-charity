import type { Integration } from '@sentry/core';
/**
 * The orchestrion-driven pieces, resolved lazily by the opt-in loader.
 *
 * IMPORTANT: this module (and everything `init()` imports) must NOT reference
 * the orchestrion code (`@sentry/server-utils/orchestrion/*`). The only
 * reference lives inside `experimentalUseDiagnosticsChannelInjection()` (a
 * separate module, reachable solely through that public export). That's the
 * tree-shaking boundary: if an app never calls the opt-in function, then a
 * bundler drops the entire orchestrion subtree, including its transitive
 * dependencies, while an app that does call it gets it bundled
 * normally.
 */
export interface DiagnosticsChannelInjection {
    /** Channel-based integrations to register, replacing their OTel equivalents. */
    integrations: Integration[] | readonly Integration[];
    /** OTel integration names these replace; filtered out of the default set. */
    replacedOtelIntegrationNames: string[];
    /** Installs the module hooks that inject the diagnostics channels. */
    register: () => void;
    /** Warns (DEBUG only) about missing or doubled channel injection. */
    detect: () => void;
}
/**
 * Set by `experimentalUseDiagnosticsChannelInjection()`. The loader
 * is the only thing that pulls in the orchestrion modules; see
 * {@link DiagnosticsChannelInjection) re tree-shaking concerns this addresses.
 *
 * @internal
 */
export declare function setDiagnosticsChannelInjectionLoader(load: () => DiagnosticsChannelInjection): void;
/** Whether `experimentalUseDiagnosticsChannelInjection()` was called. */
export declare function isDiagnosticsChannelInjectionEnabled(): boolean;
/**
 * Resolve and memoize the orchestrion pieces. This is what actually loads
 * the orchestrion modules. Returns `undefined` if the app never opted in.
 * Callers gate this on span recording, so the modules load only when both
 * opted in and tracing is enabled.
 */
export declare function resolveDiagnosticsChannelInjection(): DiagnosticsChannelInjection | undefined;
//# sourceMappingURL=diagnosticsChannelInjection.d.ts.map