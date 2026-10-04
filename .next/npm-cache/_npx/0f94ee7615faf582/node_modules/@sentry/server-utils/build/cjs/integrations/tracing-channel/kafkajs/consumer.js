Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const semconv = require('./semconv.js');
const spans = require('./spans.js');

const consumerCallbackWrapped = /* @__PURE__ */ Symbol("sentry-kafkajs-consumer-callback-wrapped");
function isWrappedConsumerCallback(fn) {
  return typeof fn === "function" && fn[consumerCallbackWrapped] === true;
}
function wrapEachMessage(original) {
  const wrapped = function eachMessage(payload) {
    const sentryTrace = spans.getHeaderAsString(payload.message.headers, "sentry-trace");
    const baggage = spans.getHeaderAsString(payload.message.headers, "baggage");
    return core.continueTrace({ sentryTrace, baggage }, () => {
      const span = spans.startConsumerSpan({
        topic: payload.topic,
        message: payload.message,
        operationType: semconv.MESSAGING_OPERATION_TYPE_VALUE_PROCESS,
        attributes: {
          [semconv.ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.partition)
        }
      });
      const promise = core.withActiveSpan(span, () => original.call(this, payload));
      return spans.endSpansOnPromise([span], promise);
    });
  };
  wrapped[consumerCallbackWrapped] = true;
  return wrapped;
}
function wrapEachBatch(original) {
  const wrapped = function eachBatch(payload) {
    const receivingSpan = core.startNewTrace(
      () => spans.startConsumerSpan({
        topic: payload.batch.topic,
        message: void 0,
        operationType: semconv.MESSAGING_OPERATION_TYPE_VALUE_RECEIVE,
        attributes: {
          [attributes.MESSAGING_BATCH_MESSAGE_COUNT]: payload.batch.messages.length,
          [semconv.ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.batch.partition)
        }
      })
    );
    return core.withActiveSpan(receivingSpan, () => {
      const spans$1 = [receivingSpan];
      payload.batch.messages.forEach((message) => {
        spans$1.push(
          spans.startConsumerSpan({
            topic: payload.batch.topic,
            message,
            operationType: semconv.MESSAGING_OPERATION_TYPE_VALUE_PROCESS,
            links: spans.getLinksFromHeaders(message.headers),
            attributes: {
              [semconv.ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.batch.partition)
            }
          })
        );
      });
      const promise = original.call(this, payload);
      return spans.endSpansOnPromise(spans$1, promise);
    });
  };
  wrapped[consumerCallbackWrapped] = true;
  return wrapped;
}

exports.isWrappedConsumerCallback = isWrappedConsumerCallback;
exports.wrapEachBatch = wrapEachBatch;
exports.wrapEachMessage = wrapEachMessage;
//# sourceMappingURL=consumer.js.map
