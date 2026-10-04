import { getCurrentScope, getClient, withIsolationScope } from './currentScopes.js';
import { DEBUG_BUILD } from './debug-build.js';
import { startNewTrace } from './tracing/trace.js';
import { debug } from './utils/debug-logger.js';
import { isThenable } from './utils/is.js';
import { uuid4 } from './utils/misc.js';
import { timestampInSeconds } from './utils/time.js';
import { isContinuingTrace } from './utils/tracing.js';

function withMonitor(monitorSlug, callback, upsertMonitorConfig) {
  function runCallback() {
    const checkInId = captureCheckIn({ monitorSlug, status: "in_progress" }, upsertMonitorConfig);
    const now = timestampInSeconds();
    function finishCheckIn(status) {
      captureCheckIn({ monitorSlug, status, checkInId, duration: timestampInSeconds() - now });
    }
    let maybePromiseResult;
    try {
      maybePromiseResult = callback();
    } catch (e) {
      finishCheckIn("error");
      throw e;
    }
    if (isThenable(maybePromiseResult)) {
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
  const oldPropagationContext = { ...getCurrentScope().getPropagationContext() };
  return withIsolationScope(() => {
    if (upsertMonitorConfig?.isolateTrace) {
      return startNewTrace(runCallback);
    }
    const scope = getCurrentScope();
    if (!isContinuingTrace(scope.getPropagationContext())) {
      scope.setPropagationContext(oldPropagationContext);
    }
    return runCallback();
  });
}
function captureCheckIn(checkIn, upsertMonitorConfig) {
  const scope = getCurrentScope();
  const client = getClient();
  if (!client) {
    DEBUG_BUILD && debug.warn("Cannot capture check-in. No client defined.");
  } else if (!client.captureCheckIn) {
    DEBUG_BUILD && debug.warn("Cannot capture check-in. Client does not support sending check-ins.");
  } else {
    return client.captureCheckIn(checkIn, upsertMonitorConfig, scope);
  }
  return uuid4();
}

export { captureCheckIn, withMonitor };
//# sourceMappingURL=monitor.js.map
