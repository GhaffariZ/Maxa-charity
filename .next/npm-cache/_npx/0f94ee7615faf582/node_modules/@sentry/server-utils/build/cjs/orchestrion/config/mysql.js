Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const subscribeInjection = require('./subscribe-injection.js');

const mysqlConfig = [
  {
    channelName: "query",
    module: { name: "mysql", versionRange: ">=2.0.0 <3", filePath: "lib/Connection.js" },
    functionQuery: { expressionName: "query", kind: "Auto" }
  }
];
const mysqlChannels = {
  MYSQL_QUERY: "orchestrion:mysql:query"
};
const mysqlSubscribeInjection = subscribeInjection.toSubscribeInjections(mysqlConfig);

exports.mysqlChannels = mysqlChannels;
exports.mysqlConfig = mysqlConfig;
exports.mysqlSubscribeInjection = mysqlSubscribeInjection;
//# sourceMappingURL=mysql.js.map
