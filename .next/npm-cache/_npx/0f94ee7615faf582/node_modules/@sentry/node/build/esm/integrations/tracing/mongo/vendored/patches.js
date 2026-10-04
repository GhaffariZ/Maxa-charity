import { getActiveSpan, withActiveSpan } from '@sentry/core';
import { shouldSkipInstrumentation, getV3SpanAttributes, patchEnd, getV4SpanAttributes } from './utils.js';
import { startMongoSpan, getV3CommandOperation } from '@sentry/server-utils';

function getV3PatchOperation(operationName) {
  return (original) => {
    return function patchedServerCommand(server, ns, ops, options, callback) {
      const resultHandler = typeof options === "function" ? options : callback;
      if (shouldSkipInstrumentation() || typeof resultHandler !== "function" || typeof ops !== "object") {
        if (typeof options === "function") {
          return original.call(this, server, ns, ops, options);
        } else {
          return original.call(this, server, ns, ops, options, callback);
        }
      }
      const span = startMongoSpan(getV3SpanAttributes(ns, server, ops[0], operationName));
      const patchedCallback = patchEnd(span, resultHandler);
      if (typeof options === "function") {
        return original.call(this, server, ns, ops, patchedCallback);
      } else {
        return original.call(this, server, ns, ops, options, patchedCallback);
      }
    };
  };
}
function getV3PatchCommand() {
  return (original) => {
    return function patchedServerCommand(server, ns, cmd, options, callback) {
      const resultHandler = typeof options === "function" ? options : callback;
      if (shouldSkipInstrumentation() || typeof resultHandler !== "function" || typeof cmd !== "object") {
        if (typeof options === "function") {
          return original.call(this, server, ns, cmd, options);
        } else {
          return original.call(this, server, ns, cmd, options, callback);
        }
      }
      const operationName = getV3CommandOperation(cmd);
      const span = startMongoSpan(getV3SpanAttributes(ns, server, cmd, operationName));
      const patchedCallback = patchEnd(span, resultHandler);
      if (typeof options === "function") {
        return original.call(this, server, ns, cmd, patchedCallback);
      } else {
        return original.call(this, server, ns, cmd, options, patchedCallback);
      }
    };
  };
}
function getV4PatchCommandCallback() {
  return (original) => {
    return function patchedV4ServerCommand(ns, cmd, options, callback) {
      const resultHandler = callback;
      const commandType = Object.keys(cmd)[0];
      if (typeof cmd !== "object" || cmd.ismaster || cmd.hello) {
        return original.call(this, ns, cmd, options, callback);
      }
      let span = void 0;
      if (!shouldSkipInstrumentation()) {
        span = startMongoSpan(getV4SpanAttributes(this, ns, cmd, commandType));
      }
      const patchedCallback = patchEnd(span, resultHandler);
      return original.call(this, ns, cmd, options, patchedCallback);
    };
  };
}
function getV4PatchCommandPromise() {
  return (original) => {
    return function patchedV4ServerCommand(...args) {
      const [ns, cmd] = args;
      const commandType = Object.keys(cmd)[0];
      const resultHandler = () => void 0;
      if (typeof cmd !== "object" || cmd.ismaster || cmd.hello) {
        return original.apply(this, args);
      }
      let span = void 0;
      if (!shouldSkipInstrumentation()) {
        span = startMongoSpan(getV4SpanAttributes(this, ns, cmd, commandType));
      }
      const patchedCallback = patchEnd(span, resultHandler);
      const result = original.apply(this, args);
      result.then(
        (res) => patchedCallback(null, res),
        (err) => patchedCallback(err)
      );
      return result;
    };
  };
}
function getV3PatchFind() {
  return (original) => {
    return function patchedServerCommand(server, ns, cmd, cursorState, options, callback) {
      const resultHandler = typeof options === "function" ? options : callback;
      if (shouldSkipInstrumentation() || typeof resultHandler !== "function" || typeof cmd !== "object") {
        if (typeof options === "function") {
          return original.call(this, server, ns, cmd, cursorState, options);
        } else {
          return original.call(this, server, ns, cmd, cursorState, options, callback);
        }
      }
      const span = startMongoSpan(getV3SpanAttributes(ns, server, cmd, "find"));
      const patchedCallback = patchEnd(span, resultHandler);
      if (typeof options === "function") {
        return original.call(this, server, ns, cmd, cursorState, patchedCallback);
      } else {
        return original.call(this, server, ns, cmd, cursorState, options, patchedCallback);
      }
    };
  };
}
function getV3PatchCursor() {
  return (original) => {
    return function patchedServerCommand(server, ns, cursorState, batchSize, options, callback) {
      const resultHandler = typeof options === "function" ? options : callback;
      if (shouldSkipInstrumentation() || typeof resultHandler !== "function") {
        if (typeof options === "function") {
          return original.call(this, server, ns, cursorState, batchSize, options);
        } else {
          return original.call(this, server, ns, cursorState, batchSize, options, callback);
        }
      }
      const span = startMongoSpan(getV3SpanAttributes(ns, server, cursorState.cmd, "getMore"));
      const patchedCallback = patchEnd(span, resultHandler);
      if (typeof options === "function") {
        return original.call(this, server, ns, cursorState, batchSize, patchedCallback);
      } else {
        return original.call(this, server, ns, cursorState, batchSize, options, patchedCallback);
      }
    };
  };
}
function getV4ConnectionPoolCheckOut() {
  return (original) => {
    return function patchedCheckout(callback) {
      const parentSpan = getActiveSpan();
      return original.call(this, function(...args) {
        return withActiveSpan(parentSpan ?? null, () => callback.apply(this, args));
      });
    };
  };
}

export { getV3PatchCommand, getV3PatchCursor, getV3PatchFind, getV3PatchOperation, getV4ConnectionPoolCheckOut, getV4PatchCommandCallback, getV4PatchCommandPromise };
//# sourceMappingURL=patches.js.map
