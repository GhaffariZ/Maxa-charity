import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { SDK_VERSION } from '@sentry/core';
import { InstrumentationNodeModuleFile } from '../../InstrumentationNodeModuleFile.js';
import { getV3PatchOperation, getV3PatchCommand, getV3PatchFind, getV3PatchCursor, getV4ConnectionPoolCheckOut, getV4PatchCommandPromise, getV4PatchCommandCallback } from './patches.js';

const PACKAGE_NAME = "@sentry/instrumentation-mongodb";
class MongoDBInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    const { v3PatchConnection, v3UnpatchConnection } = this._getV3ConnectionPatches();
    const { v4PatchConnectionCallback, v4PatchConnectionPromise, v4UnpatchConnection } = this._getV4ConnectionPatches();
    const { v4PatchConnectionPool, v4UnpatchConnectionPool } = this._getV4ConnectionPoolPatches();
    return [
      new InstrumentationNodeModuleDefinition("mongodb", [">=3.3.0 <4"], void 0, void 0, [
        new InstrumentationNodeModuleFile(
          "mongodb/lib/core/wireprotocol/index.js",
          [">=3.3.0 <4"],
          v3PatchConnection,
          v3UnpatchConnection
        )
      ]),
      new InstrumentationNodeModuleDefinition("mongodb", [">=4.0.0 <8"], void 0, void 0, [
        new InstrumentationNodeModuleFile(
          "mongodb/lib/cmap/connection.js",
          [">=4.0.0 <6.4"],
          v4PatchConnectionCallback,
          v4UnpatchConnection
        ),
        new InstrumentationNodeModuleFile(
          "mongodb/lib/cmap/connection.js",
          [">=6.4.0 <8"],
          v4PatchConnectionPromise,
          v4UnpatchConnection
        ),
        new InstrumentationNodeModuleFile(
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
        if (isWrapped(moduleExports.insert)) {
          this._unwrap(moduleExports, "insert");
        }
        this._wrap(moduleExports, "insert", getV3PatchOperation("insert"));
        if (isWrapped(moduleExports.remove)) {
          this._unwrap(moduleExports, "remove");
        }
        this._wrap(moduleExports, "remove", getV3PatchOperation("remove"));
        if (isWrapped(moduleExports.update)) {
          this._unwrap(moduleExports, "update");
        }
        this._wrap(moduleExports, "update", getV3PatchOperation("update"));
        if (isWrapped(moduleExports.command)) {
          this._unwrap(moduleExports, "command");
        }
        this._wrap(moduleExports, "command", getV3PatchCommand());
        if (isWrapped(moduleExports.query)) {
          this._unwrap(moduleExports, "query");
        }
        this._wrap(moduleExports, "query", getV3PatchFind());
        if (isWrapped(moduleExports.getMore)) {
          this._unwrap(moduleExports, "getMore");
        }
        this._wrap(moduleExports, "getMore", getV3PatchCursor());
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
        if (isWrapped(poolPrototype.checkOut)) {
          this._unwrap(poolPrototype, "checkOut");
        }
        this._wrap(poolPrototype, "checkOut", getV4ConnectionPoolCheckOut());
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
        if (isWrapped(moduleExports.Connection.prototype.command)) {
          this._unwrap(moduleExports.Connection.prototype, "command");
        }
        this._wrap(moduleExports.Connection.prototype, "command", getV4PatchCommandCallback());
        return moduleExports;
      },
      v4PatchConnectionPromise: (moduleExports) => {
        if (isWrapped(moduleExports.Connection.prototype.command)) {
          this._unwrap(moduleExports.Connection.prototype, "command");
        }
        this._wrap(moduleExports.Connection.prototype, "command", getV4PatchCommandPromise());
        return moduleExports;
      },
      v4UnpatchConnection: (moduleExports) => {
        if (moduleExports === void 0) return;
        this._unwrap(moduleExports.Connection.prototype, "command");
      }
    };
  }
}

export { MongoDBInstrumentation };
//# sourceMappingURL=instrumentation.js.map
