Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const subscribeInjection = require('./subscribe-injection.js');

const NODE_DIST_FILES = ["dist/node/index.js", "dist/node/index.mjs", "dist/node/index.cjs"];
const googleGenAiConfig = [
  // `generateContent`/`generateContentStream` are arrow properties assigned in the constructor, not class
  // methods, so they need `expressionName` rather than `className`/`methodName`.
  ...NODE_DIST_FILES.flatMap(
    (filePath) => ["generateContent", "generateContentStream"].map((expressionName) => ({
      channelName: "generate-content",
      module: { name: "@google/genai", versionRange: ">=0.10.0 <2", filePath },
      functionQuery: { expressionName, kind: "Auto" }
    }))
  ),
  // `embedContent` and the `Chat` methods are real class methods.
  ...NODE_DIST_FILES.map((filePath) => ({
    channelName: "embed-content",
    module: { name: "@google/genai", versionRange: ">=0.10.0 <2", filePath },
    functionQuery: { className: "Models", methodName: "embedContent", kind: "Auto" }
  })),
  // `sendMessage`/`sendMessageStream` internally delegate to `Models.generateContent(Stream)`; the
  // subscriber suppresses that nested `generate-content` event so a chat call yields a single span.
  ...NODE_DIST_FILES.flatMap(
    (filePath) => ["sendMessage", "sendMessageStream"].map((methodName) => ({
      channelName: "chat",
      module: { name: "@google/genai", versionRange: ">=0.10.0 <2", filePath },
      functionQuery: { className: "Chat", methodName, kind: "Auto" }
    }))
  )
];
const googleGenAiChannels = {
  GOOGLE_GENAI_GENERATE_CONTENT: "orchestrion:@google/genai:generate-content",
  GOOGLE_GENAI_EMBED_CONTENT: "orchestrion:@google/genai:embed-content",
  GOOGLE_GENAI_CHAT: "orchestrion:@google/genai:chat"
};
const googleGenAiSubscribeInjection = subscribeInjection.toSubscribeInjections(googleGenAiConfig);

exports.googleGenAiChannels = googleGenAiChannels;
exports.googleGenAiConfig = googleGenAiConfig;
exports.googleGenAiSubscribeInjection = googleGenAiSubscribeInjection;
//# sourceMappingURL=google-genai.js.map
