Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const index$1 = require('./graphql/index.js');
const index$3 = require('./mongoose/index.js');
const mongodbSpan = require('./mongodb/mongodb-span.js');
const index$4 = require('./mysql2/index.js');
const index$2 = require('./prisma/index.js');
const index$5 = require('./redis/index.js');
const redisStatementSerializer = require('./redis/redis-statement-serializer.js');
const tracingChannel = require('./tracing-channel.js');
const index$6 = require('./vercel-ai/index.js');
const index = require('./integrations/tracing-channel/fastify/index.js');
const mongooseLegacySpan = require('./mongoose/mongoose-legacy-span.js');



exports.graphqlIntegration = index$1.graphqlIntegration;
exports.mongooseIntegration = index$3.mongooseIntegration;
exports.getV3CommandOperation = mongodbSpan.getV3CommandOperation;
exports.getV3SpanAttributes = mongodbSpan.getV3SpanAttributes;
exports.getV4SpanAttributes = mongodbSpan.getV4SpanAttributes;
exports.startMongoSpan = mongodbSpan.startMongoSpan;
exports.mysql2Integration = index$4.mysql2Integration;
exports.instrumentPrisma = index$2.instrumentPrisma;
exports.prismaIntegration = index$2.prismaIntegration;
exports.redisIntegration = index$5.redisIntegration;
exports.defaultDbStatementSerializer = redisStatementSerializer.defaultDbStatementSerializer;
exports.bindTracingChannelToSpan = tracingChannel.bindTracingChannelToSpan;
exports.vercelAiIntegration = index$6.vercelAiIntegration;
exports.fastifyIntegration = index.fastifyIntegration;
exports.handleFastifyError = index.handleFastifyError;
exports.instrumentFastify = index.instrumentFastify;
exports.startMongooseLegacySpan = mongooseLegacySpan.startMongooseLegacySpan;
//# sourceMappingURL=index.js.map
