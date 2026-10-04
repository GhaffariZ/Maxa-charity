import type { Client } from '../../client';
/**
 * Post-process spans emitted by the Vercel AI SDK.
 */
/**
 * Rename and normalize Vercel AI SDK attributes to OpenTelemetry semantic conventions.
 * This is the shared attribute processing logic used by both the legacy event processor
 * path (SpanJSON) and the streamed span path (StreamedSpanJSON).
 */
export declare function processVercelAiSpanAttributes(attributes: Record<string, unknown>): void;
/**
 * Add event processors to the given client to process Vercel AI spans.
 *
 * Idempotent: both the integration and the Next.js SDK register these, and duplicate hooks would
 * process every span twice.
 */
export declare function addVercelAiProcessors(client: Client): void;
/**
 * Derive the `gen_ai.usage.*` cache/reasoning/prediction token attributes and `gen_ai.conversation.id`
 * from an AI SDK `providerMetadata` object.
 *
 * Shared between the OTel processor (which parses `providerMetadata` from a serialized span attribute)
 * and the `ai` >= 7 tracing-channel subscriber (which receives it as an object on the channel result),
 * so both paths emit an identical shape. Pass the already-parsed object; unknown/empty input yields `{}`.
 */
export declare function getProviderMetadataAttributes(providerMetadata: unknown): Record<string, number | string>;
/**
 * Usage attributes that `getProviderMetadataAttributes` derives from `providerMetadata`, which
 * describes only the last step of a call. They must not be written onto a span that reports usage
 * aggregated across steps (`gen_ai.invoke_agent`), where they would replace the aggregate with one
 * step's figures. Exported so the channel/orchestrion subscribers, which call
 * `getProviderMetadataAttributes` directly rather than through `addProviderMetadataToAttributes`,
 * apply the same rule.
 */
export declare const LAST_STEP_ONLY_USAGE_KEYS: Set<string>;
//# sourceMappingURL=index.d.ts.map