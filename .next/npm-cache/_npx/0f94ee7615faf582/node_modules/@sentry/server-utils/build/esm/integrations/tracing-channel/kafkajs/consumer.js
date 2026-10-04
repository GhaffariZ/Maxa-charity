import { MESSAGING_BATCH_MESSAGE_COUNT } from '@sentry/conventions/attributes';
import { continueTrace, withActiveSpan, startNewTrace } from '@sentry/core';
import { ATTR_MESSAGING_DESTINATION_PARTITION_ID, MESSAGING_OPERATION_TYPE_VALUE_PROCESS, MESSAGING_OPERATION_TYPE_VALUE_RECEIVE } from './semconv.js';
import { getHeaderAsString, startConsumerSpan, endSpansOnPromise, getLinksFromHeaders } from './spans.js';

const consumerCallbackWrapped = /* @__PURE__ */ Symbol("sentry-kafkajs-consumer-callback-wrapped");
function isWrappedConsumerCallback(fn) {
  return typeof fn === "function" && fn[consumerCallbackWrapped] === true;
}
function wrapEachMessage(original) {
  const wrapped = function eachMessage(payload) {
    const sentryTrace = getHeaderAsString(payload.message.headers, "sentry-trace");
    const baggage = getHeaderAsString(payload.message.headers, "baggage");
    return continueTrace({ sentryTrace, baggage }, () => {
      const span = startConsumerSpan({
        topic: payload.topic,
        message: payload.message,
        operationType: MESSAGING_OPERATION_TYPE_VALUE_PROCESS,
        attributes: {
          [ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.partition)
        }
      });
      const promise = withActiveSpan(span, () => original.call(this, payload));
      return endSpansOnPromise([span], promise);
    });
  };
  wrapped[consumerCallbackWrapped] = true;
  return wrapped;
}
function wrapEachBatch(original) {
  const wrapped = function eachBatch(payload) {
    const receivingSpan = startNewTrace(
      () => startConsumerSpan({
        topic: payload.batch.topic,
        message: void 0,
        operationType: MESSAGING_OPERATION_TYPE_VALUE_RECEIVE,
        attributes: {
          [MESSAGING_BATCH_MESSAGE_COUNT]: payload.batch.messages.length,
          [ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.batch.partition)
        }
      })
    );
    return withActiveSpan(receivingSpan, () => {
      const spans = [receivingSpan];
      payload.batch.messages.forEach((message) => {
        spans.push(
          startConsumerSpan({
            topic: payload.batch.topic,
            message,
            operationType: MESSAGING_OPERATION_TYPE_VALUE_PROCESS,
            links: getLinksFromHeaders(message.headers),
            attributes: {
              [ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.batch.partition)
            }
          })
        );
      });
      const promise = original.call(this, payload);
      return endSpansOnPromise(spans, promise);
    });
  };
  wrapped[consumerCallbackWrapped] = true;
  return wrapped;
}

export { isWrappedConsumerCallback, wrapEachBatch, wrapEachMessage };
//# sourceMappingURL=consumer.js.map
