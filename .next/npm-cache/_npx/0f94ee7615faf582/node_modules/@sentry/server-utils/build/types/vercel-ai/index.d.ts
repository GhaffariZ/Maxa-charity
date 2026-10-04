export interface VercelAiOptions {
    /**
     * Enable or disable input recording. Enabled if `dataCollection.genAI.inputs` (or the deprecated `sendDefaultPii` option) is `true`
     * or if you set `isEnabled` to `true` in your ai SDK method telemetry settings.
     * Integration-level options take precedence over global `dataCollection` config.
     */
    recordInputs?: boolean;
    /**
     * Enable or disable output recording. Enabled if `dataCollection.genAI.outputs` (or the deprecated `sendDefaultPii` option) is `true`
     * or if you set `isEnabled` to `true` in your ai SDK method telemetry settings.
     * Integration-level options take precedence over global `dataCollection` config.
     */
    recordOutputs?: boolean;
    /**
     * Enable or disable truncation of recorded input messages.
     * Defaults to `true`.
     */
    enableTruncation?: boolean;
}
/**
 * Auto-instrument the `ai` SDK's native telemetry tracing channel (ai >= 7).
 */
export declare const vercelAiIntegration: (options?: VercelAiOptions | undefined) => import("@sentry/core").Integration & {
    name: "VercelAI";
};
//# sourceMappingURL=index.d.ts.map