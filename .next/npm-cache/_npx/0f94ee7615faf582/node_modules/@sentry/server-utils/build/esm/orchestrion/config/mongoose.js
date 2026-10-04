import { toSubscribeInjections } from './subscribe-injection.js';

const module$1 = { name: "mongoose", versionRange: ">=5.9.7 <9.7.0" };
const CONTEXT_CAPTURE_QUERY_METHODS = [
  "find",
  "findOne",
  "deleteOne",
  "deleteMany",
  "estimatedDocumentCount",
  "countDocuments",
  "distinct",
  "where",
  "$where",
  "findOneAndUpdate",
  "findOneAndDelete",
  "findOneAndReplace",
  // 5/6/7 only (removed in 8), inert in recent versions
  "remove",
  "count",
  "findOneAndRemove"
];
const mongooseConfig = [
  // Query execution
  // the span for most read/write operations. `op`, collection and model are
  // read off the `Query` at exec time.
  {
    channelName: "query_exec",
    module: { ...module$1, filePath: "lib/query.js" },
    functionQuery: { expressionName: "exec", kind: "Auto" }
  },
  // Aggregation pipeline execution.
  {
    channelName: "aggregate_exec",
    module: { ...module$1, filePath: "lib/aggregate.js" },
    functionQuery: { expressionName: "exec", kind: "Auto" }
  },
  // `doc.save()` (and its `$save` alias, which mongoose points at `save` on
  // require. the alias picks up the transformed body automatically, so no
  // separate entry is needed).
  {
    channelName: "model_save",
    module: { ...module$1, filePath: "lib/model.js" },
    functionQuery: { expressionName: "save", kind: "Auto" }
  },
  // Static batch operations.
  {
    channelName: "model_insert_many",
    module: { ...module$1, filePath: "lib/model.js" },
    functionQuery: { expressionName: "insertMany", kind: "Auto" }
  },
  {
    channelName: "model_bulk_write",
    module: { ...module$1, filePath: "lib/model.js" },
    functionQuery: { expressionName: "bulkWrite", kind: "Auto" }
  },
  // `doc.remove()` (a document method, deprecated in 6 and removed in 7)
  // `expressionName: 'remove'` also matches the sibling `Model.remove`
  // *static* in this file, which no matcher can tell apart from the prototype
  // method; that static is deprecated and would just produce a redundant span
  // so the collision is accepted rather than dropping the doc-method span.
  {
    channelName: "model_remove",
    module: { ...module$1, filePath: "lib/model.js" },
    functionQuery: { expressionName: "remove", kind: "Auto" }
  },
  // NOTE: document `updateOne`/`deleteOne` (mongoose 8.21+) are deliberately
  // NOT hooked here. The vendored OTel/IITM patcher wraps
  // `Model.prototype.updateOne`/`deleteOne`, but those delegate to
  // `Query.exec`, which the `query_exec` channel above already instruments
  // (its `this.op` is the right operation). Verified by the `mongoose-v8`
  // suite against a real mongoose 8.21+ under orchestrion. A dedicated hook
  // is also not possible cleanly: `expressionName: 'updateOne'` in
  // `lib/model.js` can't be told apart from the same-named `Model.updateOne`
  // *static* (the common query-builder form), so hooking it would double-span
  // every `Model.updateOne(...)` call.
  //
  // `Model.aggregate()` builds an `Aggregate` with no method to hook for
  // context capture, so hook the static itself and stash the active span
  // on the returned aggregate. `Sync`: it returns the aggregate.
  {
    channelName: "model_aggregate",
    module: { ...module$1, filePath: "lib/model.js" },
    functionQuery: { expressionName: "aggregate", kind: "Sync" }
  },
  ...CONTEXT_CAPTURE_QUERY_METHODS.map((methodName) => ({
    channelName: `ctx_${methodName}`,
    module: { ...module$1, filePath: "lib/query.js" },
    functionQuery: { expressionName: methodName, kind: "Sync" }
  }))
];
const mongooseChannels = {
  MONGOOSE_QUERY_EXEC: "orchestrion:mongoose:query_exec",
  MONGOOSE_AGGREGATE_EXEC: "orchestrion:mongoose:aggregate_exec",
  MONGOOSE_MODEL_SAVE: "orchestrion:mongoose:model_save",
  MONGOOSE_MODEL_INSERT_MANY: "orchestrion:mongoose:model_insert_many",
  MONGOOSE_MODEL_BULK_WRITE: "orchestrion:mongoose:model_bulk_write",
  MONGOOSE_MODEL_REMOVE: "orchestrion:mongoose:model_remove",
  MONGOOSE_MODEL_AGGREGATE: "orchestrion:mongoose:model_aggregate"
};
const MONGOOSE_CONTEXT_CAPTURE_CHANNELS = CONTEXT_CAPTURE_QUERY_METHODS.map(
  (methodName) => `orchestrion:mongoose:ctx_${methodName}`
);
const mongooseSubscribeInjection = toSubscribeInjections(mongooseConfig);

export { MONGOOSE_CONTEXT_CAPTURE_CHANNELS, mongooseChannels, mongooseConfig, mongooseSubscribeInjection };
//# sourceMappingURL=mongoose.js.map
