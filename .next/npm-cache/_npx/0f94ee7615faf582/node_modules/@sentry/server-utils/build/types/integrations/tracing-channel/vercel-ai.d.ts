/**
 * Auto-instrument the `ai` SDK. Supported are:
 * - v7 via native `ai:telemetry` tracing channel
 * - v4, v5 & v6 via orchestrion `orchestrion:ai:*` channels
 */
export declare const vercelAiChannelIntegration: (options?: import("../..").VercelAiOptions | undefined) => import("@sentry/core").Integration & {
    name: "VercelAI";
};
//# sourceMappingURL=vercel-ai.d.ts.map