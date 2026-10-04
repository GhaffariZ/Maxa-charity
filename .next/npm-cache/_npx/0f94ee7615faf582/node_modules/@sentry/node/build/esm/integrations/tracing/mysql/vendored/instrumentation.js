import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { SDK_VERSION, getCurrentScope, withScope, startInactiveSpan, SPAN_KIND, withActiveSpan, bindScopeToEmitter, SPAN_STATUS_ERROR, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { DB_STATEMENT, NET_PEER_NAME, DB_USER, DB_NAME, DB_SYSTEM, NET_PEER_PORT } from '@sentry/conventions/attributes';
import { DB_SYSTEM_VALUE_MYSQL, ATTR_DB_CONNECTION_STRING } from './semconv.js';
import { getConfig, getDbQueryText, getSpanName, getJDBCString } from './utils.js';

const PACKAGE_NAME = "@sentry/instrumentation-mysql";
const ORIGIN = "auto.db.otel.mysql";
class MySQLInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    return [
      new InstrumentationNodeModuleDefinition(
        "mysql",
        [">=2.0.0 <3"],
        (moduleExports) => {
          if (isWrapped(moduleExports.createConnection)) {
            this._unwrap(moduleExports, "createConnection");
          }
          this._wrap(moduleExports, "createConnection", this._patchCreateConnection());
          if (isWrapped(moduleExports.createPool)) {
            this._unwrap(moduleExports, "createPool");
          }
          this._wrap(moduleExports, "createPool", this._patchCreatePool());
          if (isWrapped(moduleExports.createPoolCluster)) {
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
    const scope = getCurrentScope();
    return function(err, connection) {
      if (connection) {
        if (!isWrapped(connection.query)) {
          thisPlugin._wrap(connection, "query", thisPlugin._patchQuery(connection));
        }
      }
      if (typeof cb === "function") {
        withScope(scope, () => cb.call(this, err, connection));
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
        const { host, port, database, user } = getConfig(connection.config);
        const portNumber = parseInt(String(port), 10);
        const attributes = {
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [DB_SYSTEM]: DB_SYSTEM_VALUE_MYSQL,
          [ATTR_DB_CONNECTION_STRING]: getJDBCString(host, port, database),
          [DB_NAME]: database,
          [DB_USER]: user,
          [DB_STATEMENT]: getDbQueryText(query),
          [NET_PEER_NAME]: host
        };
        if (!isNaN(portNumber)) {
          attributes[NET_PEER_PORT] = portNumber;
        }
        const span = startInactiveSpan({
          name: getSpanName(query),
          kind: SPAN_KIND.CLIENT,
          attributes
        });
        const cbIndex = Array.from(arguments).findIndex((arg) => typeof arg === "function");
        const scope = getCurrentScope();
        if (cbIndex === -1) {
          const streamableQuery = withActiveSpan(span, () => {
            return originalQuery.apply(connection, arguments);
          });
          bindScopeToEmitter(streamableQuery, scope);
          return streamableQuery.on("error", (err) => {
            span.setStatus({
              code: SPAN_STATUS_ERROR,
              message: err.message
            });
          }).on("end", () => {
            span.end();
          });
        } else {
          thisPlugin._wrap(arguments, cbIndex, thisPlugin._patchCallbackQuery(span, scope));
          return withActiveSpan(span, () => {
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
            code: SPAN_STATUS_ERROR,
            message: err.message
          });
        }
        span.end();
        return withScope(scope, () => originalCallback(...arguments));
      };
    };
  }
}

export { MySQLInstrumentation };
//# sourceMappingURL=instrumentation.js.map
