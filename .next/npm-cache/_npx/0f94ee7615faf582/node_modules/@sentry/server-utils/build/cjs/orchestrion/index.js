Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const amqplib = require('../integrations/tracing-channel/amqplib.js');
const anthropic = require('../integrations/tracing-channel/anthropic.js');
const index = require('../integrations/tracing-channel/aws-sdk/index.js');
const dataloader = require('../integrations/tracing-channel/dataloader.js');
const genericPool = require('../integrations/tracing-channel/generic-pool.js');
const googleGenai = require('../integrations/tracing-channel/google-genai.js');
const index$3 = require('../integrations/tracing-channel/graphql/index.js');
const hapi = require('../integrations/tracing-channel/hapi.js');
const koa = require('../integrations/tracing-channel/koa.js');
const ioredis = require('../integrations/tracing-channel/ioredis.js');
const index$4 = require('../integrations/tracing-channel/kafkajs/index.js');
const knex = require('../integrations/tracing-channel/knex.js');
const langchain = require('../integrations/tracing-channel/langchain.js');
const langgraph = require('../integrations/tracing-channel/langgraph.js');
const lruMemoizer = require('../integrations/tracing-channel/lru-memoizer.js');
const mongodb = require('../integrations/tracing-channel/mongodb.js');
const mongoose = require('../integrations/tracing-channel/mongoose.js');
const mysql = require('../integrations/tracing-channel/mysql.js');
const mysql2 = require('../integrations/tracing-channel/mysql2.js');
const openai = require('../integrations/tracing-channel/openai.js');
const postgres = require('../integrations/tracing-channel/postgres.js');
const postgresJs = require('../integrations/tracing-channel/postgres-js.js');
const tedious = require('../integrations/tracing-channel/tedious.js');
const vercelAi = require('../integrations/tracing-channel/vercel-ai.js');
const index$1 = require('../integrations/tracing-channel/express/index.js');
const index$2 = require('../integrations/tracing-channel/firebase/index.js');
const detect = require('./detect.js');
const registerChannelIntegration = require('./registerChannelIntegration.js');
const nestjs = require('./config/nestjs.js');
const remix = require('./config/remix.js');
const redis = require('../integrations/tracing-channel/redis.js');

const channelIntegrations = {
  postgresIntegration: postgres.postgresChannelIntegration,
  postgresJsIntegration: postgresJs.postgresJsChannelIntegration,
  mongoIntegration: mongodb.mongodbChannelIntegration,
  mysqlIntegration: mysql.mysqlChannelIntegration,
  mysql2Integration: mysql2.mysql2ChannelIntegration,
  genericPoolIntegration: genericPool.genericPoolChannelIntegration,
  mongooseIntegration: mongoose.mongooseChannelIntegration,
  lruMemoizerIntegration: lruMemoizer.lruMemoizerChannelIntegration,
  openaiIntegration: openai.openaiChannelIntegration,
  anthropicIntegration: anthropic.anthropicChannelIntegration,
  googleGenAIIntegration: googleGenai.googleGenAIChannelIntegration,
  langChainIntegration: langchain.langChainChannelIntegration,
  langGraphIntegration: langgraph.langGraphChannelIntegration,
  vercelAiIntegration: vercelAi.vercelAiChannelIntegration,
  amqplibIntegration: amqplib.amqplibChannelIntegration,
  hapiIntegration: hapi.hapiChannelIntegration,
  koaIntegration: koa.koaChannelIntegration,
  expressIntegration: index$1.expressChannelIntegration,
  graphqlIntegration: index$3.graphqlDiagnosticsChannelIntegration,
  kafkajsIntegration: index$4.kafkajsChannelIntegration,
  tediousIntegration: tedious.tediousChannelIntegration,
  awsIntegration: index.awsChannelIntegration,
  firebaseIntegration: index$2.firebaseChannelIntegration
};

exports.amqplibChannelIntegration = amqplib.amqplibChannelIntegration;
exports.anthropicChannelIntegration = anthropic.anthropicChannelIntegration;
exports.awsChannelIntegration = index.awsChannelIntegration;
exports.dataloaderChannelIntegration = dataloader.dataloaderChannelIntegration;
exports.genericPoolChannelIntegration = genericPool.genericPoolChannelIntegration;
exports.googleGenAIChannelIntegration = googleGenai.googleGenAIChannelIntegration;
exports.graphqlChannelIntegration = index$3.graphqlChannelIntegration;
exports.hapiChannelIntegration = hapi.hapiChannelIntegration;
exports.koaChannelIntegration = koa.koaChannelIntegration;
exports.ioredisChannelIntegration = ioredis.ioredisChannelIntegration;
exports.kafkajsChannelIntegration = index$4.kafkajsChannelIntegration;
exports.knexChannelIntegration = knex.knexChannelIntegration;
exports.langChainChannelIntegration = langchain.langChainChannelIntegration;
exports.langGraphChannelIntegration = langgraph.langGraphChannelIntegration;
exports.lruMemoizerChannelIntegration = lruMemoizer.lruMemoizerChannelIntegration;
exports.mongodbChannelIntegration = mongodb.mongodbChannelIntegration;
exports.mongooseChannelIntegration = mongoose.mongooseChannelIntegration;
exports.mysqlChannelIntegration = mysql.mysqlChannelIntegration;
exports.mysql2ChannelIntegration = mysql2.mysql2ChannelIntegration;
exports.openaiChannelIntegration = openai.openaiChannelIntegration;
exports.postgresChannelIntegration = postgres.postgresChannelIntegration;
exports.postgresJsChannelIntegration = postgresJs.postgresJsChannelIntegration;
exports.tediousChannelIntegration = tedious.tediousChannelIntegration;
exports.vercelAiChannelIntegration = vercelAi.vercelAiChannelIntegration;
exports.expressChannelIntegration = index$1.expressChannelIntegration;
exports.firebaseChannelIntegration = index$2.firebaseChannelIntegration;
exports.detectOrchestrionSetup = detect.detectOrchestrionSetup;
exports.isOrchestrionInjected = detect.isOrchestrionInjected;
exports.registerOrchestrionChannelIntegration = registerChannelIntegration.registerOrchestrionChannelIntegration;
exports.nestjsChannels = nestjs.nestjsChannels;
exports.remixChannels = remix.remixChannels;
exports.redisChannelIntegration = redis.redisChannelIntegration;
exports.channelIntegrations = channelIntegrations;
//# sourceMappingURL=index.js.map
