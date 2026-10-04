import { toSubscribeInjections } from './subscribe-injection.js';

const chatModelConfig = ["dist/language_models/chat_models.cjs", "dist/language_models/chat_models.js"].flatMap(
  (filePath) => {
    const module = { name: "@langchain/core", versionRange: ">=0.1.0 <2.0.0", filePath };
    return [
      {
        channelName: "chatModelInvoke",
        module,
        functionQuery: { className: "BaseChatModel", methodName: "invoke", kind: "Async" }
      },
      {
        channelName: "chatModelStream",
        module,
        functionQuery: { className: "BaseChatModel", methodName: "_streamIterator", kind: "Async" }
      }
    ];
  }
);
const EMBED_QUERY = "embedQuery";
const EMBED_DOCUMENTS = "embedDocuments";
const EMBEDDINGS_PROVIDERS = [
  { name: "@langchain/openai", versionRange: ">=0.1.0 <2.0.0", methods: [EMBED_QUERY, EMBED_DOCUMENTS] },
  { name: "@langchain/google-genai", versionRange: ">=0.1.0 <3.0.0", methods: [EMBED_QUERY, EMBED_DOCUMENTS] },
  { name: "@langchain/mistralai", versionRange: ">=0.1.0 <2.0.0", methods: [EMBED_QUERY, EMBED_DOCUMENTS] },
  // `@langchain/google-vertexai` inherits its embed methods from this shared base. The base's
  // `embedQuery` delegates to `embedDocuments`, so hooking only `embedDocuments` still traces both
  // entry points as a single span each, instead of emitting a nested duplicate for `embedQuery`.
  { name: "@langchain/google-common", versionRange: ">=0.1.0 <3.0.0", methods: [EMBED_DOCUMENTS] }
];
const embeddingsConfig = EMBEDDINGS_PROVIDERS.flatMap(
  ({ name, versionRange, methods }) => ["dist/embeddings.cjs", "dist/embeddings.js"].flatMap(
    (filePath) => methods.map((method) => ({
      channelName: method,
      module: { name, versionRange, filePath },
      functionQuery: { methodName: method, kind: "Async" }
    }))
  )
);
const langchainConfig = [...chatModelConfig, ...embeddingsConfig];
const langchainEmbeddingsChannels = EMBEDDINGS_PROVIDERS.flatMap(
  ({ name, methods }) => methods.map((method) => `orchestrion:${name}:${method}`)
);
const langchainChannels = {
  LANGCHAIN_CHAT_MODEL_INVOKE: "orchestrion:@langchain/core:chatModelInvoke",
  LANGCHAIN_CHAT_MODEL_STREAM: "orchestrion:@langchain/core:chatModelStream"
};
const langchainSubscribeInjection = toSubscribeInjections(langchainConfig);

export { langchainChannels, langchainConfig, langchainEmbeddingsChannels, langchainSubscribeInjection };
//# sourceMappingURL=langchain.js.map
