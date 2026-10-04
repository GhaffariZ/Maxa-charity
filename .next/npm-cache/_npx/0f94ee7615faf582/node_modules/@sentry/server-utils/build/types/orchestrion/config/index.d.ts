import type { InstrumentationConfig } from '..';
/**
 * The orchestrion code-transform configs. Every instrumentable library is here
 * so the transform is all-or-nothing: whenever orchestrion is enabled, all of
 * these are injected. The channel LISTENERS may live elsewhere (e.g. the NestJS
 * one lives in `@sentry/nestjs`), but the config that decides what gets
 * transformed is centralized here.
 */
export declare const SENTRY_INSTRUMENTATIONS: InstrumentationConfig[];
/**
 * The `Program`-matching injection configs that make each instrumented file
 * self-register its channel subscriber at load (used by bundler-only SDKs like
 * `@sentry/cloudflare`).
 *
 * Deliberately separate from `SENTRY_INSTRUMENTATIONS`: these reference a custom
 * transform that only the opted-in bundler plugin registers, so feeding them to
 * the runtime `--import` hook (which can't register it) would make the
 * code-transformer drop the whole file. Each library owns its own
 * `*SubscribeInjection` (derived from its channel configs), collected here.
 */
export declare const SUBSCRIBE_INJECTIONS: InstrumentationConfig[];
/**
 * The unique set of package names instrumented by `SENTRY_INSTRUMENTATIONS`
 * merged with any caller-provided `instrumentations` (e.g. `['mysql']`).
 *
 * Bundler plugins MUST ensure these are actually bundled rather than
 * externalized: an externalized dependency is resolved from `node_modules` at
 * runtime and never passes through the code transform's `onLoad`, so its
 * diagnostics_channel calls are silently never injected. This includes a
 * plugin's custom `instrumentations`, otherwise those extra packages can stay
 * externalized and their transform never runs.
 */
export declare function instrumentedModuleNames(instrumentations?: InstrumentationConfig[]): string[];
/** The instrumented module names from the default Sentry config, with no custom additions. */
export declare const INSTRUMENTED_MODULE_NAMES: string[];
/**
 * Returns `external` with any instrumented packages removed, so a bundler that
 * uses an "external" denylist (esbuild, Bun, Rollup) still bundles — and thus
 * transforms — them. Matches an exact package name (`'mysql'`) or a subpath
 * (`'mysql/lib/...'`); wildcard/other patterns are left untouched. `undefined`
 * is returned unchanged.
 *
 * Pass `moduleNames` from `instrumentedModuleNames(options.instrumentations)` so
 * custom instrumentations are stripped too; it defaults to the Sentry config
 * list. (Vite uses an `ssr.noExternal` allowlist instead, so it consumes
 * `instrumentedModuleNames` directly rather than this helper.)
 */
export declare function withoutInstrumentedExternals(external: readonly string[] | undefined, moduleNames?: string[]): string[] | undefined;
//# sourceMappingURL=index.d.ts.map