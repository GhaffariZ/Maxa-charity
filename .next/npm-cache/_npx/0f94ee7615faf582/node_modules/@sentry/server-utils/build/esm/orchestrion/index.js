import { amqplibChannelIntegration } from '../integrations/tracing-channel/amqplib.js';
import { anthropicChannelIntegration } from '../integrations/tracing-channel/anthropic.js';
import { awsChannelIntegration } from '../integrations/tracing-channel/aws-sdk/index.js';
export { dataloaderChannelIntegration } from '../integrations/tracing-channel/dataloader.js';
import { genericPoolChannelIntegration } from '../integrations/tracing-channel/generic-pool.js';
import { googleGenAIChannelIntegration } from '../integrations/tracing-channel/google-genai.js';
import { graphqlDiagnosticsChannelIntegration } from '../integrations/tracing-channel/graphql/index.js';
export { graphqlChannelIntegration } from '../integrations/tracing-channel/graphql/index.js';
import { hapiChannelIntegration } from '../integrations/tracing-channel/hapi.js';
import { koaChannelIntegration } from '../integrations/tracing-channel/koa.js';
export { ioredisChannelIntegration } from '../integrations/tracing-channel/ioredis.js';
import { kafkajsChannelIntegration } from '../integrations/tracing-channel/kafkajs/index.js';
export { knexChannelIntegration } from '../integrations/tracing-channel/knex.js';
import { langChainChannelIntegration } from '../integrations/tracing-channel/langchain.js';
import { langGraphChannelIntegration } from '../integrations/tracing-channel/langgraph.js';
import { lruMemoizerChannelIntegration } from '../integrations/tracing-channel/lru-memoizer.js';
import { mongodbChannelIntegration } from '../integrations/tracing-channel/mongodb.js';
import { mongooseChannelIntegration } from '../integrations/tracing-channel/mongoose.js';
import { mysqlChannelIntegration } from '../integrations/tracing-channel/mysql.js';
import { mysql2ChannelIntegration } from '../integrations/tracing-channel/mysql2.js';
import { openaiChannelIntegration } from '../integrations/tracing-channel/openai.js';
import { postgresChannelIntegration } from '../integrations/tracing-channel/postgres.js';
import { postgresJsChannelIntegration } from '../integrations/tracing-channel/postgres-js.js';
import { tediousChannelIntegration } from '../integrations/tracing-channel/tedious.js';
import { vercelAiChannelIntegration } from '../integrations/tracing-channel/vercel-ai.js';
import { expressChannelIntegration } from '../integrations/tracing-channel/express/index.js';
import { firebaseChannelIntegration } from '../integrations/tracing-channel/firebase/index.js';
export { detectOrchestrionSetup, isOrchestrionInjected } from './detect.js';
export { registerOrchestrionChannelIntegration } from './registerChannelIntegration.js';
export { nestjsChannels } from './config/nestjs.js';
export { remixChannels } from './config/remix.js';
export { redisChannelIntegration } from '../integrations/tracing-channel/redis.js';

const channelIntegrations = {
  postgresIntegration: postgresChannelIntegration,
  postgresJsIntegration: postgresJsChannelIntegration,
  mongoIntegration: mongodbChannelIntegration,
  mysqlIntegration: mysqlChannelIntegration,
  mysql2Integration: mysql2ChannelIntegration,
  genericPoolIntegration: genericPoolChannelIntegration,
  mongooseIntegration: mongooseChannelIntegration,
  lruMemoizerIntegration: lruMemoizerChannelIntegration,
  openaiIntegration: openaiChannelIntegration,
  anthropicIntegration: anthropicChannelIntegration,
  googleGenAIIntegration: googleGenAIChannelIntegration,
  langChainIntegration: langChainChannelIntegration,
  langGraphIntegration: langGraphChannelIntegration,
  vercelAiIntegration: vercelAiChannelIntegration,
  amqplibIntegration: amqplibChannelIntegration,
  hapiIntegration: hapiChannelIntegration,
  koaIntegration: koaChannelIntegration,
  expressIntegration: expressChannelIntegration,
  graphqlIntegration: graphqlDiagnosticsChannelIntegration,
  kafkajsIntegration: kafkajsChannelIntegration,
  tediousIntegration: tediousChannelIntegration,
  awsIntegration: awsChannelIntegration,
  firebaseIntegration: firebaseChannelIntegration
};

export { amqplibChannelIntegration, anthropicChannelIntegration, awsChannelIntegration, channelIntegrations, expressChannelIntegration, firebaseChannelIntegration, genericPoolChannelIntegration, googleGenAIChannelIntegration, hapiChannelIntegration, kafkajsChannelIntegration, koaChannelIntegration, langChainChannelIntegration, langGraphChannelIntegration, lruMemoizerChannelIntegration, mongodbChannelIntegration, mongooseChannelIntegration, mysql2ChannelIntegration, mysqlChannelIntegration, openaiChannelIntegration, postgresChannelIntegration, postgresJsChannelIntegration, tediousChannelIntegration, vercelAiChannelIntegration };
//# sourceMappingURL=index.js.map
