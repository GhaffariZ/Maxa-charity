Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const InstrumentationNodeModuleFile = require('../../InstrumentationNodeModuleFile.js');
const patches = require('./patches.js');

const PACKAGE_NAME = "@sentry/instrumentation-mongodb";
class MongoDBInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    const { v3PatchConnection, v3UnpatchConnection } = this._getV3ConnectionPatches();
    const { v4PatchConnectionCallback, v4PatchConnectionPromise, v4UnpatchConnection } = this._getV4ConnectionPatches();
    const { v4PatchConnectionPool, v4UnpatchConnectionPool } = this._getV4ConnectionPoolPatches();
    return [
      new instrumentation.InstrumentationNodeModuleDefinition("mongodb", [">=3.3.0 <4"], void 0, void 0, [
        new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
          "mongodb/lib/core/wireprotocol/index.js",
          [">=3.3.0 <4"],
          v3PatchConnection,
          v3UnpatchConnection
        )
      ]),
      new instrumentation.InstrumentationNodeModuleDefinition("mongodb", [">=4.0.0 <8"], void 0, void 0, [
        new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
          "mongodb/lib/cmap/connection.js",
          [">=4.0.0 <6.4"],
          v4PatchConnectionCallback,
          v4UnpatchConnection
        ),
        new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
          "mongodb/lib/cmap/connection.js",
          [">=6.4.0 <8"],
          v4PatchConnectionPromise,
          v4UnpatchConnection
        ),
        new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
          "mongodb/lib/cmap/connection_pool.js",
          [">=4.0.0 <6.4"],
          v4PatchConnectionPool,
          v4UnpatchConnectionPool
        )
      ])
    ];
  }
  _getV3ConnectionPatches() {
    return {
      v3PatchConnection: (moduleExports) => {
        if (instrumentation.isWrapped(moduleExports.insert)) {
          this._unwrap(moduleExports, "insert");
        }
        this._wrap(moduleExports, "insert", patches.getV3PatchOperation("insert"));
        if (instrumentation.isWrapped(moduleExports.remove)) {
          this._unwrap(moduleExports, "remove");
        }
        this._wrap(moduleExports, "remove", patches.getV3PatchOperation("remove"));
        if (instrumentation.isWrapped(moduleExports.update)) {
          this._unwrap(moduleExports, "update");
        }
        this._wrap(moduleExports, "update", patches.getV3PatchOperation("update"));
        if (instrumentation.isWrapped(moduleExports.command)) {
          this._unwrap(moduleExports, "command");
        }
        this._wrap(moduleExports, "command", patches.getV3PatchCommand());
        if (instrumentation.isWrapped(moduleExports.query)) {
          this._unwrap(moduleExports, "query");
        }
        this._wrap(moduleExports, "query", patches.getV3PatchFind());
        if (instrumentation.isWrapped(moduleExports.getMore)) {
          this._unwrap(moduleExports, "getMore");
        }
        this._wrap(moduleExports, "getMore", patches.getV3PatchCursor());
        return moduleExports;
      },
      v3UnpatchConnection: (moduleExports) => {
        if (moduleExports === void 0) return;
        this._unwrap(moduleExports, "insert");
        this._unwrap(moduleExports, "remove");
        this._unwrap(moduleExports, "update");
        this._unwrap(moduleExports, "command");
        this._unwrap(moduleExports, "query");
        this._unwrap(moduleExports, "getMore");
      }
    };
  }
  _getV4ConnectionPoolPatches() {
    return {
      v4PatchConnectionPool: (moduleExports) => {
        const poolPrototype = moduleExports.ConnectionPool.prototype;
        if (instrumentation.isWrapped(poolPrototype.checkOut)) {
          this._unwrap(poolPrototype, "checkOut");
        }
        this._wrap(poolPrototype, "checkOut", patches.getV4ConnectionPoolCheckOut());
        return moduleExports;
      },
      v4UnpatchConnectionPool: (moduleExports) => {
        if (moduleExports === void 0) return;
        this._unwrap(moduleExports.ConnectionPool.prototype, "checkOut");
      }
    };
  }
  _getV4ConnectionPatches() {
    return {
      v4PatchConnectionCallback: (moduleExports) => {
        if (instrumentation.isWrapped(moduleExports.Connection.prototype.command)) {
          this._unwrap(moduleExports.Connection.prototype, "command");
        }
        this._wrap(moduleExports.Connection.prototype, "command", patches.getV4PatchCommandCallback());
        return moduleExports;
      },
      v4PatchConnectionPromise: (moduleExports) => {
        if (instrumentation.isWrapped(moduleExports.Connection.prototype.command)) {
          this._unwrap(moduleExports.Connection.prototype, "command");
        }
        this._wrap(moduleExports.Connection.prototype, "command", patches.getV4PatchCommandPromise());
        return moduleExports;
      },
      v4UnpatchConnection: (moduleExports) => {
        if (moduleExports === void 0) return;
        this._unwrap(moduleExports.Connection.prototype, "command");
      }
    };
  }
}

exports.MongoDBInstrumentation = MongoDBInstrumentation;
//# sourceMappingURL=instrumentation.js.map
