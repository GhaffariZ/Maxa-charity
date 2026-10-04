import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
import { SDK_VERSION, getActiveSpan, withActiveSpan } from '@sentry/core';
import { startMongooseLegacySpan } from '@sentry/server-utils';
import { handleCallbackResponse, handlePromiseResponse } from './utils.js';

const PACKAGE_NAME = "@sentry/instrumentation-mongoose";
const ORIGIN = "auto.db.otel.mongoose";
const contextCaptureFunctionsCommon = [
  "deleteOne",
  "deleteMany",
  "find",
  "findOne",
  "estimatedDocumentCount",
  "countDocuments",
  "distinct",
  "where",
  "$where",
  "findOneAndUpdate",
  "findOneAndDelete",
  "findOneAndReplace"
];
const contextCaptureFunctions6 = ["remove", "count", "findOneAndRemove", ...contextCaptureFunctionsCommon];
const contextCaptureFunctions7 = ["count", "findOneAndRemove", ...contextCaptureFunctionsCommon];
const contextCaptureFunctions8 = [...contextCaptureFunctionsCommon];
function getContextCaptureFunctions(moduleVersion) {
  if (!moduleVersion) {
    return contextCaptureFunctionsCommon;
  } else if (moduleVersion.startsWith("6.") || moduleVersion.startsWith("5.")) {
    return contextCaptureFunctions6;
  } else if (moduleVersion.startsWith("7.")) {
    return contextCaptureFunctions7;
  } else {
    return contextCaptureFunctions8;
  }
}
function instrumentRemove(moduleVersion) {
  return moduleVersion && (moduleVersion.startsWith("5.") || moduleVersion.startsWith("6.")) || false;
}
function needsDocumentMethodPatch(moduleVersion) {
  if (!moduleVersion || !moduleVersion.startsWith("8.")) {
    return false;
  }
  const minor = parseInt(moduleVersion.split(".")[1], 10);
  return minor >= 21;
}
const _STORED_PARENT_SPAN = /* @__PURE__ */ Symbol("stored-parent-span");
const _ALREADY_INSTRUMENTED = /* @__PURE__ */ Symbol("already-instrumented");
class MongooseInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    const module = new InstrumentationNodeModuleDefinition(
      "mongoose",
      // mongoose >= 9.7.0 publishes via diagnostics_channel and is instrumented by
      // `subscribeMongooseDiagnosticChannels` instead, so this IITM patcher must not
      // overlap it — otherwise every operation would emit two mongoose spans.
      [">=5.9.7 <9.7.0"],
      this.patch.bind(this),
      this.unpatch.bind(this)
    );
    return module;
  }
  patch(module, moduleVersion) {
    const moduleExports = module[Symbol.toStringTag] === "Module" && module.default ? module.default : module;
    this._wrap(moduleExports.Model.prototype, "save", this.patchOnModelMethods("save"));
    moduleExports.Model.prototype.$save = moduleExports.Model.prototype.save;
    if (instrumentRemove(moduleVersion)) {
      this._wrap(moduleExports.Model.prototype, "remove", this.patchOnModelMethods("remove"));
    }
    if (needsDocumentMethodPatch(moduleVersion)) {
      this._wrap(moduleExports.Model.prototype, "updateOne", this._patchDocumentUpdateMethods("updateOne"));
      this._wrap(moduleExports.Model.prototype, "deleteOne", this._patchDocumentUpdateMethods("deleteOne"));
    }
    this._wrap(moduleExports.Query.prototype, "exec", this.patchQueryExec());
    this._wrap(moduleExports.Aggregate.prototype, "exec", this.patchAggregateExec());
    const contextCaptureFunctions = getContextCaptureFunctions(moduleVersion);
    contextCaptureFunctions.forEach((funcName) => {
      this._wrap(moduleExports.Query.prototype, funcName, this.patchAndCaptureSpanContext(funcName));
    });
    this._wrap(moduleExports.Model, "aggregate", this.patchModelAggregate());
    this._wrap(moduleExports.Model, "insertMany", this.patchModelStatic("insertMany"));
    this._wrap(moduleExports.Model, "bulkWrite", this.patchModelStatic("bulkWrite"));
    return moduleExports;
  }
  unpatch(module, moduleVersion) {
    const moduleExports = module[Symbol.toStringTag] === "Module" && module.default ? module.default : module;
    const contextCaptureFunctions = getContextCaptureFunctions(moduleVersion);
    this._unwrap(moduleExports.Model.prototype, "save");
    moduleExports.Model.prototype.$save = moduleExports.Model.prototype.save;
    if (instrumentRemove(moduleVersion)) {
      this._unwrap(moduleExports.Model.prototype, "remove");
    }
    if (needsDocumentMethodPatch(moduleVersion)) {
      this._unwrap(moduleExports.Model.prototype, "updateOne");
      this._unwrap(moduleExports.Model.prototype, "deleteOne");
    }
    this._unwrap(moduleExports.Query.prototype, "exec");
    this._unwrap(moduleExports.Aggregate.prototype, "exec");
    contextCaptureFunctions.forEach((funcName) => {
      this._unwrap(moduleExports.Query.prototype, funcName);
    });
    this._unwrap(moduleExports.Model, "aggregate");
    this._unwrap(moduleExports.Model, "insertMany");
    this._unwrap(moduleExports.Model, "bulkWrite");
  }
  patchAggregateExec() {
    const self = this;
    return (originalAggregate) => {
      return function exec(callback) {
        const parentSpan = this[_STORED_PARENT_SPAN];
        const span = startMongooseLegacySpan({
          collection: this._model.collection,
          modelName: this._model?.modelName,
          operation: "aggregate",
          origin: ORIGIN,
          parentSpan
        });
        return self._handleResponse(span, originalAggregate, this, arguments, callback);
      };
    };
  }
  patchQueryExec() {
    const self = this;
    return (originalExec) => {
      return function exec(callback) {
        if (this[_ALREADY_INSTRUMENTED]) {
          return originalExec.apply(this, arguments);
        }
        const parentSpan = this[_STORED_PARENT_SPAN];
        const span = startMongooseLegacySpan({
          collection: this.mongooseCollection,
          modelName: this.model.modelName,
          operation: this.op,
          origin: ORIGIN,
          parentSpan
        });
        return self._handleResponse(span, originalExec, this, arguments, callback);
      };
    };
  }
  patchOnModelMethods(op) {
    const self = this;
    return (originalOnModelFunction) => {
      return function method(options, callback) {
        const span = startMongooseLegacySpan({
          collection: this.constructor.collection,
          modelName: this.constructor.modelName,
          operation: op,
          origin: ORIGIN
        });
        if (options instanceof Function) {
          callback = options;
        }
        return self._handleResponse(span, originalOnModelFunction, this, arguments, callback);
      };
    };
  }
  // Patch document instance methods (doc.updateOne/deleteOne) for Mongoose 8.21.0+.
  _patchDocumentUpdateMethods(op) {
    const self = this;
    return (originalMethod) => {
      return function method(update, options, callback) {
        let actualCallback = callback;
        if (typeof update === "function") {
          actualCallback = update;
        } else if (typeof options === "function") {
          actualCallback = options;
        }
        const span = startMongooseLegacySpan({
          collection: this.constructor.collection,
          modelName: this.constructor.modelName,
          operation: op,
          origin: ORIGIN
        });
        const result = self._handleResponse(span, originalMethod, this, arguments, actualCallback);
        if (result && typeof result === "object") {
          result[_ALREADY_INSTRUMENTED] = true;
        }
        return result;
      };
    };
  }
  patchModelStatic(op) {
    const self = this;
    return (original) => {
      return function patchedStatic(docsOrOps, options, callback) {
        if (typeof options === "function") {
          callback = options;
        }
        const span = startMongooseLegacySpan({
          collection: this.collection,
          modelName: this.modelName,
          operation: op,
          origin: ORIGIN
        });
        return self._handleResponse(span, original, this, arguments, callback);
      };
    };
  }
  // we want to capture the otel span on the object which is calling exec.
  // in the special case of aggregate, we need have no function to path
  // on the Aggregate object to capture the context on, so we patch
  // the aggregate of Model, and set the context on the Aggregate object
  patchModelAggregate() {
    return (original) => {
      return function captureSpanContext() {
        const currentSpan = getActiveSpan();
        const aggregate = original.apply(this, arguments);
        if (aggregate) aggregate[_STORED_PARENT_SPAN] = currentSpan;
        return aggregate;
      };
    };
  }
  patchAndCaptureSpanContext(_funcName) {
    return (original) => {
      return function captureSpanContext() {
        this[_STORED_PARENT_SPAN] = getActiveSpan();
        return original.apply(this, arguments);
      };
    };
  }
  _handleResponse(span, exec, originalThis, args, callback) {
    return withActiveSpan(span, () => {
      if (callback instanceof Function) {
        return handleCallbackResponse(callback, exec, originalThis, span, args);
      } else {
        const response = exec.apply(originalThis, args);
        return handlePromiseResponse(response, span);
      }
    });
  }
}

export { MongooseInstrumentation, _ALREADY_INSTRUMENTED, _STORED_PARENT_SPAN };
//# sourceMappingURL=mongoose.js.map
