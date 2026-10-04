import { amqplibChannels } from './config/amqplib.js';
import { anthropicAiChannels } from './config/anthropic-ai.js';
import { awsSdkChannels } from './config/aws-sdk.js';
import { dataloaderChannels } from './config/dataloader.js';
import { expressChannels } from './config/express.js';
import { firebaseChannels } from './config/firebase.js';
import { genericPoolChannels } from './config/generic-pool.js';
import { googleGenAiChannels } from './config/google-genai.js';
import { graphqlChannels } from './config/graphql.js';
import { hapiChannels } from './config/hapi.js';
import { ioredisChannels } from './config/ioredis.js';
import { kafkajsChannels } from './config/kafkajs.js';
import { knexChannels } from './config/knex.js';
import { koaChannels } from './config/koa.js';
import { langchainChannels } from './config/langchain.js';
import { langgraphChannels } from './config/langgraph.js';
import { lruMemoizerChannels } from './config/lru-memoizer.js';
import { mongodbChannels } from './config/mongodb.js';
import { mongooseChannels } from './config/mongoose.js';
import { mysql2Channels } from './config/mysql2.js';
import { mysqlChannels } from './config/mysql.js';
import { nestjsChannels } from './config/nestjs.js';
import { openaiChannels } from './config/openai.js';
import { pgChannels } from './config/pg.js';
import { postgresJsChannels } from './config/postgres.js';
import { redisChannels } from './config/redis.js';
import { remixChannels } from './config/remix.js';
import { tediousChannels } from './config/tedious.js';
import { vercelAiChannels } from './config/vercel-ai.js';

const CHANNELS = {
  ...amqplibChannels,
  ...anthropicAiChannels,
  ...awsSdkChannels,
  ...dataloaderChannels,
  ...expressChannels,
  ...firebaseChannels,
  ...genericPoolChannels,
  ...googleGenAiChannels,
  ...graphqlChannels,
  ...hapiChannels,
  ...ioredisChannels,
  ...kafkajsChannels,
  ...knexChannels,
  ...koaChannels,
  ...langchainChannels,
  ...langgraphChannels,
  ...lruMemoizerChannels,
  ...mongodbChannels,
  ...mongooseChannels,
  ...mysql2Channels,
  ...mysqlChannels,
  ...nestjsChannels,
  ...openaiChannels,
  ...pgChannels,
  ...postgresJsChannels,
  ...redisChannels,
  ...remixChannels,
  ...tediousChannels,
  ...vercelAiChannels
};

export { CHANNELS };
//# sourceMappingURL=channels.js.map
