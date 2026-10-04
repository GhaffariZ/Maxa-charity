Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const CHANNEL_INTEGRATION_DEFINITIONS = [
  { exportName: "postgresChannelIntegration", modules: ["pg", "pg-pool"] },
  { exportName: "postgresJsChannelIntegration", modules: ["postgres"] },
  { exportName: "mysqlChannelIntegration", modules: ["mysql"] },
  { exportName: "mysql2ChannelIntegration", modules: ["mysql2"] },
  { exportName: "genericPoolChannelIntegration", modules: ["generic-pool"] },
  { exportName: "lruMemoizerChannelIntegration", modules: ["lru-memoizer"] },
  { exportName: "openaiChannelIntegration", modules: ["openai"] },
  { exportName: "anthropicChannelIntegration", modules: ["@anthropic-ai/sdk"] },
  { exportName: "googleGenAIChannelIntegration", modules: ["@google/genai"] },
  { exportName: "vercelAiChannelIntegration", modules: ["ai"] },
  { exportName: "amqplibChannelIntegration", modules: ["amqplib"] },
  { exportName: "hapiChannelIntegration", modules: ["@hapi/hapi"] },
  { exportName: "expressChannelIntegration", modules: ["express", "router"] },
  { exportName: "graphqlChannelIntegration", modules: ["graphql"] },
  { exportName: "kafkajsChannelIntegration", modules: ["kafkajs"] },
  { exportName: "redisChannelIntegration", modules: ["redis", "@redis/client"] },
  { exportName: "ioredisChannelIntegration", modules: ["ioredis"] },
  { exportName: "dataloaderChannelIntegration", modules: ["dataloader"] }
];
function subscriberExportForModule(moduleName) {
  return CHANNEL_INTEGRATION_DEFINITIONS.find((d) => d.modules.includes(moduleName))?.exportName;
}

exports.CHANNEL_INTEGRATION_DEFINITIONS = CHANNEL_INTEGRATION_DEFINITIONS;
exports.subscriberExportForModule = subscriberExportForModule;
//# sourceMappingURL=channel-integration-definitions.js.map
