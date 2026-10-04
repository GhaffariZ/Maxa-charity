import type { Plugin } from 'esbuild';
import type { PluginOptions } from './options';
/**
 * esbuild plugin that runs the orchestrion code transform on the bundled output.
 *
 * Use when bundling a Node app with esbuild. For unbundled Node processes use the
 * runtime hook instead (`node --import @sentry/node/orchestrion app.js`).
 *
 * Instrumented packages marked as `external` never pass through the code
 * transform, so a build warning is emitted for them.
 *
 * @example
 * ```ts
 * // build.mjs
 * import { sentryOrchestrionPlugin } from '@sentry/server-utils/orchestrion/esbuild';
 * await esbuild.build({ plugins: [sentryOrchestrionPlugin()] });
 * ```
 */
export declare function sentryOrchestrionPlugin(options?: PluginOptions): Plugin;
//# sourceMappingURL=esbuild.d.ts.map