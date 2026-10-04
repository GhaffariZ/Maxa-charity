Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const events = require('events');
const instrumentation = require('@opentelemetry/instrumentation');
const attributes = require('@sentry/conventions/attributes');
const semconv = require('./semconv.js');
const utils = require('./utils.js');
const core = require('@sentry/core');

const PACKAGE_NAME = "@sentry/instrumentation-tedious";
const CURRENT_DATABASE = /* @__PURE__ */ Symbol("opentelemetry.instrumentation-tedious.current-database");
const PATCHED_METHODS = ["callProcedure", "execSql", "execSqlBatch", "execBulkLoad", "prepare", "execute"];
function setDatabase(databaseName) {
  Object.defineProperty(this, CURRENT_DATABASE, {
    value: databaseName,
    writable: true
  });
}
const _TediousInstrumentation = class _TediousInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    return [
      new instrumentation.InstrumentationNodeModuleDefinition(
        _TediousInstrumentation.COMPONENT,
        [">=1.11.0 <20"],
        (moduleExports) => {
          const ConnectionPrototype = moduleExports.Connection.prototype;
          for (const method of PATCHED_METHODS) {
            if (instrumentation.isWrapped(ConnectionPrototype[method])) {
              this._unwrap(ConnectionPrototype, method);
            }
            this._wrap(ConnectionPrototype, method, this._patchQuery(method));
          }
          if (instrumentation.isWrapped(ConnectionPrototype.connect)) {
            this._unwrap(ConnectionPrototype, "connect");
          }
          this._wrap(ConnectionPrototype, "connect", this._patchConnect);
          return moduleExports;
        },
        (moduleExports) => {
          if (moduleExports === void 0) return;
          const ConnectionPrototype = moduleExports.Connection.prototype;
          for (const method of PATCHED_METHODS) {
            this._unwrap(ConnectionPrototype, method);
          }
          this._unwrap(ConnectionPrototype, "connect");
        }
      )
    ];
  }
  _patchConnect(original) {
    return function patchedConnect() {
      setDatabase.call(this, this.config?.options?.database);
      this.removeListener("databaseChange", setDatabase);
      this.on("databaseChange", setDatabase);
      this.once("end", () => {
        this.removeListener("databaseChange", setDatabase);
      });
      return original.apply(this, arguments);
    };
  }
  _patchQuery(operation) {
    return (originalMethod) => {
      const thisPlugin = this;
      function patchedMethod(request) {
        if (!(request instanceof events.EventEmitter)) {
          thisPlugin._diag.warn(`Unexpected invocation of patched ${operation} method. Span not recorded`);
          return originalMethod.apply(this, arguments);
        }
        let procCount = 0;
        let statementCount = 0;
        const incrementStatementCount = () => statementCount++;
        const incrementProcCount = () => procCount++;
        const databaseName = this[CURRENT_DATABASE];
        const sql = ((request2) => {
          if (request2.sqlTextOrProcedure === "sp_prepare" && request2.parametersByName?.stmt?.value) {
            return request2.parametersByName.stmt.value;
          }
          return request2.sqlTextOrProcedure;
        })(request);
        const attributes$1 = {
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.db.otel.tedious",
          // eslint-disable-next-line typescript/no-deprecated
          [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_MSSQL,
          // eslint-disable-next-line typescript/no-deprecated
          [attributes.DB_NAME]: databaseName,
          // >=4 uses `authentication` object; older versions just userName and password pair
          // eslint-disable-next-line typescript/no-deprecated
          [attributes.DB_USER]: this.config?.userName ?? this.config?.authentication?.options?.userName,
          // eslint-disable-next-line typescript/no-deprecated
          [attributes.DB_STATEMENT]: sql,
          // eslint-disable-next-line typescript/no-deprecated
          [semconv.ATTR_DB_SQL_TABLE]: request.table,
          // eslint-disable-next-line typescript/no-deprecated
          [attributes.NET_PEER_NAME]: this.config?.server,
          // eslint-disable-next-line typescript/no-deprecated
          [attributes.NET_PEER_PORT]: this.config?.options?.port
        };
        const span = core.startInactiveSpan({
          name: utils.getSpanName(operation, databaseName, sql, request.table),
          kind: core.SPAN_KIND.CLIENT,
          attributes: attributes$1
        });
        const endSpan = utils.once((err) => {
          request.removeListener("done", incrementStatementCount);
          request.removeListener("doneInProc", incrementStatementCount);
          request.removeListener("doneProc", incrementProcCount);
          request.removeListener("error", endSpan);
          this.removeListener("end", endSpan);
          span.setAttribute("tedious.procedure_count", procCount);
          span.setAttribute("tedious.statement_count", statementCount);
          if (err) {
            span.setStatus({
              code: core.SPAN_STATUS_ERROR,
              message: err.message
            });
          }
          span.end();
        });
        request.on("done", incrementStatementCount);
        request.on("doneInProc", incrementStatementCount);
        request.on("doneProc", incrementProcCount);
        request.once("error", endSpan);
        this.on("end", endSpan);
        if (typeof request.callback === "function") {
          thisPlugin._wrap(request, "callback", thisPlugin._patchCallbackQuery(endSpan));
        } else {
          thisPlugin._diag.error("Expected request.callback to be a function");
        }
        return core.withActiveSpan(span, () => originalMethod.apply(this, arguments));
      }
      Object.defineProperty(patchedMethod, "length", {
        value: originalMethod.length,
        writable: false
      });
      return patchedMethod;
    };
  }
  _patchCallbackQuery(endSpan) {
    return (originalCallback) => {
      return function(err, _rowCount, _rows) {
        endSpan(err);
        return originalCallback.apply(this, arguments);
      };
    };
  }
};
_TediousInstrumentation.COMPONENT = "tedious";
let TediousInstrumentation = _TediousInstrumentation;

exports.TediousInstrumentation = TediousInstrumentation;
//# sourceMappingURL=instrumentation.js.map
