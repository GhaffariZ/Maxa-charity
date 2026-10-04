import { getDefaultExportFromCjs } from '../../../../_virtual/_commonjsHelpers.js';
import require$$0 from 'node:fs';
import require$$1 from 'node:path';

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

const getPackageVersion$1 = /*@__PURE__*/getDefaultExportFromCjs(getPackageVersion_1);

export { getPackageVersion$1 as default, getPackageVersion_1 as g };
//# sourceMappingURL=get-package-version.js.map
