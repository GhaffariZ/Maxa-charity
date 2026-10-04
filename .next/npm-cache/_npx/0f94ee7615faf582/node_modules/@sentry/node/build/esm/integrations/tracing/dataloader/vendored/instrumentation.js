import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { CACHE_KEY } from '@sentry/conventions/attributes';
import { SDK_VERSION, startSpan, SEMANTIC_ATTRIBUTE_SENTRY_OP, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SPAN_KIND } from '@sentry/core';

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
class DataloaderInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    return [
      new InstrumentationNodeModuleDefinition(
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
            if (isWrapped(dataloader.prototype[method])) {
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
      return startSpan(
        {
          name: getSpanName(this, "batch"),
          links: this._batch?.spanLinks,
          attributes: {
            [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("batch"),
            [CACHE_KEY]: getCacheKey(args[0])
          },
          onlyIfParent: true
        },
        () => batchLoadFn.apply(this, args)
      );
    };
  }
  _getPatchedConstructor(constructor) {
    const instrumentation = this;
    const prototype = constructor.prototype;
    if (!instrumentation.isEnabled()) {
      return constructor;
    }
    function PatchedDataloader(...args) {
      if (typeof args[0] === "function") {
        if (isWrapped(args[0])) {
          instrumentation._unwrap(args, 0);
        }
        args[0] = instrumentation._wrapBatchLoadFn(args[0]);
      }
      return constructor.apply(this, args);
    }
    PatchedDataloader.prototype = prototype;
    return PatchedDataloader;
  }
  _patchLoad(proto) {
    if (isWrapped(proto.load)) {
      this._unwrap(proto, "load");
    }
    this._wrap(proto, "load", this._getPatchedLoad.bind(this));
  }
  _getPatchedLoad(original) {
    return function patchedLoad(...args) {
      return startSpan(
        {
          name: getSpanName(this, "load"),
          kind: SPAN_KIND.CLIENT,
          attributes: {
            [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("load"),
            [CACHE_KEY]: getCacheKey(args[0])
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
    if (isWrapped(proto.loadMany)) {
      this._unwrap(proto, "loadMany");
    }
    this._wrap(proto, "loadMany", this._getPatchedLoadMany.bind(this));
  }
  _getPatchedLoadMany(original) {
    return function patchedLoadMany(...args) {
      return startSpan(
        {
          name: getSpanName(this, "loadMany"),
          kind: SPAN_KIND.CLIENT,
          attributes: {
            [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("loadMany"),
            [CACHE_KEY]: getCacheKey(args[0])
          },
          onlyIfParent: true
        },
        () => original.call(this, ...args)
      );
    };
  }
  _patchPrime(proto) {
    if (isWrapped(proto.prime)) {
      this._unwrap(proto, "prime");
    }
    this._wrap(proto, "prime", this._getPatchedPrime.bind(this));
  }
  _getPatchedPrime(original) {
    return function patchedPrime(...args) {
      return startSpan(
        {
          name: getSpanName(this, "prime"),
          kind: SPAN_KIND.CLIENT,
          attributes: {
            [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("prime")
          },
          onlyIfParent: true
        },
        () => original.call(this, ...args)
      );
    };
  }
  _patchClear(proto) {
    if (isWrapped(proto.clear)) {
      this._unwrap(proto, "clear");
    }
    this._wrap(proto, "clear", this._getPatchedClear.bind(this));
  }
  _getPatchedClear(original) {
    return function patchedClear(...args) {
      return startSpan(
        {
          name: getSpanName(this, "clear"),
          kind: SPAN_KIND.CLIENT,
          attributes: {
            [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("clear")
          },
          onlyIfParent: true
        },
        () => original.call(this, ...args)
      );
    };
  }
  _patchClearAll(proto) {
    if (isWrapped(proto.clearAll)) {
      this._unwrap(proto, "clearAll");
    }
    this._wrap(proto, "clearAll", this._getPatchedClearAll.bind(this));
  }
  _getPatchedClearAll(original) {
    return function patchedClearAll(...args) {
      return startSpan(
        {
          name: getSpanName(this, "clearAll"),
          kind: SPAN_KIND.CLIENT,
          attributes: {
            [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
            [SEMANTIC_ATTRIBUTE_SENTRY_OP]: getSpanOp("clearAll")
          },
          onlyIfParent: true
        },
        () => original.call(this, ...args)
      );
    };
  }
}

export { DataloaderInstrumentation };
//# sourceMappingURL=instrumentation.js.map
