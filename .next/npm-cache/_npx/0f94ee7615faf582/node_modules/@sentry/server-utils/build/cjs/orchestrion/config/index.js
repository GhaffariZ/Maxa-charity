Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const awsSdk = require('./aws-sdk.js');
const amqplib = require('./amqplib.js');
const anthropicAi = require('./anthropic-ai.js');
const dataloader = require('./dataloader.js');
const express = require('./express.js');
const firebase = require('./firebase.js');
const genericPool = require('./generic-pool.js');
const googleGenai = require('./google-genai.js');
const graphql = require('./graphql.js');
const hapi = require('./hapi.js');
const ioredis = require('./ioredis.js');
const kafkajs = require('./kafkajs.js');
const knex = require('./knex.js');
const koa = require('./koa.js');
const langchain = require('./langchain.js');
const langgraph = require('./langgraph.js');
const lruMemoizer = require('./lru-memoizer.js');
const mongodb = require('./mongodb.js');
const mongoose = require('./mongoose.js');
const mysql2 = require('./mysql2.js');
const mysql = require('./mysql.js');
const nestjs = require('./nestjs.js');
const openai = require('./openai.js');
const pg = require('./pg.js');
const postgres = require('./postgres.js');
const redis = require('./redis.js');
const remix = require('./remix.js');
const tedious = require('./tedious.js');
const vercelAi = require('./vercel-ai.js');

const SENTRY_INSTRUMENTATIONS = [
  ...amqplib.amqplibConfig,
  ...anthropicAi.anthropicAiConfig,
  ...awsSdk.awsSdkConfig,
  ...dataloader.dataloaderConfig,
  ...express.expressConfig,
  ...firebase.firebaseConfig,
  ...genericPool.genericPoolConfig,
  ...googleGenai.googleGenAiConfig,
  ...graphql.graphqlConfig,
  ...hapi.hapiConfig,
  ...ioredis.ioredisConfig,
  ...kafkajs.kafkajsConfig,
  ...knex.knexConfig,
  ...koa.koaConfig,
  ...langchain.langchainConfig,
  ...langgraph.langgraphConfig,
  ...lruMemoizer.lruMemoizerConfig,
  ...mongodb.mongodbConfig,
  ...mongoose.mongooseConfig,
  ...mysql2.mysql2Config,
  ...mysql.mysqlConfig,
  ...nestjs.nestjsConfig,
  ...openai.openaiConfig,
  ...pg.pgConfig,
  ...postgres.postgresJsConfig,
  ...redis.redisConfig,
  ...remix.remixConfig,
  ...tedious.tediousConfig,
  ...vercelAi.vercelAiConfig
];
const SUBSCRIBE_INJECTIONS = [
  ...amqplib.amqplibSubscribeInjection,
  ...anthropicAi.anthropicAiSubscribeInjection,
  ...awsSdk.awsSdkSubscribeInjection,
  ...dataloader.dataloaderSubscribeInjection,
  ...express.expressSubscribeInjection,
  ...firebase.firebaseSubscribeInjection,
  ...genericPool.genericPoolSubscribeInjection,
  ...googleGenai.googleGenAiSubscribeInjection,
  ...graphql.graphqlSubscribeInjection,
  ...hapi.hapiSubscribeInjection,
  ...ioredis.ioredisSubscribeInjection,
  ...kafkajs.kafkajsSubscribeInjection,
  ...knex.knexSubscribeInjection,
  ...koa.koaSubscribeInjection,
  ...langchain.langchainSubscribeInjection,
  ...langgraph.langgraphSubscribeInjection,
  ...lruMemoizer.lruMemoizerSubscribeInjection,
  ...mongodb.mongodbSubscribeInjection,
  ...mongoose.mongooseSubscribeInjection,
  ...mysql2.mysql2SubscribeInjection,
  ...mysql.mysqlSubscribeInjection,
  ...nestjs.nestjsSubscribeInjection,
  ...openai.openaiSubscribeInjection,
  ...pg.pgSubscribeInjection,
  ...postgres.postgresJsSubscribeInjection,
  ...redis.redisSubscribeInjection,
  ...remix.remixSubscribeInjection,
  ...tedious.tediousSubscribeInjection,
  ...vercelAi.vercelAiSubscribeInjection
];
function instrumentedModuleNames(instrumentations = []) {
  return [
    ...core.uniq([...SENTRY_INSTRUMENTATIONS, ...instrumentations].map((i) => i.module.name)),
    // Additional things that need to be bundled but are not covered by the above
    // Remix needs to bundle this so @remix-run/server-runtime is _also_ bundled
    "@remix-run/node"
  ];
}
const INSTRUMENTED_MODULE_NAMES = instrumentedModuleNames();
function withoutInstrumentedExternals(external, moduleNames = INSTRUMENTED_MODULE_NAMES) {
  if (!external) {
    return void 0;
  }
  return external.filter((entry) => !moduleNames.some((name) => entry === name || entry.startsWith(`${name}/`)));
}

exports.INSTRUMENTED_MODULE_NAMES = INSTRUMENTED_MODULE_NAMES;
exports.SENTRY_INSTRUMENTATIONS = SENTRY_INSTRUMENTATIONS;
exports.SUBSCRIBE_INJECTIONS = SUBSCRIBE_INJECTIONS;
exports.instrumentedModuleNames = instrumentedModuleNames;
exports.withoutInstrumentedExternals = withoutInstrumentedExternals;
//# sourceMappingURL=index.js.map
