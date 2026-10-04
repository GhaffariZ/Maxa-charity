import { uniq } from '@sentry/core';
import { awsSdkConfig, awsSdkSubscribeInjection } from './aws-sdk.js';
import { amqplibConfig, amqplibSubscribeInjection } from './amqplib.js';
import { anthropicAiConfig, anthropicAiSubscribeInjection } from './anthropic-ai.js';
import { dataloaderConfig, dataloaderSubscribeInjection } from './dataloader.js';
import { expressConfig, expressSubscribeInjection } from './express.js';
import { firebaseConfig, firebaseSubscribeInjection } from './firebase.js';
import { genericPoolConfig, genericPoolSubscribeInjection } from './generic-pool.js';
import { googleGenAiConfig, googleGenAiSubscribeInjection } from './google-genai.js';
import { graphqlConfig, graphqlSubscribeInjection } from './graphql.js';
import { hapiConfig, hapiSubscribeInjection } from './hapi.js';
import { ioredisConfig, ioredisSubscribeInjection } from './ioredis.js';
import { kafkajsConfig, kafkajsSubscribeInjection } from './kafkajs.js';
import { knexConfig, knexSubscribeInjection } from './knex.js';
import { koaConfig, koaSubscribeInjection } from './koa.js';
import { langchainConfig, langchainSubscribeInjection } from './langchain.js';
import { langgraphConfig, langgraphSubscribeInjection } from './langgraph.js';
import { lruMemoizerConfig, lruMemoizerSubscribeInjection } from './lru-memoizer.js';
import { mongodbConfig, mongodbSubscribeInjection } from './mongodb.js';
import { mongooseConfig, mongooseSubscribeInjection } from './mongoose.js';
import { mysql2Config, mysql2SubscribeInjection } from './mysql2.js';
import { mysqlConfig, mysqlSubscribeInjection } from './mysql.js';
import { nestjsConfig, nestjsSubscribeInjection } from './nestjs.js';
import { openaiConfig, openaiSubscribeInjection } from './openai.js';
import { pgConfig, pgSubscribeInjection } from './pg.js';
import { postgresJsConfig, postgresJsSubscribeInjection } from './postgres.js';
import { redisConfig, redisSubscribeInjection } from './redis.js';
import { remixConfig, remixSubscribeInjection } from './remix.js';
import { tediousConfig, tediousSubscribeInjection } from './tedious.js';
import { vercelAiConfig, vercelAiSubscribeInjection } from './vercel-ai.js';

const SENTRY_INSTRUMENTATIONS = [
  ...amqplibConfig,
  ...anthropicAiConfig,
  ...awsSdkConfig,
  ...dataloaderConfig,
  ...expressConfig,
  ...firebaseConfig,
  ...genericPoolConfig,
  ...googleGenAiConfig,
  ...graphqlConfig,
  ...hapiConfig,
  ...ioredisConfig,
  ...kafkajsConfig,
  ...knexConfig,
  ...koaConfig,
  ...langchainConfig,
  ...langgraphConfig,
  ...lruMemoizerConfig,
  ...mongodbConfig,
  ...mongooseConfig,
  ...mysql2Config,
  ...mysqlConfig,
  ...nestjsConfig,
  ...openaiConfig,
  ...pgConfig,
  ...postgresJsConfig,
  ...redisConfig,
  ...remixConfig,
  ...tediousConfig,
  ...vercelAiConfig
];
const SUBSCRIBE_INJECTIONS = [
  ...amqplibSubscribeInjection,
  ...anthropicAiSubscribeInjection,
  ...awsSdkSubscribeInjection,
  ...dataloaderSubscribeInjection,
  ...expressSubscribeInjection,
  ...firebaseSubscribeInjection,
  ...genericPoolSubscribeInjection,
  ...googleGenAiSubscribeInjection,
  ...graphqlSubscribeInjection,
  ...hapiSubscribeInjection,
  ...ioredisSubscribeInjection,
  ...kafkajsSubscribeInjection,
  ...knexSubscribeInjection,
  ...koaSubscribeInjection,
  ...langchainSubscribeInjection,
  ...langgraphSubscribeInjection,
  ...lruMemoizerSubscribeInjection,
  ...mongodbSubscribeInjection,
  ...mongooseSubscribeInjection,
  ...mysql2SubscribeInjection,
  ...mysqlSubscribeInjection,
  ...nestjsSubscribeInjection,
  ...openaiSubscribeInjection,
  ...pgSubscribeInjection,
  ...postgresJsSubscribeInjection,
  ...redisSubscribeInjection,
  ...remixSubscribeInjection,
  ...tediousSubscribeInjection,
  ...vercelAiSubscribeInjection
];
function instrumentedModuleNames(instrumentations = []) {
  return [
    ...uniq([...SENTRY_INSTRUMENTATIONS, ...instrumentations].map((i) => i.module.name)),
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

export { INSTRUMENTED_MODULE_NAMES, SENTRY_INSTRUMENTATIONS, SUBSCRIBE_INJECTIONS, instrumentedModuleNames, withoutInstrumentedExternals };
//# sourceMappingURL=index.js.map
