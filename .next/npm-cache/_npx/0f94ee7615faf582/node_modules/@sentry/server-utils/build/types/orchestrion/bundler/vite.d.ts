import type { Plugin } from 'vite';
import type { PluginOptions } from './options';
/**
 * Vite plugin that runs the orchestrion code transform on the bundled output.
 *
 * Use when bundling a Node app with Vite (e.g. Vite SSR builds, Nuxt's Nitro
 * pipeline, SvelteKit). For unbundled Node processes use the runtime hook
 * instead (`node --import @sentry/node/orchestrion app.js`).
 *
 * @example
 * ```ts
 * // vite.config.ts
 * import { sentryOrchestrionPlugin } from '@sentry/server-utils/orchestrion/vite';
 * export default { plugins: [sentryOrchestrionPlugin()] };
 * ```
 */
export declare function sentryOrchestrionPlugin(options?: PluginOptions): Plugin;
//# sourceMappingURL=vite.d.ts.map