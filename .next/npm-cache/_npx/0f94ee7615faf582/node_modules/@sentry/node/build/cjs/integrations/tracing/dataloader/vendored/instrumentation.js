Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');

const MODULE_NAME = "dataloader";
const PACKAGE_NAME = "@sentry/instrumentation-dataloader";
const ORIGIN = "auto.db.otel.dataloader";
function isModule(module) {
  return module[Symbol.toStringTag] === "Module";
}
function extractModuleExports(module) {
  return isModule(module) ? module.default : module;
}
function getSpanName(dataloader, operation) {
  const dataloaderName = dataloader.name;
  if (dataloaderName) {
    return `${MODULE_NAME}.${operation} ${dataloaderName}`;
  }
  return `${MODULE_NAME}.${operation}`;
}
function getSpanOp(operation) {
  if (operation === "load" || operation === "loadMany" || operation === "batch") {
    return "cache.get";
  }
  return void 0;
}
function getCacheKey(keyArg) {
  if (Array.isArray(keyArg)) {
    return keyArg.map((key) => String(key));
  }
  return keyArg == null ? void 0 : [String(keyArg)];
}
class DataloaderInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    return [
      new instrumentation.InstrumentationNodeModuleDefinition(
        MODULE_NAME,
        [">=2.0.0 <3"],
        (module) => {
          const dataloader = extractModuleExports(module);
          this._patchLoad(dataloader.prototype);
          this._patchLoadMany(dataloader.prototype);
          this._patchPrime(dataloader.prototype);
          this._patchClear(dataloader.prototype);
          this._patchClearAll(dataloader.prototype);
          return this._getPatchedConstructor(dataloader);
        },
        (module) => {
          const dataloader = extractModuleExports(module);
          ["load", "loadMany", "prime", "clear", "clearAll"].forEach((method) => {
            if (instrumentation.isWrapped(dataloader.prototype[method])) {
              this._unwrap(dataloader.prototype, method);
            }
          });
        }
      )
    ];
  }
  _wrapBatchLoadFn(batchLoadFn) {
    const instrumentation = this;
    return function patchedBatchLoadFn(...args) {
      if (!instrumentation.isEnabled()) {
        return batchLoadFn.call(this, ...args);
      }
      return core.startSpan(
        {
          name: getSpanName(this, "batch"),
          links: this._batch?.spanLinks,
          attributes: {
            [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("batch"),
            [attributes.CACHE_KEY]: getCacheKey(args[0])
          },
          onlyIfParent: true
        },
        () => batchLoadFn.apply(this, args)
      );
    };
  }
  _getPatchedConstructor(constructor) {
    const instrumentation$1 = this;
    const prototype = constructor.prototype;
    if (!instrumentation$1.isEnabled()) {
      return constructor;
    }
    function PatchedDataloader(...args) {
      if (typeof args[0] === "function") {
        if (instrumentation.isWrapped(args[0])) {
          instrumentation$1._unwrap(args, 0);
        }
        args[0] = instrumentation$1._wrapBatchLoadFn(args[0]);
      }
      return constructor.apply(this, args);
    }
    PatchedDataloader.prototype = prototype;
    return PatchedDataloader;
  }
  _patchLoad(proto) {
    if (instrumentation.isWrapped(proto.load)) {
      this._unwrap(proto, "load");
    }
    this._wrap(proto, "load", this._getPatchedLoad.bind(this));
  }
  _getPatchedLoad(original) {
    return function patchedLoad(...args) {
      return core.startSpan(
        {
          name: getSpanName(this, "load"),
          kind: core.SPAN_KIND.CLIENT,
          attributes: {
            [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("load"),
            [attributes.CACHE_KEY]: getCacheKey(args[0])
          },
          onlyIfParent: true
        },
        (span) => {
          const result = original.call(this, ...args);
          if (this._batch && span.isRecording()) {
            if (!this._batch.spanLinks) {
              this._batch.spanLinks = [];
            }
            this._batch.spanLinks.push({ context: span.spanContext() });
          }
          return result;
        }
      );
    };
  }
  _patchLoadMany(proto) {
    if (instrumentation.isWrapped(proto.loadMany)) {
      this._unwrap(proto, "loadMany");
    }
    this._wrap(proto, "loadMany", this._getPatchedLoadMany.bind(this));
  }
  _getPatchedLoadMany(original) {
    return function patchedLoadMany(...args) {
      return core.startSpan(
        {
          name: getSpanName(this, "loadMany"),
          kind: core.SPAN_KIND.CLIENT,
          attributes: {
            [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("loadMany"),
            [attributes.CACHE_KEY]: getCacheKey(args[0])
          },
          onlyIfParent: true
        },
        () => original.call(this, ...args)
      );
    };
  }
  _patchPrime(proto) {
    if (instrumentation.isWrapped(proto.prime)) {
      this._unwrap(proto, "prime");
    }
    this._wrap(proto, "prime", this._getPatchedPrime.bind(this));
  }
  _getPatchedPrime(original) {
    return function patchedPrime(...args) {
      return core.startSpan(
        {
          name: getSpanName(this, "prime"),
          kind: core.SPAN_KIND.CLIENT,
          attributes: {
            [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("prime")
          },
          onlyIfParent: true
        },
        () => original.call(this, ...args)
      );
    };
  }
  _patchClear(proto) {
    if (instrumentation.isWrapped(proto.clear)) {
      this._unwrap(proto, "clear");
    }
    this._wrap(proto, "clear", this._getPatchedClear.bind(this));
  }
  _getPatchedClear(original) {
    return function patchedClear(...args) {
      return core.startSpan(
        {
          name: getSpanName(this, "clear"),
          kind: core.SPAN_KIND.CLIENT,
          attributes: {
            [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("clear")
          },
          onlyIfParent: true
        },
        () => original.call(this, ...args)
      );
    };
  }
  _patchClearAll(proto) {
    if (instrumentation.isWrapped(proto.clearAll)) {
      this._unwrap(proto, "clearAll");
    }
    this._wrap(proto, "clearAll", this._getPatchedClearAll.bind(this));
  }
  _getPatchedClearAll(original) {
    return function patchedClearAll(...args) {
      return core.startSpan(
        {
          name: getSpanName(this, "clearAll"),
          kind: core.SPAN_KIND.CLIENT,
          attributes: {
            [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("clearAll")
          },
          onlyIfParent: true
        },
        () => original.call(this, ...args)
      );
    };
  }
}

exports.DataloaderInstrumentation = DataloaderInstrumentation;
//# sourceMappingURL=instrumentation.js.map
