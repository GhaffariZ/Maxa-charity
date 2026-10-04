Object.defineProperties(exports, { __esModule: { value: true }, [Symbol.toStringTag]: { value: 'Module' } });

const _commonjsHelpers = require('../../../../_virtual/_commonjsHelpers.js');
const require$$0 = require('node:fs');
const require$$1 = require('node:path');

const { readFileSync } = require$$0;
const { join } = require$$1;
const packageVersions = /* @__PURE__ */ new Map();
function getPackageVersion(baseDir) {
  if (packageVersions.has(baseDir)) {
    return packageVersions.get(baseDir);
  }
  try {
    const packageJsonPath = join(baseDir, "package.json");
    const jsonFile = readFileSync(packageJsonPath);
    const { version } = JSON.parse(jsonFile);
    packageVersions.set(baseDir, version);
    return version;
  } catch {
    return process.version.slice(1);
  }
}
var getPackageVersion_1 = getPackageVersion;

const getPackageVersion$1 = /*@__PURE__*/_commonjsHelpers.getDefaultExportFromCjs(getPackageVersion_1);

exports.default = getPackageVersion$1;
exports.getPackageVersion_1 = getPackageVersion_1;
//# sourceMappingURL=get-package-version.js.map
