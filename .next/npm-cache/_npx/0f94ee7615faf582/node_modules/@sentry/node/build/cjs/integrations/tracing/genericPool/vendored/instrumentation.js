Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');

const MODULE_NAME = "generic-pool";
const PACKAGE_NAME = "@sentry/instrumentation-generic-pool";
class GenericPoolInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
    // only used for v2 - v2.3)
    this._isDisabled = false;
  }
  init() {
    return [
      new instrumentation.InstrumentationNodeModuleDefinition(
        MODULE_NAME,
        [">=3.0.0 <4"],
        (moduleExports) => {
          const Pool = moduleExports.Pool;
          if (instrumentation.isWrapped(Pool.prototype.acquire)) {
            this._unwrap(Pool.prototype, "acquire");
          }
          this._wrap(Pool.prototype, "acquire", this._acquirePatcher.bind(this));
          return moduleExports;
        },
        (moduleExports) => {
          const Pool = moduleExports.Pool;
          this._unwrap(Pool.prototype, "acquire");
          return moduleExports;
        }
      ),
      new instrumentation.InstrumentationNodeModuleDefinition(
        MODULE_NAME,
        [">=2.4.0 <3"],
        (moduleExports) => {
          const Pool = moduleExports.Pool;
          if (instrumentation.isWrapped(Pool.prototype.acquire)) {
            this._unwrap(Pool.prototype, "acquire");
          }
          this._wrap(Pool.prototype, "acquire", this._acquireWithCallbacksPatcher.bind(this));
          return moduleExports;
        },
        (moduleExports) => {
          const Pool = moduleExports.Pool;
          this._unwrap(Pool.prototype, "acquire");
          return moduleExports;
        }
      ),
      new instrumentation.InstrumentationNodeModuleDefinition(
        MODULE_NAME,
        [">=2.0.0 <2.4"],
        (moduleExports) => {
          this._isDisabled = false;
          if (instrumentation.isWrapped(moduleExports.Pool)) {
            this._unwrap(moduleExports, "Pool");
          }
          this._wrap(moduleExports, "Pool", this._poolWrapper.bind(this));
          return moduleExports;
        },
        (moduleExports) => {
          this._isDisabled = true;
          return moduleExports;
        }
      )
    ];
  }
  _acquirePatcher(original) {
    return function wrapped_acquire(...args) {
      return core.startSpan(
        {
          name: "generic-pool.acquire",
          attributes: { [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.db.otel.generic_pool" }
        },
        () => {
          return original.call(this, ...args);
        }
      );
    };
  }
  _poolWrapper(original) {
    const wrap = this._wrap.bind(this);
    const acquireWithCallbacksPatcher = this._acquireWithCallbacksPatcher.bind(this);
    return function wrapped_pool(...args) {
      const pool = original.apply(this, args);
      wrap(pool, "acquire", acquireWithCallbacksPatcher);
      return pool;
    };
  }
  _acquireWithCallbacksPatcher(original) {
    const isDisabled = () => this._isDisabled;
    return function wrapped_acquire(cb, priority) {
      if (isDisabled()) {
        return original.call(this, cb, priority);
      }
      return core.startSpanManual(
        {
          name: "generic-pool.acquire",
          attributes: { [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.db.otel.generic_pool" }
        },
        (span) => {
          original.call(
            this,
            (err, client) => {
              if (err) {
                span.setStatus({ code: core.SPAN_STATUS_ERROR, message: "internal_error" });
              }
              span.end();
              if (cb) {
                cb(err, client);
              }
            },
            priority
          );
        }
      );
    };
  }
}

exports.GenericPoolInstrumentation = GenericPoolInstrumentation;
//# sourceMappingURL=instrumentation.js.map
