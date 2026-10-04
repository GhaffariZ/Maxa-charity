import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { SDK_VERSION, getActiveSpan, startSpan, SPAN_KIND, SPAN_STATUS_ERROR, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { DB_STATEMENT, NET_TRANSPORT, NET_PEER_PORT, NET_PEER_NAME, DB_USER, DB_SYSTEM, DB_NAME, DB_OPERATION } from '@sentry/conventions/attributes';
import { InstrumentationNodeModuleFile } from '../../InstrumentationNodeModuleFile.js';
import { ATTR_DB_SQL_TABLE } from './semconv.js';
import { extractTableName, extractDatabaseFromConnectionString, limitLength, extractPortFromConnectionString, extractHostFromConnectionString, mapSystem, getName, getFormatter } from './utils.js';

const PACKAGE_NAME = "@sentry/instrumentation-knex";
const ORIGIN = "auto.db.otel.knex";
const MODULE_NAME = "knex";
const SUPPORTED_VERSIONS = [
  // use "lib/execution" for runner.js, "lib" for client.js as basepath, latest tested 0.95.6
  ">=0.22.0 <4",
  // use "lib" as basepath
  ">=0.10.0 <0.18.0",
  ">=0.19.0 <0.22.0",
  // use "src" as basepath
  ">=0.18.0 <0.19.0"
];
const MAX_QUERY_LENGTH = 1022;
const parentSpanSymbol = /* @__PURE__ */ Symbol("sentry.instrumentation-knex.parent-span");
class KnexInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    const module = new InstrumentationNodeModuleDefinition(MODULE_NAME, SUPPORTED_VERSIONS);
    module.files.push(
      this._getClientNodeModuleFileInstrumentation("src"),
      this._getClientNodeModuleFileInstrumentation("lib"),
      this._getRunnerNodeModuleFileInstrumentation("src"),
      this._getRunnerNodeModuleFileInstrumentation("lib"),
      this._getRunnerNodeModuleFileInstrumentation("lib/execution")
    );
    return module;
  }
  _getRunnerNodeModuleFileInstrumentation(basePath) {
    return new InstrumentationNodeModuleFile(
      `knex/${basePath}/runner.js`,
      SUPPORTED_VERSIONS,
      (Runner, moduleVersion) => {
        this._ensureWrapped(Runner.prototype, "query", this._createQueryWrapper(moduleVersion));
        return Runner;
      },
      (Runner) => {
        this._unwrap(Runner.prototype, "query");
        return Runner;
      }
    );
  }
  _getClientNodeModuleFileInstrumentation(basePath) {
    return new InstrumentationNodeModuleFile(
      `knex/${basePath}/client.js`,
      SUPPORTED_VERSIONS,
      (Client) => {
        this._ensureWrapped(Client.prototype, "queryBuilder", this._storeContext.bind(this));
        this._ensureWrapped(Client.prototype, "schemaBuilder", this._storeContext.bind(this));
        this._ensureWrapped(Client.prototype, "raw", this._storeContext.bind(this));
        return Client;
      },
      (Client) => {
        this._unwrap(Client.prototype, "queryBuilder");
        this._unwrap(Client.prototype, "schemaBuilder");
        this._unwrap(Client.prototype, "raw");
        return Client;
      }
    );
  }
  _createQueryWrapper(moduleVersion) {
    return function wrapQuery(original) {
      return function wrapped_logging_method(query) {
        const config = this.client.config;
        const table = extractTableName(this.builder);
        const operation = query?.method;
        const connectionString = config?.connection?.connectionString;
        const name = config?.connection?.filename || config?.connection?.database || extractDatabaseFromConnectionString(connectionString);
        const attributes = {
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          "knex.version": moduleVersion,
          [DB_SYSTEM]: mapSystem(this.client.driverName),
          [ATTR_DB_SQL_TABLE]: table,
          [DB_OPERATION]: operation,
          [DB_USER]: config?.connection?.user,
          [DB_NAME]: name,
          [NET_PEER_NAME]: config?.connection?.host ?? extractHostFromConnectionString(connectionString),
          [NET_PEER_PORT]: config?.connection?.port ?? extractPortFromConnectionString(connectionString),
          [NET_TRANSPORT]: config?.connection?.filename === ":memory:" ? "inproc" : void 0,
          [DB_STATEMENT]: limitLength(query?.sql, MAX_QUERY_LENGTH)
        };
        const parentSpan = this.builder[parentSpanSymbol] || getActiveSpan();
        const args = arguments;
        return startSpan(
          {
            name: getName(name, operation, table),
            kind: SPAN_KIND.CLIENT,
            attributes,
            parentSpan,
            onlyIfParent: true
          },
          (span) => (
            // `Runner.query` returns a real, already-executing Promise, so it is safe to let
            // `startSpan` await it and auto-end the span.
            original.apply(this, args).catch((err) => {
              const formatter = getFormatter(this);
              const fullQuery = formatter(query.sql, query.bindings || []);
              const message = err.message.replace(`${fullQuery} - `, "");
              span.setStatus({ code: SPAN_STATUS_ERROR, message });
              throw err;
            })
          )
        );
      };
    };
  }
  _storeContext(original) {
    return function wrapped_logging_method() {
      const builder = original.apply(this, arguments);
      Object.defineProperty(builder, parentSpanSymbol, {
        value: getActiveSpan()
      });
      return builder;
    };
  }
  _ensureWrapped(obj, methodName, wrapper) {
    if (isWrapped(obj[methodName])) {
      this._unwrap(obj, methodName);
    }
    this._wrap(obj, methodName, wrapper);
  }
}

export { KnexInstrumentation };
//# sourceMappingURL=instrumentation.js.map
