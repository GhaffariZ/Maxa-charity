/**
 * Fully-qualified `diagnostics_channel` names that orchestrion publishes to.
 *
 * Orchestrion's transform always prefixes the configured `channelName` with
 * `orchestrion:${module.name}:`. So a config of
 *   `{ channelName: 'query', module: { name: 'mysql' } }`
 * publishes to `orchestrion:mysql:query`.
 *
 * Subscribers (`integrations/<lib>/tracing-channel.ts`) consume the full
 * prefixed string from this map; the config files set only the unprefixed
 * suffix in `channelName`. Keeping both pieces in one file is what guarantees
 * they don't drift apart and silently stop firing.
 *
 * Kept sorted alphabetically by module so concurrent additions insert at different
 * points rather than all appending to the end (fewer merge conflicts).
 */
export declare const CHANNELS: {
    readonly VERCEL_AI_GENERATE_TEXT: "orchestrion:ai:generateText";
    readonly VERCEL_AI_STREAM_TEXT: "orchestrion:ai:streamText";
    readonly VERCEL_AI_GENERATE_OBJECT: "orchestrion:ai:generateObject";
    readonly VERCEL_AI_EMBED: "orchestrion:ai:embed";
    readonly VERCEL_AI_EMBED_MANY: "orchestrion:ai:embedMany";
    readonly VERCEL_AI_EXECUTE_TOOL_CALL: "orchestrion:ai:executeToolCall";
    readonly VERCEL_AI_RESOLVE_LANGUAGE_MODEL: "orchestrion:ai:resolveLanguageModel";
    readonly TEDIOUS_CONNECT: "orchestrion:tedious:connect";
    readonly TEDIOUS_EXEC_SQL: "orchestrion:tedious:execSql";
    readonly TEDIOUS_EXEC_SQL_BATCH: "orchestrion:tedious:execSqlBatch";
    readonly TEDIOUS_CALL_PROCEDURE: "orchestrion:tedious:callProcedure";
    readonly TEDIOUS_EXEC_BULK_LOAD: "orchestrion:tedious:execBulkLoad";
    readonly TEDIOUS_PREPARE: "orchestrion:tedious:prepare";
    readonly TEDIOUS_EXECUTE: "orchestrion:tedious:execute";
    readonly REMIX_REQUEST_HANDLER: "orchestrion:@remix-run/server-runtime:requestHandler";
    readonly REMIX_MATCH_SERVER_ROUTES: "orchestrion:@remix-run/server-runtime:matchServerRoutes";
    readonly REMIX_CALL_ROUTE_LOADER: "orchestrion:@remix-run/server-runtime:callRouteLoader";
    readonly REMIX_CALL_ROUTE_ACTION: "orchestrion:@remix-run/server-runtime:callRouteAction";
    readonly REDIS_COMMAND: "orchestrion:redis:command";
    readonly NODE_REDIS_COMMAND: "orchestrion:@redis/client:command";
    readonly NODE_REDIS_EXECUTOR: "orchestrion:@redis/client:executor";
    readonly NODE_REDIS_CONNECT: "orchestrion:@redis/client:connect";
    readonly NODE_REDIS_MULTI: "orchestrion:@redis/client:multi";
    readonly NODE_REDIS_PIPELINE: "orchestrion:@redis/client:pipeline";
    readonly NODE_REDIS_BATCH: "orchestrion:@redis/client:batch";
    readonly POSTGRESJS_HANDLE: "orchestrion:postgres:handle";
    readonly POSTGRESJS_CONNECTION: "orchestrion:postgres:connection";
    readonly POSTGRESJS_EXECUTE: "orchestrion:postgres:execute";
    readonly POSTGRESJS_CONNECT: "orchestrion:postgres:connect";
    readonly PG_QUERY: "orchestrion:pg:query";
    readonly PG_CONNECT: "orchestrion:pg:connect";
    readonly PGPOOL_CONNECT: "orchestrion:pg-pool:connect";
    readonly OPENAI_CHAT: "orchestrion:openai:chat";
    readonly OPENAI_EMBEDDINGS: "orchestrion:openai:embeddings";
    readonly NESTJS_APP_CREATION: "orchestrion:@nestjs/core:nestFactoryCreate";
    readonly NESTJS_ROUTER_CONTEXT: "orchestrion:@nestjs/core:routerExecutionContextCreate";
    readonly NESTJS_INJECTABLE: "orchestrion:@nestjs/common:injectableDecorator";
    readonly NESTJS_CATCH: "orchestrion:@nestjs/common:catchDecorator";
    readonly NESTJS_SCHEDULE_CRON: "orchestrion:@nestjs/schedule:cronDecorator";
    readonly NESTJS_SCHEDULE_INTERVAL: "orchestrion:@nestjs/schedule:intervalDecorator";
    readonly NESTJS_SCHEDULE_TIMEOUT: "orchestrion:@nestjs/schedule:timeoutDecorator";
    readonly NESTJS_ONEVENT: "orchestrion:@nestjs/event-emitter:onEventDecorator";
    readonly NESTJS_PROCESSOR: "orchestrion:@nestjs/bullmq:processorDecorator";
    readonly MYSQL_QUERY: "orchestrion:mysql:query";
    readonly MYSQL2_QUERY: "orchestrion:mysql2:query";
    readonly MYSQL2_EXECUTE: "orchestrion:mysql2:execute";
    readonly MONGOOSE_QUERY_EXEC: "orchestrion:mongoose:query_exec";
    readonly MONGOOSE_AGGREGATE_EXEC: "orchestrion:mongoose:aggregate_exec";
    readonly MONGOOSE_MODEL_SAVE: "orchestrion:mongoose:model_save";
    readonly MONGOOSE_MODEL_INSERT_MANY: "orchestrion:mongoose:model_insert_many";
    readonly MONGOOSE_MODEL_BULK_WRITE: "orchestrion:mongoose:model_bulk_write";
    readonly MONGOOSE_MODEL_REMOVE: "orchestrion:mongoose:model_remove";
    readonly MONGOOSE_MODEL_AGGREGATE: "orchestrion:mongoose:model_aggregate";
    readonly MONGODB_COMMAND: "orchestrion:mongodb:command";
    readonly MONGODB_CHECKOUT: "orchestrion:mongodb:checkout";
    readonly MONGODB_V3_INSERT: "orchestrion:mongodb:v3_insert";
    readonly MONGODB_V3_UPDATE: "orchestrion:mongodb:v3_update";
    readonly MONGODB_V3_REMOVE: "orchestrion:mongodb:v3_remove";
    readonly MONGODB_V3_COMMAND: "orchestrion:mongodb:v3_command";
    readonly MONGODB_V3_QUERY: "orchestrion:mongodb:v3_query";
    readonly MONGODB_V3_GET_MORE: "orchestrion:mongodb:v3_get_more";
    readonly LRU_MEMOIZER_LOAD: "orchestrion:lru-memoizer:load";
    readonly LANGGRAPH_STATE_GRAPH_COMPILE: "orchestrion:@langchain/langgraph:stateGraphCompile";
    readonly LANGGRAPH_CREATE_REACT_AGENT: "orchestrion:@langchain/langgraph:createReactAgent";
    readonly LANGCHAIN_CHAT_MODEL_INVOKE: "orchestrion:@langchain/core:chatModelInvoke";
    readonly LANGCHAIN_CHAT_MODEL_STREAM: "orchestrion:@langchain/core:chatModelStream";
    readonly KOA_USE: "orchestrion:koa:use";
    readonly KNEX_QUERY: "orchestrion:knex:query";
    readonly KNEX_QUERY_BUILDER: "orchestrion:knex:queryBuilder";
    readonly KNEX_SCHEMA_BUILDER: "orchestrion:knex:schemaBuilder";
    readonly KNEX_RAW: "orchestrion:knex:raw";
    readonly KAFKAJS_SEND_BATCH: "orchestrion:kafkajs:send_batch";
    readonly KAFKAJS_CONSUMER_RUN: "orchestrion:kafkajs:consumer_run";
    readonly IOREDIS_COMMAND: "orchestrion:ioredis:command";
    readonly IOREDIS_CONNECT: "orchestrion:ioredis:connect";
    readonly HAPI_ROUTE: "orchestrion:@hapi/hapi:route";
    readonly HAPI_EXT: "orchestrion:@hapi/hapi:ext";
    readonly GRAPHQL_PARSE: "orchestrion:graphql:parse";
    readonly GRAPHQL_VALIDATE: "orchestrion:graphql:validate";
    readonly GRAPHQL_EXECUTE: "orchestrion:graphql:execute";
    readonly GOOGLE_GENAI_GENERATE_CONTENT: "orchestrion:@google/genai:generate-content";
    readonly GOOGLE_GENAI_EMBED_CONTENT: "orchestrion:@google/genai:embed-content";
    readonly GOOGLE_GENAI_CHAT: "orchestrion:@google/genai:chat";
    readonly GENERIC_POOL_ACQUIRE: "orchestrion:generic-pool:acquire";
    readonly FIREBASE_FIRESTORE_ADD_DOC: "orchestrion:@firebase/firestore:add-doc";
    readonly FIREBASE_FIRESTORE_GET_DOCS: "orchestrion:@firebase/firestore:get-docs";
    readonly FIREBASE_FIRESTORE_SET_DOC: "orchestrion:@firebase/firestore:set-doc";
    readonly FIREBASE_FIRESTORE_DELETE_DOC: "orchestrion:@firebase/firestore:delete-doc";
    readonly FIREBASE_FUNCTIONS_HTTP_REQUEST: "orchestrion:firebase-functions:http-request";
    readonly FIREBASE_FUNCTIONS_HTTP_CALL: "orchestrion:firebase-functions:http-call";
    readonly FIREBASE_FUNCTIONS_FIRESTORE_CREATED: "orchestrion:firebase-functions:firestore-created";
    readonly FIREBASE_FUNCTIONS_FIRESTORE_UPDATED: "orchestrion:firebase-functions:firestore-updated";
    readonly FIREBASE_FUNCTIONS_FIRESTORE_DELETED: "orchestrion:firebase-functions:firestore-deleted";
    readonly FIREBASE_FUNCTIONS_FIRESTORE_WRITTEN: "orchestrion:firebase-functions:firestore-written";
    readonly FIREBASE_FUNCTIONS_SCHEDULER: "orchestrion:firebase-functions:scheduler";
    readonly FIREBASE_FUNCTIONS_STORAGE_FINALIZED: "orchestrion:firebase-functions:storage-finalized";
    readonly FIREBASE_FUNCTIONS_STORAGE_ARCHIVED: "orchestrion:firebase-functions:storage-archived";
    readonly FIREBASE_FUNCTIONS_STORAGE_DELETED: "orchestrion:firebase-functions:storage-deleted";
    readonly FIREBASE_FUNCTIONS_STORAGE_METADATA_UPDATED: "orchestrion:firebase-functions:storage-metadata-updated";
    readonly EXPRESS_HANDLE: "orchestrion:express:handle";
    readonly ROUTER_HANDLE: "orchestrion:router:handle";
    readonly EXPRESS_REGISTER: "orchestrion:express:register";
    readonly ROUTER_REGISTER: "orchestrion:router:register";
    readonly DATALOADER_CONSTRUCT: "orchestrion:dataloader:construct";
    readonly DATALOADER_LOAD: "orchestrion:dataloader:load";
    readonly DATALOADER_LOAD_MANY: "orchestrion:dataloader:loadMany";
    readonly DATALOADER_PRIME: "orchestrion:dataloader:prime";
    readonly DATALOADER_CLEAR: "orchestrion:dataloader:clear";
    readonly DATALOADER_CLEAR_ALL: "orchestrion:dataloader:clearAll";
    readonly AWS_SMITHY_CORE_SEND: "orchestrion:@smithy/core:send";
    readonly AWS_SMITHY_CLIENT_SEND: "orchestrion:@smithy/smithy-client:send";
    readonly AWS_SDK_SMITHY_CLIENT_SEND: "orchestrion:@aws-sdk/smithy-client:send";
    readonly ANTHROPIC_CHAT: "orchestrion:@anthropic-ai/sdk:chat";
    readonly ANTHROPIC_MODELS: "orchestrion:@anthropic-ai/sdk:models";
    readonly ANTHROPIC_MESSAGES_STREAM: "orchestrion:@anthropic-ai/sdk:messages-stream";
    readonly AMQPLIB_PUBLISH: "orchestrion:amqplib:publish";
    readonly AMQPLIB_CONFIRM_PUBLISH: "orchestrion:amqplib:confirmPublish";
    readonly AMQPLIB_CONSUME: "orchestrion:amqplib:consume";
    readonly AMQPLIB_DISPATCH: "orchestrion:amqplib:dispatch";
    readonly AMQPLIB_ACK: "orchestrion:amqplib:ack";
    readonly AMQPLIB_NACK: "orchestrion:amqplib:nack";
    readonly AMQPLIB_REJECT: "orchestrion:amqplib:reject";
    readonly AMQPLIB_ACK_ALL: "orchestrion:amqplib:ackAll";
    readonly AMQPLIB_NACK_ALL: "orchestrion:amqplib:nackAll";
    readonly AMQPLIB_CONNECT: "orchestrion:amqplib:connect";
};
export type ChannelName = (typeof CHANNELS)[keyof typeof CHANNELS];
//# sourceMappingURL=channels.d.ts.map