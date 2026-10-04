Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const debugBuild = require('../debug-build.js');
const tracingChannel = require('../tracing-channel.js');

const MONGOOSE_DC_CHANNEL_QUERY = "mongoose:query";
const MONGOOSE_DC_CHANNEL_AGGREGATE = "mongoose:aggregate";
const MONGOOSE_DC_CHANNEL_MODEL_SAVE = "mongoose:model:save";
const MONGOOSE_DC_CHANNEL_MODEL_INSERT_MANY = "mongoose:model:insertMany";
const MONGOOSE_DC_CHANNEL_MODEL_BULK_WRITE = "mongoose:model:bulkWrite";
const MONGOOSE_DC_CHANNEL_CURSOR_NEXT = "mongoose:cursor:next";
const ORIGIN = "auto.db.mongoose.diagnostic_channel";
const DB_SYSTEM_NAME_VALUE_MONGODB = "mongodb";
const MAX_REDACTION_DEPTH = 10;
let subscribed = false;
function subscribeMongooseDiagnosticChannels(tracingChannel) {
  if (subscribed) {
    return;
  }
  subscribed = true;
  try {
    setupChannel(tracingChannel, MONGOOSE_DC_CHANNEL_QUERY);
    setupChannel(tracingChannel, MONGOOSE_DC_CHANNEL_AGGREGATE);
    setupChannel(tracingChannel, MONGOOSE_DC_CHANNEL_MODEL_SAVE);
    setupChannel(tracingChannel, MONGOOSE_DC_CHANNEL_MODEL_INSERT_MANY);
    setupChannel(tracingChannel, MONGOOSE_DC_CHANNEL_MODEL_BULK_WRITE);
    setupChannel(tracingChannel, MONGOOSE_DC_CHANNEL_CURSOR_NEXT);
  } catch {
    debugBuild.DEBUG_BUILD && core.debug.log("Mongoose node:diagnostics_channel subscription failed.");
  }
}
function setupChannel(tracingChannel$1, channelName) {
  tracingChannel.bindTracingChannelToSpan(tracingChannel$1(channelName), (data) => {
    const collection = data.collection;
    const queryText = redactMongoQuery(data.args?.pipeline ?? data.args?.filter);
    const batchSize = getBatchSize(data);
    return core.startInactiveSpan({
      name: collection ? `mongoose.${collection}.${data.operation}` : `mongoose.${data.operation}`,
      attributes: {
        [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: "db",
        [attributes.DB_SYSTEM_NAME]: DB_SYSTEM_NAME_VALUE_MONGODB,
        [attributes.DB_OPERATION_NAME]: data.operation,
        [attributes.DB_COLLECTION_NAME]: collection ?? void 0,
        [attributes.DB_NAMESPACE]: data.database ?? void 0,
        [attributes.DB_QUERY_TEXT]: queryText ?? void 0,
        [attributes.DB_OPERATION_BATCH_SIZE]: batchSize ?? void 0,
        [attributes.SERVER_ADDRESS]: data.serverAddress ?? void 0,
        [attributes.SERVER_PORT]: data.serverPort ?? void 0
      }
    });
  });
}
function getBatchSize(data) {
  const args = data.args;
  const batch = data.operation === "insertMany" ? args?.docs : data.operation === "bulkWrite" ? args?.ops : void 0;
  return Array.isArray(batch) && batch.length > 1 ? batch.length : void 0;
}
function redactMongoQuery(value) {
  if (value == null) {
    return void 0;
  }
  try {
    const redacted = redactValue(value, 0);
    const text = JSON.stringify(redacted);
    return text == null || text === "{}" || text === "[]" ? void 0 : text;
  } catch {
    return void 0;
  }
}
function redactValue(value, depth) {
  if (depth > MAX_REDACTION_DEPTH) {
    return "?";
  }
  if (Array.isArray(value)) {
    return value.map((item) => redactValue(item, depth + 1));
  }
  if (core.isObjectLike(value)) {
    const out = {};
    for (const key of Object.keys(value)) {
      out[key] = redactValue(value[key], depth + 1);
    }
    return out;
  }
  return "?";
}

exports.MONGOOSE_DC_CHANNEL_AGGREGATE = MONGOOSE_DC_CHANNEL_AGGREGATE;
exports.MONGOOSE_DC_CHANNEL_CURSOR_NEXT = MONGOOSE_DC_CHANNEL_CURSOR_NEXT;
exports.MONGOOSE_DC_CHANNEL_MODEL_BULK_WRITE = MONGOOSE_DC_CHANNEL_MODEL_BULK_WRITE;
exports.MONGOOSE_DC_CHANNEL_MODEL_INSERT_MANY = MONGOOSE_DC_CHANNEL_MODEL_INSERT_MANY;
exports.MONGOOSE_DC_CHANNEL_MODEL_SAVE = MONGOOSE_DC_CHANNEL_MODEL_SAVE;
exports.MONGOOSE_DC_CHANNEL_QUERY = MONGOOSE_DC_CHANNEL_QUERY;
exports.subscribeMongooseDiagnosticChannels = subscribeMongooseDiagnosticChannels;
//# sourceMappingURL=mongoose-dc-subscriber.js.map
