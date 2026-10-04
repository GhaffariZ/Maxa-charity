import { startInactiveSpan, getActiveSpan, withActiveSpan, suppressTracing, SPAN_STATUS_ERROR, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SEMANTIC_ATTRIBUTE_SENTRY_OP, startSpan } from '@sentry/core';
import * as fs from 'fs';
import { promisify } from 'util';
import { SYNC_FUNCTIONS, CALLBACK_FUNCTIONS, PROMISE_FUNCTIONS } from './constants.js';
import { indexFs } from './utils.js';

const SPAN_ORIGIN = "auto.file.fs";
const SPAN_OP = "file";
const FS_OPERATIONS_WITH_OLD_PATH_NEW_PATH = ["rename", "renameSync"];
const FS_OPERATIONS_WITH_SRC_DEST = ["copyFile", "cp", "copyFileSync", "cpSync"];
const FS_OPERATIONS_WITH_EXISTING_PATH_NEW_PATH = ["link", "linkSync"];
const FS_OPERATIONS_WITH_PREFIX = ["mkdtemp", "mkdtempSync"];
const FS_OPERATIONS_WITH_TARGET_PATH = ["symlink", "symlinkSync"];
const FS_OPERATIONS_WITH_PATH_ARG = [
  "access",
  "appendFile",
  "chmod",
  "chown",
  "exists",
  "mkdir",
  "lchown",
  "lstat",
  "lutimes",
  "open",
  "opendir",
  "readdir",
  "readFile",
  "readlink",
  "realpath",
  "realpath.native",
  "rm",
  "rmdir",
  "stat",
  "truncate",
  "unlink",
  "utimes",
  "writeFile",
  "accessSync",
  "appendFileSync",
  "chmodSync",
  "chownSync",
  "existsSync",
  "lchownSync",
  "lstatSync",
  "lutimesSync",
  "opendirSync",
  "mkdirSync",
  "openSync",
  "readdirSync",
  "readFileSync",
  "readlinkSync",
  "realpathSync",
  "realpathSync.native",
  "rmdirSync",
  "rmSync",
  "statSync",
  "truncateSync",
  "unlinkSync",
  "utimesSync",
  "writeFileSync"
];
function getSpanAttributes(functionName, args, config) {
  const attributes = {
    [SEMANTIC_ATTRIBUTE_SENTRY_OP]: SPAN_OP,
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: SPAN_ORIGIN
  };
  if (!config.recordFilePaths) {
    return attributes;
  }
  if (typeof args[0] === "string" && FS_OPERATIONS_WITH_PATH_ARG.includes(functionName)) {
    attributes["path_argument"] = args[0];
  } else if (typeof args[0] === "string" && typeof args[1] === "string") {
    if (FS_OPERATIONS_WITH_TARGET_PATH.includes(functionName)) {
      attributes["target_argument"] = args[0];
      attributes["path_argument"] = args[1];
    } else if (FS_OPERATIONS_WITH_EXISTING_PATH_NEW_PATH.includes(functionName)) {
      attributes["existing_path_argument"] = args[0];
      attributes["new_path_argument"] = args[1];
    } else if (FS_OPERATIONS_WITH_SRC_DEST.includes(functionName)) {
      attributes["src_argument"] = args[0];
      attributes["dest_argument"] = args[1];
    } else if (FS_OPERATIONS_WITH_OLD_PATH_NEW_PATH.includes(functionName)) {
      attributes["old_path_argument"] = args[0];
      attributes["new_path_argument"] = args[1];
    }
  } else if (typeof args[0] === "string" && FS_OPERATIONS_WITH_PREFIX.includes(functionName)) {
    attributes["prefix_argument"] = args[0];
  }
  return attributes;
}
function patchedFunctionWithOriginalProperties(patchedFunction, original) {
  return Object.assign(patchedFunction, original);
}
const _patched = /* @__PURE__ */ new WeakMap();
function _patchMethod(obj, name, wrapper) {
  const original = obj[name];
  if (typeof original !== "function") return;
  let patched = _patched.get(obj);
  if (!patched) {
    patched = /* @__PURE__ */ new Set();
    _patched.set(obj, patched);
  }
  if (patched.has(name)) return;
  patched.add(name);
  obj[name] = wrapper(original);
}
function _patchSyncFunction(functionName, original, config) {
  const patchedFunction = function(...args) {
    const attributes = getSpanAttributes(functionName, args, config);
    return startSpan({ name: `fs.${functionName}`, onlyIfParent: true, attributes }, (span) => {
      try {
        return suppressTracing(() => original.apply(this, args));
      } catch (error) {
        recordError(span, error, config);
        throw error;
      }
    });
  };
  return patchedFunctionWithOriginalProperties(patchedFunction, original);
}
function _patchCallbackFunction(functionName, original, config) {
  const patchedFunction = function(...args) {
    const lastIdx = args.length - 1;
    const cb = args[lastIdx];
    if (typeof cb !== "function") {
      return original.apply(this, args);
    }
    const attributes = getSpanAttributes(functionName, args, config);
    const span = startInactiveSpan({ name: `fs.${functionName}`, onlyIfParent: true, attributes });
    const parentSpan = getActiveSpan();
    args[lastIdx] = function(...cbArgs) {
      const error = cbArgs[0];
      if (error) {
        recordError(span, error, config);
      }
      span.end();
      if (parentSpan) {
        return withActiveSpan(parentSpan, () => cb.apply(this, cbArgs));
      }
      return cb.apply(this, cbArgs);
    };
    try {
      return suppressTracing(() => original.apply(this, args));
    } catch (error) {
      recordError(span, error, config);
      span.end();
      throw error;
    }
  };
  return patchedFunctionWithOriginalProperties(patchedFunction, original);
}
function _patchExistsCallbackFunction(original, config) {
  const functionName = "exists";
  const patchedFunction = function(...args) {
    const lastIdx = args.length - 1;
    const cb = args[lastIdx];
    if (typeof cb !== "function") {
      return original.apply(this, args);
    }
    const attributes = getSpanAttributes(functionName, args, config);
    const span = startInactiveSpan({ name: `fs.${functionName}`, onlyIfParent: true, attributes });
    const parentSpan = getActiveSpan();
    args[lastIdx] = function(...cbArgs) {
      span.end();
      if (parentSpan) {
        return withActiveSpan(parentSpan, () => cb.apply(this, cbArgs));
      }
      return cb.apply(this, cbArgs);
    };
    try {
      return suppressTracing(() => original.apply(this, args));
    } catch (error) {
      recordError(span, error, config);
      span.end();
      throw error;
    }
  };
  const functionWithOriginalProperties = patchedFunctionWithOriginalProperties(patchedFunction, original);
  const promisified = function(path) {
    return new Promise((resolve) => functionWithOriginalProperties(path, resolve));
  };
  Object.defineProperty(promisified, "name", { value: functionName });
  Object.defineProperty(functionWithOriginalProperties, promisify.custom, {
    value: promisified
  });
  return functionWithOriginalProperties;
}
function _patchPromiseFunction(functionName, original, config) {
  const patchedFunction = async function(...args) {
    const attributes = getSpanAttributes(functionName, args, config);
    return startSpan({ name: `fs.${functionName}`, onlyIfParent: true, attributes }, async (span) => {
      try {
        return await suppressTracing(() => original.apply(this, args));
      } catch (error) {
        recordError(span, error, config);
        throw error;
      }
    });
  };
  return patchedFunctionWithOriginalProperties(patchedFunction, original);
}
function enableFsInstrumentation(config = {}) {
  for (const fName of SYNC_FUNCTIONS) {
    const { objectToPatch, functionNameToPatch } = indexFs(fs, fName);
    _patchMethod(objectToPatch, functionNameToPatch, (original) => _patchSyncFunction(fName, original, config));
  }
  for (const fName of CALLBACK_FUNCTIONS) {
    const { objectToPatch, functionNameToPatch } = indexFs(fs, fName);
    if (fName === "exists") {
      _patchMethod(objectToPatch, functionNameToPatch, (original) => _patchExistsCallbackFunction(original, config));
    } else {
      _patchMethod(objectToPatch, functionNameToPatch, (original) => _patchCallbackFunction(fName, original, config));
    }
  }
  const fsPromises = fs.promises;
  for (const fName of PROMISE_FUNCTIONS) {
    _patchMethod(fsPromises, fName, (original) => _patchPromiseFunction(fName, original, config));
  }
}
function recordError(span, error, config) {
  span.setStatus({ code: SPAN_STATUS_ERROR, message: "internal_error" });
  if (config.recordErrorMessagesAsSpanAttributes && error instanceof Error) {
    span.setAttribute("fs_error", error.message);
  }
}

export { enableFsInstrumentation };
//# sourceMappingURL=instrumentation.js.map
