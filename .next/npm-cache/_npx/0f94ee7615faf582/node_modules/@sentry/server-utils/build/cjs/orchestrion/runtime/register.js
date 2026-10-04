Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const require$$1 = require('node:module');
const require$$3 = require('node:url');
const node_worker_threads = require('node:worker_threads');
const index = require('../config/index.js');
const index$1 = require('../../vendored/@apm-js-collab/tracing-hooks/index.js');
const hook = require('../../vendored/@apm-js-collab/tracing-hooks/hook.js');
const diagnostics = require('../../vendored/@apm-js-collab/tracing-hooks/lib/diagnostics.js');

function _interopNamespaceDefault(e) {
  const n = Object.create(null, { [Symbol.toStringTag]: { value: 'Module' } });
  if (e) {
    for (const k in e) {
      n[k] = e[k];
    }
  }
  n.default = e;
  return n;
}

const require$$1__namespace = /*#__PURE__*/_interopNamespaceDefault(require$$1);

function hasStableSyncModuleHooks(denoVersionString) {
  if (denoVersionString) {
    const { major: major2 = 0, minor: minor2 = 0 } = core.parseSemver(denoVersionString);
    return major2 > 2 || major2 === 2 && minor2 >= 8;
  }
  const { major = 0, minor = 0 } = core.parseSemver(process.versions.node ?? "0.0.0");
  return major > 25 || major === 25 && minor >= 1 || major === 24 && minor >= 13;
}
function registerDiagnosticsChannelInjection(_options) {
  if (!node_worker_threads.isMainThread && !node_worker_threads.parentPort) {
    return;
  }
  if (core.GLOBAL_OBJ?.__SENTRY_ORCHESTRION__?.runtime) {
    return;
  }
  const globalAny = globalThis;
  const stableSyncHooks = hasStableSyncModuleHooks(globalAny.Deno?.version?.deno);
  const mod = require$$1__namespace;
  diagnostics.diagnostics.setDiagnosticsHook(({ moduleName, error }) => {
    if (error) {
      core.debug.warn(`[orchestrion] failed to inject diagnostics-channel into ${moduleName}:`, error);
    } else {
      core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__ = core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__ || {};
      core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime = core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime || [];
      core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime.push(moduleName);
    }
  });
  try {
    if (typeof mod.registerHooks === "function" && stableSyncHooks) {
      hook.initializeSync({ instrumentations: index.SENTRY_INSTRUMENTATIONS });
      mod.registerHooks({ resolve: hook.resolveSync, load: hook.loadSync });
      core.debug.log("Registered diagnostics-channel injection via Module.registerHooks()");
    } else if (typeof mod.register === "function" && !globalAny.Bun && !globalAny.Deno) {
      const diagnosticsPort = hook.createDiagnosticsPort();
      let parentURL;
      parentURL = require$$3.pathToFileURL(__filename).href;
      mod.register("@sentry/server-utils/orchestrion/hook", {
        parentURL,
        data: { instrumentations: index.SENTRY_INSTRUMENTATIONS, diagnosticsPort },
        transferList: [diagnosticsPort]
      });
      new index$1.default({ instrumentations: index.SENTRY_INSTRUMENTATIONS }).patch();
      core.debug.log("Registered diagnostics-channel injection via Module.register()");
    } else {
      core.debug.warn("No available Node API to register diagnostics-channel injection hooks; skipping.");
      return;
    }
  } catch (error) {
    core.debug.warn(
      "Failed to register diagnostics-channel injection hooks; channel-based integrations will not record spans.",
      error
    );
    return;
  }
  core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__ = core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__ || {};
  core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime = core.GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime || [];
}

exports.registerDiagnosticsChannelInjection = registerDiagnosticsChannelInjection;
//# sourceMappingURL=register.js.map
