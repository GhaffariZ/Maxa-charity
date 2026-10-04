import { toSubscribeInjections } from './subscribe-injection.js';

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
const koaSubscribeInjection = toSubscribeInjections(koaConfig);

export { koaChannels, koaConfig, koaSubscribeInjection };
//# sourceMappingURL=koa.js.map
