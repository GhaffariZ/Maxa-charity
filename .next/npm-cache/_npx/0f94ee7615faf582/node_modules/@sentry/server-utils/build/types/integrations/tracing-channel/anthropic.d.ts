import type { AnthropicAiOptions } from '@sentry/core';
/**
 * EXPERIMENTAL — orchestrion-driven Anthropic integration. Subscribes to the `orchestrion:@anthropic-ai/sdk:*`
 * diagnostics_channels injected into the SDK's chat (`messages`/`completions`/beta `messages`), `models`, and
 * `messages.stream()` methods, so it requires the orchestrion runtime hook or bundler plugin.
 */
export declare const anthropicChannelIntegration: (options?: AnthropicAiOptions | undefined) => import("@sentry/core").Integration & {
    name: "Anthropic_AI";
};
//# sourceMappingURL=anthropic.d.ts.map