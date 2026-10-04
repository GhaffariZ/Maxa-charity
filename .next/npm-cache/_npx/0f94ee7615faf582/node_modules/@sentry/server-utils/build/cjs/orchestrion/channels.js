Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const amqplib = require('./config/amqplib.js');
const anthropicAi = require('./config/anthropic-ai.js');
const awsSdk = require('./config/aws-sdk.js');
const dataloader = require('./config/dataloader.js');
const express = require('./config/express.js');
const firebase = require('./config/firebase.js');
const genericPool = require('./config/generic-pool.js');
const googleGenai = require('./config/google-genai.js');
const graphql = require('./config/graphql.js');
const hapi = require('./config/hapi.js');
const ioredis = require('./config/ioredis.js');
const kafkajs = require('./config/kafkajs.js');
const knex = require('./config/knex.js');
const koa = require('./config/koa.js');
const langchain = require('./config/langchain.js');
const langgraph = require('./config/langgraph.js');
const lruMemoizer = require('./config/lru-memoizer.js');
const mongodb = require('./config/mongodb.js');
const mongoose = require('./config/mongoose.js');
const mysql2 = require('./config/mysql2.js');
const mysql = require('./config/mysql.js');
const nestjs = require('./config/nestjs.js');
const openai = require('./config/openai.js');
const pg = require('./config/pg.js');
const postgres = require('./config/postgres.js');
const redis = require('./config/redis.js');
const remix = require('./config/remix.js');
const tedious = require('./config/tedious.js');
const vercelAi = require('./config/vercel-ai.js');

const CHANNELS = {
  ...amqplib.amqplibChannels,
  ...anthropicAi.anthropicAiChannels,
  ...awsSdk.awsSdkChannels,
  ...dataloader.dataloaderChannels,
  ...express.expressChannels,
  ...firebase.firebaseChannels,
  ...genericPool.genericPoolChannels,
  ...googleGenai.googleGenAiChannels,
  ...graphql.graphqlChannels,
  ...hapi.hapiChannels,
  ...ioredis.ioredisChannels,
  ...kafkajs.kafkajsChannels,
  ...knex.knexChannels,
  ...koa.koaChannels,
  ...langchain.langchainChannels,
  ...langgraph.langgraphChannels,
  ...lruMemoizer.lruMemoizerChannels,
  ...mongodb.mongodbChannels,
  ...mongoose.mongooseChannels,
  ...mysql2.mysql2Channels,
  ...mysql.mysqlChannels,
  ...nestjs.nestjsChannels,
  ...openai.openaiChannels,
  ...pg.pgChannels,
  ...postgres.postgresJsChannels,
  ...redis.redisChannels,
  ...remix.remixChannels,
  ...tedious.tediousChannels,
  ...vercelAi.vercelAiChannels
};

exports.CHANNELS = CHANNELS;
//# sourceMappingURL=channels.js.map
