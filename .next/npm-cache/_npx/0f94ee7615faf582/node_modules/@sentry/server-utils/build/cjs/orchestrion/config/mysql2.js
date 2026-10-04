Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const subscribeInjection = require('./subscribe-injection.js');

const mysql2Config = [
  {
    channelName: "query",
    module: { name: "mysql2", versionRange: ">=1.4.2 <3.11.5", filePath: "lib/connection.js" },
    functionQuery: { className: "Connection", methodName: "query", kind: "Callback" }
  },
  {
    channelName: "execute",
    module: { name: "mysql2", versionRange: ">=1.4.2 <3.11.5", filePath: "lib/connection.js" },
    functionQuery: { className: "Connection", methodName: "execute", kind: "Callback" }
  },
  {
    channelName: "query",
    module: { name: "mysql2", versionRange: ">=3.11.5 <3.20.0", filePath: "lib/base/connection.js" },
    functionQuery: { className: "BaseConnection", methodName: "query", kind: "Callback" }
  },
  {
    channelName: "execute",
    module: { name: "mysql2", versionRange: ">=3.11.5 <3.20.0", filePath: "lib/base/connection.js" },
    functionQuery: { className: "BaseConnection", methodName: "execute", kind: "Callback" }
  }
];
const mysql2Channels = {
  MYSQL2_QUERY: "orchestrion:mysql2:query",
  MYSQL2_EXECUTE: "orchestrion:mysql2:execute"
};
const mysql2SubscribeInjection = subscribeInjection.toSubscribeInjections(mysql2Config);

exports.mysql2Channels = mysql2Channels;
exports.mysql2Config = mysql2Config;
exports.mysql2SubscribeInjection = mysql2SubscribeInjection;
//# sourceMappingURL=mysql2.js.map
