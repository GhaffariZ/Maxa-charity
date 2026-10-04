import { getActiveSpan, SPAN_STATUS_ERROR, withActiveSpan } from '@sentry/core';
import { getV3SpanAttributes as getV3SpanAttributes$1, getV4SpanAttributes as getV4SpanAttributes$1 } from '@sentry/server-utils';
export { getV3CommandOperation, startMongoSpan } from '@sentry/server-utils';

const ORIGIN = "auto.db.otel.mongo";
function getV4SpanAttributes(connectionCtx, ns, command, operation) {
  return getV4SpanAttributes$1(connectionCtx, ns, command, operation, ORIGIN);
}
function getV3SpanAttributes(ns, topology, command, operation) {
  return getV3SpanAttributes$1(ns, topology, command, operation, ORIGIN);
}
function patchEnd(span, resultHandler) {
  const parentSpan = getActiveSpan();
  let spanEnded = false;
  return function patchedEnd(...args) {
    if (!spanEnded) {
      spanEnded = true;
      const error = args[0];
      if (span) {
        if (error instanceof Error) {
          span.setStatus({ code: SPAN_STATUS_ERROR, message: error.message });
        }
        span.end();
      }
    }
    return withActiveSpan(parentSpan ?? null, () => resultHandler.apply(this, args));
  };
}
function shouldSkipInstrumentation() {
  return !getActiveSpan();
}

export { getV3SpanAttributes, getV4SpanAttributes, patchEnd, shouldSkipInstrumentation };
//# sourceMappingURL=utils.js.map
