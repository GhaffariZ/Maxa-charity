import type { OpenAiOptions } from '@sentry/core';
/**
 * EXPERIMENTAL — orchestrion-driven OpenAI integration. Subscribes to the `orchestrion:openai:*`
 * diagnostics_channels injected into `openai`'s `create` methods (chat completions, responses, embeddings,
 * conversations), so it requires the orchestrion runtime hook or bundler plugin.
 */
export declare const openaiChannelIntegration: (options?: OpenAiOptions | undefined) => import("@sentry/core").Integration & {
    name: "OpenAI";
};
//# sourceMappingURL=openai.d.ts.map