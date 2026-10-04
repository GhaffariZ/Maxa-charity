import type { GoogleGenAIOptions } from '@sentry/core';
/**
 * EXPERIMENTAL — orchestrion-driven Google GenAI integration. Subscribes to the
 * `orchestrion:@google/genai:*` diagnostics_channels injected into the SDK's `Models`
 * (`generateContent`/`generateContentStream`/`embedContent`) and `Chat`
 * (`sendMessage`/`sendMessageStream`) methods, so it requires the orchestrion runtime hook or
 * bundler plugin.
 */
export declare const googleGenAIChannelIntegration: (options?: GoogleGenAIOptions | undefined) => import("@sentry/core").Integration & {
    name: "Google_GenAI";
};
//# sourceMappingURL=google-genai.d.ts.map