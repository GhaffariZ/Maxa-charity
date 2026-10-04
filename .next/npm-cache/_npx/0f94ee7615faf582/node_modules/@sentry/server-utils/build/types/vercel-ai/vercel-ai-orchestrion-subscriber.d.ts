import { type VercelAiChannelOptions, type VercelAiTracingChannelFactory } from './vercel-ai-dc-subscriber';
/**
 * Subscribe the v6 orchestrion channel adapter. Safe to always call: inert on
 * `ai` >= 7 (those channels are never published) and when orchestrion injection
 * isn't active. Idempotent.
 *
 * `tracingChannel` is the platform-provided factory (the same one passed to
 * `subscribeVercelAiTracingChannel`); `options` pins the recording settings at
 * subscribe time so we never look the integration up per event.
 */
export declare function subscribeVercelAiOrchestrionChannels(tracingChannel: VercelAiTracingChannelFactory, options?: VercelAiChannelOptions): void;
//# sourceMappingURL=vercel-ai-orchestrion-subscriber.d.ts.map