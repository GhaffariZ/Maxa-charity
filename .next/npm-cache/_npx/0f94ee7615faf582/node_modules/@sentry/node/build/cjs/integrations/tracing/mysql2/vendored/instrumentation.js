Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const InstrumentationNodeModuleFile = require('../../InstrumentationNodeModuleFile.js');
const semconv = require('./semconv.js');
const utils = require('./utils.js');

const PACKAGE_NAME = "@sentry/instrumentation-mysql2";
const ORIGIN = "auto.db.otel.mysql2";
const supportedVersions = [">=1.4.2 <3.20.0"];
class MySQL2Instrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    let format;
    function setFormatFunction(moduleExports) {
      if (!format && moduleExports.format) {
        format = moduleExports.format;
      }
    }
    const patch = (ConnectionPrototype) => {
      if (instrumentation.isWrapped(ConnectionPrototype.query)) {
        this._unwrap(ConnectionPrototype, "query");
      }
      this._wrap(ConnectionPrototype, "query", this._patchQuery(format));
      if (instrumentation.isWrapped(ConnectionPrototype.execute)) {
        this._unwrap(ConnectionPrototype, "execute");
      }
      this._wrap(ConnectionPrototype, "execute", this._patchQuery(format));
    };
    const unpatch = (ConnectionPrototype) => {
      this._unwrap(ConnectionPrototype, "query");
      this._unwrap(ConnectionPrototype, "execute");
    };
    return [
      new instrumentation.InstrumentationNodeModuleDefinition(
        "mysql2",
        supportedVersions,
        (moduleExports) => {
          setFormatFunction(moduleExports);
          return moduleExports;
        },
        () => {
        },
        [
          new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
            "mysql2/promise.js",
            supportedVersions,
            (moduleExports) => {
              setFormatFunction(moduleExports);
              return moduleExports;
            },
            () => {
            }
          ),
          new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
            "mysql2/lib/connection.js",
            supportedVersions,
            (moduleExports) => {
              const ConnectionPrototype = utils.getConnectionPrototypeToInstrument(moduleExports);
              patch(ConnectionPrototype);
              return moduleExports;
            },
            (moduleExports) => {
              if (moduleExports === void 0) return;
              const ConnectionPrototype = utils.getConnectionPrototypeToInstrument(moduleExports);
              unpatch(ConnectionPrototype);
            }
          )
        ]
      )
    ];
  }
  _patchQuery(format) {
    const thisPlugin = this;
    return (originalQuery) => {
      return function query(query, _valuesOrCallback, _callback) {
        let values;
        if (Array.isArray(_valuesOrCallback)) {
          values = _valuesOrCallback;
        } else if (arguments[2]) {
          values = [_valuesOrCallback];
        }
        const attributes$1 = {
          ...utils.getConnectionAttributes(this.config),
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_MYSQL,
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_STATEMENT]: utils.getQueryText(query, format, values),
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        };
        const span = core.startInactiveSpan({
          name: utils.getSpanName(query),
          kind: core.SPAN_KIND.CLIENT,
          attributes: attributes$1
        });
        const endSpan = utils.once((err) => {
          if (err) {
            span.setStatus({ code: core.SPAN_STATUS_ERROR, message: err.message });
          }
          span.end();
        });
        if (arguments.length === 1) {
          if (typeof query.onResult === "function") {
            thisPlugin._wrap(query, "onResult", thisPlugin._patchCallbackQuery(endSpan));
          }
          const streamableQuery = originalQuery.apply(this, arguments);
          streamableQuery.once("error", (err) => {
            endSpan(err);
          }).once("result", () => {
            endSpan();
          });
          return streamableQuery;
        }
        if (typeof arguments[1] === "function") {
          thisPlugin._wrap(arguments, 1, thisPlugin._patchCallbackQuery(endSpan));
        } else if (typeof arguments[2] === "function") {
          thisPlugin._wrap(arguments, 2, thisPlugin._patchCallbackQuery(endSpan));
        }
        return originalQuery.apply(this, arguments);
      };
    };
  }
  _patchCallbackQuery(endSpan) {
    return (originalCallback) => {
      return function(...args) {
        endSpan(args[0]);
        return originalCallback(...args);
      };
    };
  }
}

exports.MySQL2Instrumentation = MySQL2Instrumentation;
//# sourceMappingURL=instrumentation.js.map
