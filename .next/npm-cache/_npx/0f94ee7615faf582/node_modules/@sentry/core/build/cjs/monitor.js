Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const currentScopes = require('./currentScopes.js');
const debugBuild = require('./debug-build.js');
const trace = require('./tracing/trace.js');
const debugLogger = require('./utils/debug-logger.js');
const is = require('./utils/is.js');
const misc = require('./utils/misc.js');
const time = require('./utils/time.js');
const tracing = require('./utils/tracing.js');

function withMonitor(monitorSlug, callback, upsertMonitorConfig) {
  function runCallback() {
    const checkInId = captureCheckIn({ monitorSlug, status: "in_progress" }, upsertMonitorConfig);
    const now = time.timestampInSeconds();
    function finishCheckIn(status) {
      captureCheckIn({ monitorSlug, status, checkInId, duration: time.timestampInSeconds() - now });
    }
    let maybePromiseResult;
    try {
      maybePromiseResult = callback();
    } catch (e) {
      finishCheckIn("error");
      throw e;
    }
    if (is.isThenable(maybePromiseResult)) {
      return maybePromiseResult.then(
        (r) => {
          finishCheckIn("ok");
          return r;
        },
        (e) => {
          finishCheckIn("error");
          throw e;
        }
      );
    }
    finishCheckIn("ok");
    return maybePromiseResult;
  }
  const oldPropagationContext = { ...currentScopes.getCurrentScope().getPropagationContext() };
  return currentScopes.withIsolationScope(() => {
    if (upsertMonitorConfig?.isolateTrace) {
      return trace.startNewTrace(runCallback);
    }
    const scope = currentScopes.getCurrentScope();
    if (!tracing.isContinuingTrace(scope.getPropagationContext())) {
      scope.setPropagationContext(oldPropagationContext);
    }
    return runCallback();
  });
}
function captureCheckIn(checkIn, upsertMonitorConfig) {
  const scope = currentScopes.getCurrentScope();
  const client = currentScopes.getClient();
  if (!client) {
    debugBuild.DEBUG_BUILD && debugLogger.debug.warn("Cannot capture check-in. No client defined.");
  } else if (!client.captureCheckIn) {
    debugBuild.DEBUG_BUILD && debugLogger.debug.warn("Cannot capture check-in. Client does not support sending check-ins.");
  } else {
    return client.captureCheckIn(checkIn, upsertMonitorConfig, scope);
  }
  return misc.uuid4();
}

exports.captureCheckIn = captureCheckIn;
exports.withMonitor = withMonitor;
//# sourceMappingURL=monitor.js.map
