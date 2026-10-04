Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const semconv = require('./semconv.js');
const utils = require('./utils.js');

const PACKAGE_NAME = "@sentry/instrumentation-mysql";
const ORIGIN = "auto.db.otel.mysql";
class MySQLInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    return [
      new instrumentation.InstrumentationNodeModuleDefinition(
        "mysql",
        [">=2.0.0 <3"],
        (moduleExports) => {
          if (instrumentation.isWrapped(moduleExports.createConnection)) {
            this._unwrap(moduleExports, "createConnection");
          }
          this._wrap(moduleExports, "createConnection", this._patchCreateConnection());
          if (instrumentation.isWrapped(moduleExports.createPool)) {
            this._unwrap(moduleExports, "createPool");
          }
          this._wrap(moduleExports, "createPool", this._patchCreatePool());
          if (instrumentation.isWrapped(moduleExports.createPoolCluster)) {
            this._unwrap(moduleExports, "createPoolCluster");
          }
          this._wrap(moduleExports, "createPoolCluster", this._patchCreatePoolCluster());
          return moduleExports;
        },
        (moduleExports) => {
          if (moduleExports === void 0) return;
          this._unwrap(moduleExports, "createConnection");
          this._unwrap(moduleExports, "createPool");
          this._unwrap(moduleExports, "createPoolCluster");
        }
      )
    ];
  }
  // global export function
  _patchCreateConnection() {
    return (originalCreateConnection) => {
      const thisPlugin = this;
      return function createConnection(_connectionUri) {
        const originalResult = originalCreateConnection(...arguments);
        thisPlugin._wrap(originalResult, "query", thisPlugin._patchQuery(originalResult));
        return originalResult;
      };
    };
  }
  // global export function
  _patchCreatePool() {
    return (originalCreatePool) => {
      const thisPlugin = this;
      return function createPool(_config) {
        const pool = originalCreatePool(...arguments);
        thisPlugin._wrap(pool, "query", thisPlugin._patchQuery(pool));
        thisPlugin._wrap(pool, "getConnection", thisPlugin._patchGetConnection(pool));
        return pool;
      };
    };
  }
  // global export function
  _patchCreatePoolCluster() {
    return (originalCreatePoolCluster) => {
      const thisPlugin = this;
      return function createPool(_config) {
        const cluster = originalCreatePoolCluster(...arguments);
        thisPlugin._wrap(cluster, "getConnection", thisPlugin._patchGetConnection(cluster));
        return cluster;
      };
    };
  }
  // method on cluster or pool
  _patchGetConnection(pool) {
    return (originalGetConnection) => {
      const thisPlugin = this;
      return function getConnection(arg1, arg2, arg3) {
        if (!thisPlugin["_enabled"]) {
          thisPlugin._unwrap(pool, "getConnection");
          return originalGetConnection.apply(pool, arguments);
        }
        if (arguments.length === 1 && typeof arg1 === "function") {
          const patchFn = thisPlugin._getConnectionCallbackPatchFn(arg1);
          return originalGetConnection.call(pool, patchFn);
        }
        if (arguments.length === 2 && typeof arg2 === "function") {
          const patchFn = thisPlugin._getConnectionCallbackPatchFn(arg2);
          return originalGetConnection.call(pool, arg1, patchFn);
        }
        if (arguments.length === 3 && typeof arg3 === "function") {
          const patchFn = thisPlugin._getConnectionCallbackPatchFn(arg3);
          return originalGetConnection.call(pool, arg1, arg2, patchFn);
        }
        return originalGetConnection.apply(pool, arguments);
      };
    };
  }
  _getConnectionCallbackPatchFn(cb) {
    const thisPlugin = this;
    const scope = core.getCurrentScope();
    return function(err, connection) {
      if (connection) {
        if (!instrumentation.isWrapped(connection.query)) {
          thisPlugin._wrap(connection, "query", thisPlugin._patchQuery(connection));
        }
      }
      if (typeof cb === "function") {
        core.withScope(scope, () => cb.call(this, err, connection));
      }
    };
  }
  _patchQuery(connection) {
    return (originalQuery) => {
      const thisPlugin = this;
      return function query(query, _valuesOrCallback, _callback) {
        if (!thisPlugin["_enabled"]) {
          thisPlugin._unwrap(connection, "query");
          return originalQuery.apply(connection, arguments);
        }
        const { host, port, database, user } = utils.getConfig(connection.config);
        const portNumber = parseInt(String(port), 10);
        const attributes$1 = {
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_MYSQL,
          [semconv.ATTR_DB_CONNECTION_STRING]: utils.getJDBCString(host, port, database),
          [attributes.DB_NAME]: database,
          [attributes.DB_USER]: user,
          [attributes.DB_STATEMENT]: utils.getDbQueryText(query),
          [attributes.NET_PEER_NAME]: host
        };
        if (!isNaN(portNumber)) {
          attributes$1[attributes.NET_PEER_PORT] = portNumber;
        }
        const span = core.startInactiveSpan({
          name: utils.getSpanName(query),
          kind: core.SPAN_KIND.CLIENT,
          attributes: attributes$1
        });
        const cbIndex = Array.from(arguments).findIndex((arg) => typeof arg === "function");
        const scope = core.getCurrentScope();
        if (cbIndex === -1) {
          const streamableQuery = core.withActiveSpan(span, () => {
            return originalQuery.apply(connection, arguments);
          });
          core.bindScopeToEmitter(streamableQuery, scope);
          return streamableQuery.on("error", (err) => {
            span.setStatus({
              code: core.SPAN_STATUS_ERROR,
              message: err.message
            });
          }).on("end", () => {
            span.end();
          });
        } else {
          thisPlugin._wrap(arguments, cbIndex, thisPlugin._patchCallbackQuery(span, scope));
          return core.withActiveSpan(span, () => {
            return originalQuery.apply(connection, arguments);
          });
        }
      };
    };
  }
  _patchCallbackQuery(span, scope) {
    return (originalCallback) => {
      return function(err, _results, _fields) {
        if (err) {
          span.setStatus({
            code: core.SPAN_STATUS_ERROR,
            message: err.message
          });
        }
        span.end();
        return core.withScope(scope, () => originalCallback(...arguments));
      };
    };
  }
}

exports.MySQLInstrumentation = MySQLInstrumentation;
//# sourceMappingURL=instrumentation.js.map
