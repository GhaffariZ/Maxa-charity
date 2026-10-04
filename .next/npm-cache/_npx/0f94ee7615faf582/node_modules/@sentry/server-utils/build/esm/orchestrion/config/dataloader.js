import { toSubscribeInjections } from './subscribe-injection.js';

const module$1 = { name: "dataloader", versionRange: ">=2.0.0 <3", filePath: "index.js" };
const dataloaderConfig = [
  // Wrap the constructor so the subscriber can wrap the user's `batchLoadFn` (arg 0). The batch span
  // is opened when that wrapped function actually runs (on the deferred dispatch tick), mirroring the
  // vendored OTel instrumentation which also wraps `batchLoadFn` at construction time.
  {
    channelName: "construct",
    module: module$1,
    functionQuery: { functionName: "DataLoader", kind: "Sync" }
  },
  // `load`/`loadMany` return Promises, so they're `Async`: the span ends on `asyncEnd` (when the
  // load resolves), capturing the real latency and enclosing the deferred `batch` span — matching the
  // vendored OTel `startSpan`. `prime`/`clear`/`clearAll` return `this` synchronously, so they stay `Sync`.
  {
    channelName: "load",
    module: module$1,
    functionQuery: { expressionName: "load", kind: "Async" }
  },
  {
    channelName: "loadMany",
    module: module$1,
    functionQuery: { expressionName: "loadMany", kind: "Async" }
  },
  {
    channelName: "prime",
    module: module$1,
    functionQuery: { expressionName: "prime", kind: "Sync" }
  },
  {
    channelName: "clear",
    module: module$1,
    functionQuery: { expressionName: "clear", kind: "Sync" }
  },
  {
    channelName: "clearAll",
    module: module$1,
    functionQuery: { expressionName: "clearAll", kind: "Sync" }
  }
];
const dataloaderChannels = {
  DATALOADER_CONSTRUCT: "orchestrion:dataloader:construct",
  DATALOADER_LOAD: "orchestrion:dataloader:load",
  DATALOADER_LOAD_MANY: "orchestrion:dataloader:loadMany",
  DATALOADER_PRIME: "orchestrion:dataloader:prime",
  DATALOADER_CLEAR: "orchestrion:dataloader:clear",
  DATALOADER_CLEAR_ALL: "orchestrion:dataloader:clearAll"
};
const dataloaderSubscribeInjection = toSubscribeInjections(dataloaderConfig);

export { dataloaderChannels, dataloaderConfig, dataloaderSubscribeInjection };
//# sourceMappingURL=dataloader.js.map
