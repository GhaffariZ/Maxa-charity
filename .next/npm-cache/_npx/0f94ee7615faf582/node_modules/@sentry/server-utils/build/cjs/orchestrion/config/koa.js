Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const subscribeInjection = require('./subscribe-injection.js');

const koaConfig = [
  {
    channelName: "use",
    module: { name: "koa", versionRange: ">=2.0.0 <4", filePath: "lib/application.js" },
    functionQuery: { className: "Application", methodName: "use", kind: "Sync" }
  }
];
const koaChannels = {
  KOA_USE: "orchestrion:koa:use"
};
const koaSubscribeInjection = subscribeInjection.toSubscribeInjections(koaConfig);

exports.koaChannels = koaChannels;
exports.koaConfig = koaConfig;
exports.koaSubscribeInjection = koaSubscribeInjection;
//# sourceMappingURL=koa.js.map
