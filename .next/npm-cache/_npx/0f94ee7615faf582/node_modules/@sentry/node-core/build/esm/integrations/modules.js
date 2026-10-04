import { readFileSync } from 'node:fs';
import { join } from 'node:path';
import { GLOBAL_OBJ } from '@sentry/core';

let moduleCache;
const INTEGRATION_NAME = "Modules";
function getServerModules() {
  if (typeof __SENTRY_SERVER_MODULES__ !== "undefined") {
    return __SENTRY_SERVER_MODULES__;
  }
  return GLOBAL_OBJ.__SENTRY_SERVER_MODULES__ ?? {};
}
const _modulesIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    processEvent(event) {
      event.modules = {
        ...event.modules,
        ..._getModules()
      };
      return event;
    },
    getModules: _getModules
  };
});
const modulesIntegration = _modulesIntegration;
function collectModules() {
  return {
    ...getServerModules(),
    ...getModulesFromPackageJson(),
    };
}
function _getModules() {
  if (!moduleCache) {
    moduleCache = collectModules();
  }
  return moduleCache;
}
function getPackageJson() {
  try {
    const filePath = join(process.cwd(), "package.json");
    const packageJson = JSON.parse(readFileSync(filePath, "utf8"));
    return packageJson;
  } catch {
    return {};
  }
}
function getModulesFromPackageJson() {
  const packageJson = getPackageJson();
  return {
    ...packageJson.dependencies,
    ...packageJson.devDependencies
  };
}

export { modulesIntegration };
//# sourceMappingURL=modules.js.map
