Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const InstrumentationNodeModuleFile = require('../../InstrumentationNodeModuleFile.js');
const semconv = require('./semconv.js');
const utils = require('./utils.js');

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
class KnexInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    const module = new instrumentation.InstrumentationNodeModuleDefinition(MODULE_NAME, SUPPORTED_VERSIONS);
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
    return new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
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
    return new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
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
        const table = utils.extractTableName(this.builder);
        const operation = query?.method;
        const connectionString = config?.connection?.connectionString;
        const name = config?.connection?.filename || config?.connection?.database || utils.extractDatabaseFromConnectionString(connectionString);
        const attributes$1 = {
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          "knex.version": moduleVersion,
          [attributes.DB_SYSTEM]: utils.mapSystem(this.client.driverName),
          [semconv.ATTR_DB_SQL_TABLE]: table,
          [attributes.DB_OPERATION]: operation,
          [attributes.DB_USER]: config?.connection?.user,
          [attributes.DB_NAME]: name,
          [attributes.NET_PEER_NAME]: config?.connection?.host ?? utils.extractHostFromConnectionString(connectionString),
          [attributes.NET_PEER_PORT]: config?.connection?.port ?? utils.extractPortFromConnectionString(connectionString),
          [attributes.NET_TRANSPORT]: config?.connection?.filename === ":memory:" ? "inproc" : void 0,
          [attributes.DB_STATEMENT]: utils.limitLength(query?.sql, MAX_QUERY_LENGTH)
        };
        const parentSpan = this.builder[parentSpanSymbol] || core.getActiveSpan();
        const args = arguments;
        return core.startSpan(
          {
            name: utils.getName(name, operation, table),
            kind: core.SPAN_KIND.CLIENT,
            attributes: attributes$1,
            parentSpan,
            onlyIfParent: true
          },
          (span) => (
            // `Runner.query` returns a real, already-executing Promise, so it is safe to let
            // `startSpan` await it and auto-end the span.
            original.apply(this, args).catch((err) => {
              const formatter = utils.getFormatter(this);
              const fullQuery = formatter(query.sql, query.bindings || []);
              const message = err.message.replace(`${fullQuery} - `, "");
              span.setStatus({ code: core.SPAN_STATUS_ERROR, message });
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
        value: core.getActiveSpan()
      });
      return builder;
    };
  }
  _ensureWrapped(obj, methodName, wrapper) {
    if (instrumentation.isWrapped(obj[methodName])) {
      this._unwrap(obj, methodName);
    }
    this._wrap(obj, methodName, wrapper);
  }
}

exports.KnexInstrumentation = KnexInstrumentation;
//# sourceMappingURL=instrumentation.js.map
