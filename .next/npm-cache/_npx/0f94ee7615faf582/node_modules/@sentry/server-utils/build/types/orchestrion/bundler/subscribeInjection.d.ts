import type { PluginOptions } from './options';
/**
 * The `instrumentations` + `customTransforms` a bundler plugin passes to
 * {@link orchestrionTransformOptions} to enable the marker-push subscribe
 * injection used by bundler-only SDKs (e.g. `@sentry/cloudflare`).
 *
 * The `SUBSCRIBE_INJECTIONS` configs ride alongside the real channel-publishing
 * configs, and `injectSubscribe` runs on each matched module, so every
 * transformed package self-registers its subscriber on the global marker
 * without a runtime module hook.
 */
export declare function subscribeInjectionOptions(): Pick<PluginOptions, 'instrumentations' | 'customTransforms'>;
//# sourceMappingURL=subscribeInjection.d.ts.map