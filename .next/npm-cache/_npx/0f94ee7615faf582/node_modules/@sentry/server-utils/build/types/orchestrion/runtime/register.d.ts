export interface RegisterDiagnosticsChannelInjectionOptions {
    /**
     * @deprecated No longer used and ignored. The `@apm-js-collab/tracing-hooks` runtime is compiled
     * into this package's build, so there is no package location left to point the module hook at.
     */
    tracingHooksDir?: string;
}
/**
 * Synchronously register the diagnostics-channel injection module hooks.
 *
 * This is the single source of truth for the registration logic. It is used by:
 * - `Sentry.init()` (the Node SDK calls it directly — that's why this module
 *   must be CJS-compatible / dual-built, so it can be `require()`d synchronously
 *   before the app's `import`s resolve), and
 * - `import-hook.mjs`, the side-effecting `--import` entry, which just calls it.
 *
 * Libraries imported *after* this call publish the `tracingChannel` events that
 * the channel-based integrations subscribe to.
 */
export declare function registerDiagnosticsChannelInjection(_options?: RegisterDiagnosticsChannelInjectionOptions): void;
//# sourceMappingURL=register.d.ts.map