Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const serverUtils = require('@sentry/server-utils');

const ORIGIN = "auto.db.otel.mongo";
function getV4SpanAttributes(connectionCtx, ns, command, operation) {
  return serverUtils.getV4SpanAttributes(connectionCtx, ns, command, operation, ORIGIN);
}
function getV3SpanAttributes(ns, topology, command, operation) {
  return serverUtils.getV3SpanAttributes(ns, topology, command, operation, ORIGIN);
}
function patchEnd(span, resultHandler) {
  const parentSpan = core.getActiveSpan();
  let spanEnded = false;
  return function patchedEnd(...args) {
    if (!spanEnded) {
      spanEnded = true;
      const error = args[0];
      if (span) {
        if (error instanceof Error) {
          span.setStatus({ code: core.SPAN_STATUS_ERROR, message: error.message });
        }
        span.end();
      }
    }
    return core.withActiveSpan(parentSpan ?? null, () => resultHandler.apply(this, args));
  };
}
function shouldSkipInstrumentation() {
  return !core.getActiveSpan();
}

exports.getV3CommandOperation = serverUtils.getV3CommandOperation;
exports.startMongoSpan = serverUtils.startMongoSpan;
exports.getV3SpanAttributes = getV3SpanAttributes;
exports.getV4SpanAttributes = getV4SpanAttributes;
exports.patchEnd = patchEnd;
exports.shouldSkipInstrumentation = shouldSkipInstrumentation;
//# sourceMappingURL=utils.js.map
