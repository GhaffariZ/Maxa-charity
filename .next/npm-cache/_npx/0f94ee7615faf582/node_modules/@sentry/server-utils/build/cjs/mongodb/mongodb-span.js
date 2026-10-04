Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');

const ATTR_DB_SYSTEM = "db.system";
const ATTR_DB_NAME = "db.name";
const ATTR_DB_OPERATION = "db.operation";
const ATTR_DB_STATEMENT = "db.statement";
const ATTR_DB_MONGODB_COLLECTION = "db.mongodb.collection";
const ATTR_DB_CONNECTION_STRING = "db.connection_string";
const ATTR_NET_PEER_NAME = "net.peer.name";
const ATTR_NET_PEER_PORT = "net.peer.port";
const DB_SYSTEM_VALUE_MONGODB = "mongodb";
function serializeDbStatement(commandObj) {
  return JSON.stringify(scrubStatement(commandObj));
}
function scrubStatement(value) {
  if (Array.isArray(value)) {
    return value.map((element) => scrubStatement(element));
  }
  if (isCommandObj(value)) {
    const initial = {};
    return Object.entries(value).map(([key, element]) => [key, scrubStatement(element)]).reduce((prev, current) => {
      if (isCommandEntry(current)) {
        prev[current[0]] = current[1];
      }
      return prev;
    }, initial);
  }
  return "?";
}
function isCommandObj(value) {
  return core.isObjectLike(value) && !isBuffer(value);
}
function isBuffer(value) {
  return typeof Buffer !== "undefined" && Buffer.isBuffer(value);
}
function isCommandEntry(value) {
  return Array.isArray(value);
}
function getV4SpanAttributes(connectionCtx, ns, command, operation, origin) {
  let host;
  let port;
  if (connectionCtx) {
    const hostParts = typeof connectionCtx.address === "string" ? connectionCtx.address.split(":") : "";
    if (hostParts.length === 2) {
      host = hostParts[0];
      port = hostParts[1];
    }
  }
  let commandObj;
  if (command?.documents?.[0]) {
    commandObj = command.documents[0];
  } else if (command?.cursors) {
    commandObj = command.cursors;
  } else {
    commandObj = command;
  }
  return getSpanAttributes(ns.db, ns.collection, host, port, commandObj, operation, origin);
}
function getSpanAttributes(dbName, dbCollection, host, port, commandObj, operation, origin) {
  const attributes = {
    [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: origin,
    [ATTR_DB_SYSTEM]: DB_SYSTEM_VALUE_MONGODB,
    [ATTR_DB_NAME]: dbName,
    [ATTR_DB_MONGODB_COLLECTION]: dbCollection,
    [ATTR_DB_OPERATION]: operation,
    [ATTR_DB_CONNECTION_STRING]: `mongodb://${host}:${port}/${dbName}`
  };
  if (host && port) {
    attributes[ATTR_NET_PEER_NAME] = host;
    const portNumber = parseInt(port, 10);
    if (!isNaN(portNumber)) {
      attributes[ATTR_NET_PEER_PORT] = portNumber;
    }
  }
  if (commandObj) {
    try {
      attributes[ATTR_DB_STATEMENT] = serializeDbStatement(commandObj);
    } catch {
    }
  }
  return attributes;
}
function getV3CommandOperation(command) {
  if (command.createIndexes !== void 0) {
    return "createIndexes";
  } else if (command.findandmodify !== void 0) {
    return "findAndModify";
  } else if (command.ismaster !== void 0) {
    return "isMaster";
  } else if (command.count !== void 0) {
    return "count";
  } else if (command.aggregate !== void 0) {
    return "aggregate";
  }
  return void 0;
}
function getV3SpanAttributes(ns, topology, command, operation, origin) {
  let host;
  let port;
  if (topology?.s) {
    host = topology.s.options?.host ?? topology.s.host;
    port = (topology.s.options?.port ?? topology.s.port)?.toString();
    if (host == null || port == null) {
      const address = topology.description?.address;
      if (address) {
        const segments = address.split(":");
        host = segments[0];
        port = segments[1];
      }
    }
  }
  const [dbName, dbCollection] = ns.toString().split(".");
  const commandObj = command?.query ?? command?.q ?? command;
  return getSpanAttributes(dbName, dbCollection, host, port, commandObj, operation, origin);
}
function startMongoSpan(attributes) {
  return core.startInactiveSpan({
    name: `mongodb.${attributes[ATTR_DB_OPERATION] || "command"}`,
    op: "db",
    kind: core.SPAN_KIND.CLIENT,
    attributes
  });
}

exports.getSpanAttributes = getSpanAttributes;
exports.getV3CommandOperation = getV3CommandOperation;
exports.getV3SpanAttributes = getV3SpanAttributes;
exports.getV4SpanAttributes = getV4SpanAttributes;
exports.serializeDbStatement = serializeDbStatement;
exports.startMongoSpan = startMongoSpan;
//# sourceMappingURL=mongodb-span.js.map
