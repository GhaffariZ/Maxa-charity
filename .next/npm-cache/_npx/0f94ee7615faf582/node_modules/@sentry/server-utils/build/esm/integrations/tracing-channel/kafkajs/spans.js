import { ERROR_TYPE, MESSAGING_OPERATION_TYPE, MESSAGING_OPERATION_NAME, MESSAGING_DESTINATION_NAME, MESSAGING_SYSTEM } from '@sentry/conventions/attributes';
import { SPAN_STATUS_ERROR, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, getTraceData, propagationContextFromHeaders } from '@sentry/core';
import { ERROR_TYPE_VALUE_OTHER, MESSAGING_OPERATION_TYPE_VALUE_SEND, MESSAGING_SYSTEM_VALUE_KAFKA, ATTR_MESSAGING_DESTINATION_PARTITION_ID, ATTR_MESSAGING_KAFKA_MESSAGE_TOMBSTONE, ATTR_MESSAGING_KAFKA_MESSAGE_KEY, ATTR_MESSAGING_KAFKA_OFFSET, MESSAGING_OPERATION_TYPE_VALUE_RECEIVE } from './semconv.js';

const PRODUCER_ORIGIN = "auto.kafkajs.orchestrion.producer";
const CONSUMER_ORIGIN = "auto.kafkajs.orchestrion.consumer";
const TRACE_FLAG_SAMPLED = 1;
const TRACE_FLAG_NONE = 0;
function getHeaderAsString(headers, key) {
  const value = headers?.[key];
  if (value == null) {
    return void 0;
  }
  return Array.isArray(value) ? value[0]?.toString() : value.toString();
}
function getLinksFromHeaders(headers) {
  const sentryTrace = getHeaderAsString(headers, "sentry-trace");
  if (!sentryTrace) {
    return void 0;
  }
  const { traceId, parentSpanId, sampled } = propagationContextFromHeaders(
    sentryTrace,
    getHeaderAsString(headers, "baggage")
  );
  if (!parentSpanId) {
    return void 0;
  }
  return [
    {
      context: {
        traceId,
        spanId: parentSpanId,
        isRemote: true,
        traceFlags: sampled ? TRACE_FLAG_SAMPLED : TRACE_FLAG_NONE
      }
    }
  ];
}
function startConsumerSpan({ topic, message, operationType, links, attributes }) {
  const operationName = operationType === MESSAGING_OPERATION_TYPE_VALUE_RECEIVE ? "poll" : operationType;
  return startInactiveSpan({
    name: `${operationName} ${topic}`,
    // todo(v11): Use https://getsentry.github.io/sentry-conventions/ops/#messaging
    op: "message",
    kind: operationType === MESSAGING_OPERATION_TYPE_VALUE_RECEIVE ? SPAN_KIND.CLIENT : SPAN_KIND.CONSUMER,
    links,
    attributes: {
      ...attributes,
      [MESSAGING_SYSTEM]: MESSAGING_SYSTEM_VALUE_KAFKA,
      [MESSAGING_DESTINATION_NAME]: topic,
      [MESSAGING_OPERATION_TYPE]: operationType,
      [MESSAGING_OPERATION_NAME]: operationName,
      [ATTR_MESSAGING_KAFKA_MESSAGE_KEY]: message?.key ? String(message.key) : void 0,
      [ATTR_MESSAGING_KAFKA_MESSAGE_TOMBSTONE]: message?.key && message.value === null ? true : void 0,
      [ATTR_MESSAGING_KAFKA_OFFSET]: message?.offset,
      // Mirror the upstream behavior of only tagging per-message processing spans (not the batch
      // receiving span, which carries no message) with the auto origin.
      ...message ? { [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: CONSUMER_ORIGIN } : {}
    }
  });
}
function startProducerSpan(topic, message) {
  const span = startInactiveSpan({
    name: `send ${topic}`,
    op: "message",
    kind: SPAN_KIND.PRODUCER,
    attributes: {
      [MESSAGING_SYSTEM]: MESSAGING_SYSTEM_VALUE_KAFKA,
      [MESSAGING_DESTINATION_NAME]: topic,
      [ATTR_MESSAGING_KAFKA_MESSAGE_KEY]: message.key ? String(message.key) : void 0,
      [ATTR_MESSAGING_KAFKA_MESSAGE_TOMBSTONE]: message.key && message.value === null ? true : void 0,
      [ATTR_MESSAGING_DESTINATION_PARTITION_ID]: message.partition !== void 0 ? String(message.partition) : void 0,
      [MESSAGING_OPERATION_NAME]: "send",
      [MESSAGING_OPERATION_TYPE]: MESSAGING_OPERATION_TYPE_VALUE_SEND,
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: PRODUCER_ORIGIN
    }
  });
  message.headers = message.headers ?? {};
  const traceData = getTraceData({ span });
  if (traceData["sentry-trace"]) {
    message.headers["sentry-trace"] = traceData["sentry-trace"];
  }
  if (traceData.baggage) {
    message.headers["baggage"] = traceData.baggage;
  }
  return span;
}
function applyErrorToSpans(spans, reason) {
  let errorMessage;
  let errorType = ERROR_TYPE_VALUE_OTHER;
  if (typeof reason === "string" || reason === void 0) {
    errorMessage = reason;
  } else if (typeof reason === "object" && reason !== null && Object.prototype.hasOwnProperty.call(reason, "message")) {
    errorMessage = reason.message;
    errorType = reason.constructor.name;
  }
  spans.forEach((span) => {
    span.setAttribute(ERROR_TYPE, errorType);
    span.setStatus({ code: SPAN_STATUS_ERROR, message: errorMessage });
  });
}
function endSpansOnPromise(spans, promise) {
  return Promise.resolve(promise).catch((reason) => {
    applyErrorToSpans(spans, reason);
    throw reason;
  }).finally(() => {
    spans.forEach((span) => span.end());
  });
}

export { applyErrorToSpans, endSpansOnPromise, getHeaderAsString, getLinksFromHeaders, startConsumerSpan, startProducerSpan };
//# sourceMappingURL=spans.js.map
