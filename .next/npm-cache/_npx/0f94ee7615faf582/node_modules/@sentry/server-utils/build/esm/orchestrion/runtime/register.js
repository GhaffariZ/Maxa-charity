import { GLOBAL_OBJ, debug, parseSemver } from '@sentry/core';
import * as require$$1 from 'node:module';
import 'node:url';
import { isMainThread, parentPort } from 'node:worker_threads';
import { SENTRY_INSTRUMENTATIONS } from '../config/index.js';
import ModulePatch from '../../vendored/@apm-js-collab/tracing-hooks/index.js';
import { initializeSync, loadSync, resolveSync, createDiagnosticsPort } from '../../vendored/@apm-js-collab/tracing-hooks/hook.js';
import { d as diagnostics } from '../../vendored/@apm-js-collab/tracing-hooks/lib/diagnostics.js';

function hasStableSyncModuleHooks(denoVersionString) {
  if (denoVersionString) {
    const { major: major2 = 0, minor: minor2 = 0 } = parseSemver(denoVersionString);
    return major2 > 2 || major2 === 2 && minor2 >= 8;
  }
  const { major = 0, minor = 0 } = parseSemver(process.versions.node ?? "0.0.0");
  return major > 25 || major === 25 && minor >= 1 || major === 24 && minor >= 13;
}
function registerDiagnosticsChannelInjection(_options) {
  if (!isMainThread && !parentPort) {
    return;
  }
  if (GLOBAL_OBJ?.__SENTRY_ORCHESTRION__?.runtime) {
    return;
  }
  const globalAny = globalThis;
  const stableSyncHooks = hasStableSyncModuleHooks(globalAny.Deno?.version?.deno);
  const mod = require$$1;
  diagnostics.setDiagnosticsHook(({ moduleName, error }) => {
    if (error) {
      debug.warn(`[orchestrion] failed to inject diagnostics-channel into ${moduleName}:`, error);
    } else {
      GLOBAL_OBJ.__SENTRY_ORCHESTRION__ = GLOBAL_OBJ.__SENTRY_ORCHESTRION__ || {};
      GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime = GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime || [];
      GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime.push(moduleName);
    }
  });
  try {
    if (typeof mod.registerHooks === "function" && stableSyncHooks) {
      initializeSync({ instrumentations: SENTRY_INSTRUMENTATIONS });
      mod.registerHooks({ resolve: resolveSync, load: loadSync });
      debug.log("Registered diagnostics-channel injection via Module.registerHooks()");
    } else if (typeof mod.register === "function" && !globalAny.Bun && !globalAny.Deno) {
      const diagnosticsPort = createDiagnosticsPort();
      let parentURL;
      parentURL = import.meta.url;
      mod.register("@sentry/server-utils/orchestrion/hook", {
        parentURL,
        data: { instrumentations: SENTRY_INSTRUMENTATIONS, diagnosticsPort },
        transferList: [diagnosticsPort]
      });
      new ModulePatch({ instrumentations: SENTRY_INSTRUMENTATIONS }).patch();
      debug.log("Registered diagnostics-channel injection via Module.register()");
    } else {
      debug.warn("No available Node API to register diagnostics-channel injection hooks; skipping.");
      return;
    }
  } catch (error) {
    debug.warn(
      "Failed to register diagnostics-channel injection hooks; channel-based integrations will not record spans.",
      error
    );
    return;
  }
  GLOBAL_OBJ.__SENTRY_ORCHESTRION__ = GLOBAL_OBJ.__SENTRY_ORCHESTRION__ || {};
  GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime = GLOBAL_OBJ.__SENTRY_ORCHESTRION__.runtime || [];
}

export { registerDiagnosticsChannelInjection };
//# sourceMappingURL=register.js.map
