import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { DB_STATEMENT, DB_SYSTEM } from '@sentry/conventions/attributes';
import { SDK_VERSION, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SPAN_STATUS_ERROR } from '@sentry/core';
import { InstrumentationNodeModuleFile } from '../../InstrumentationNodeModuleFile.js';
import { DB_SYSTEM_VALUE_MYSQL } from './semconv.js';
import { getConnectionPrototypeToInstrument, getQueryText, getConnectionAttributes, getSpanName, once } from './utils.js';

const PACKAGE_NAME = "@sentry/instrumentation-mysql2";
const ORIGIN = "auto.db.otel.mysql2";
const supportedVersions = [">=1.4.2 <3.20.0"];
class MySQL2Instrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    let format;
    function setFormatFunction(moduleExports) {
      if (!format && moduleExports.format) {
        format = moduleExports.format;
      }
    }
    const patch = (ConnectionPrototype) => {
      if (isWrapped(ConnectionPrototype.query)) {
        this._unwrap(ConnectionPrototype, "query");
      }
      this._wrap(ConnectionPrototype, "query", this._patchQuery(format));
      if (isWrapped(ConnectionPrototype.execute)) {
        this._unwrap(ConnectionPrototype, "execute");
      }
      this._wrap(ConnectionPrototype, "execute", this._patchQuery(format));
    };
    const unpatch = (ConnectionPrototype) => {
      this._unwrap(ConnectionPrototype, "query");
      this._unwrap(ConnectionPrototype, "execute");
    };
    return [
      new InstrumentationNodeModuleDefinition(
        "mysql2",
        supportedVersions,
        (moduleExports) => {
          setFormatFunction(moduleExports);
          return moduleExports;
        },
        () => {
        },
        [
          new InstrumentationNodeModuleFile(
            "mysql2/promise.js",
            supportedVersions,
            (moduleExports) => {
              setFormatFunction(moduleExports);
              return moduleExports;
            },
            () => {
            }
          ),
          new InstrumentationNodeModuleFile(
            "mysql2/lib/connection.js",
            supportedVersions,
            (moduleExports) => {
              const ConnectionPrototype = getConnectionPrototypeToInstrument(moduleExports);
              patch(ConnectionPrototype);
              return moduleExports;
            },
            (moduleExports) => {
              if (moduleExports === void 0) return;
              const ConnectionPrototype = getConnectionPrototypeToInstrument(moduleExports);
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
        const attributes = {
          ...getConnectionAttributes(this.config),
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_SYSTEM]: DB_SYSTEM_VALUE_MYSQL,
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_STATEMENT]: getQueryText(query, format, values),
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        };
        const span = startInactiveSpan({
          name: getSpanName(query),
          kind: SPAN_KIND.CLIENT,
          attributes
        });
        const endSpan = once((err) => {
          if (err) {
            span.setStatus({ code: SPAN_STATUS_ERROR, message: err.message });
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

export { MySQL2Instrumentation };
//# sourceMappingURL=instrumentation.js.map
