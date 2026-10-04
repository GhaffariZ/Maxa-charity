/**
 * Whether orchestrion has injected the diagnostics channels into this process,
 * either by the runtime `--import` hook / init-time registration (`runtime`)
 * or a bundler plugin (`bundler`). Both injectors set a flag on the
 * `globalThis.__SENTRY_ORCHESTRION__` marker.
 *
 * Use this to avoid wiring up channel-subscriber integrations when nothing
 * will ever publish to those channels.
 */
export declare function isOrchestrionInjected(): boolean;
/**
 * Verifies that the diagnostics channels have been injected either by the
 * runtime `--import` hook (or init-time registration), a bundler plugin, or
 * both, and warns if not. When at least one injector is active, logs for each
 * mechanism whether it hooked (a defined array, even empty, means it did) and
 * which libraries it injected.
 *
 * Both injectors being active at once is fine: they operate on disjoint module
 * sets (a module is either loaded through Node's loader and transformed by the
 * runtime hook, or inlined by the bundler and transformed by the plugin), so
 * a single module can't be double-wrapped. A hybrid setup, with some deps
 * external and runtime-instrumented, others bundled and plugin-instrumented,
 * is fine.
 */
export declare function detectOrchestrionSetup(): void;
//# sourceMappingURL=detect.d.ts.map