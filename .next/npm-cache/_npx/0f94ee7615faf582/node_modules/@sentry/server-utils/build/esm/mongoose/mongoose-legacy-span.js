import { startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';

const ATTR_DB_MONGODB_COLLECTION = "db.mongodb.collection";
const ATTR_DB_NAME = "db.name";
const ATTR_DB_USER = "db.user";
const ATTR_NET_PEER_NAME = "net.peer.name";
const ATTR_NET_PEER_PORT = "net.peer.port";
const ATTR_DB_OPERATION = "db.operation";
const ATTR_DB_SYSTEM = "db.system";
function startMongooseLegacySpan({
  collection,
  modelName,
  operation,
  origin,
  parentSpan
}) {
  const attributes = {
    [ATTR_DB_MONGODB_COLLECTION]: collection?.name,
    [ATTR_DB_NAME]: collection?.conn?.name,
    [ATTR_DB_USER]: collection?.conn?.user,
    [ATTR_NET_PEER_NAME]: collection?.conn?.host,
    [ATTR_NET_PEER_PORT]: collection?.conn?.port,
    [ATTR_DB_OPERATION]: operation,
    [ATTR_DB_SYSTEM]: "mongoose",
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: origin
  };
  return startInactiveSpan({
    name: `mongoose.${modelName}.${operation}`,
    // Set this explicitly, for platforms lacking `inferDbSpanData`
    op: "db",
    kind: SPAN_KIND.CLIENT,
    attributes,
    parentSpan
  });
}

export { startMongooseLegacySpan };
//# sourceMappingURL=mongoose-legacy-span.js.map
