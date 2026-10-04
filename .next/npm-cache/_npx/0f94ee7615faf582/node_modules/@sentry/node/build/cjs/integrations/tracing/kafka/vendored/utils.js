Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const api = require('@opentelemetry/api');
const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const semconv = require('./semconv.js');

const PRODUCER_ORIGIN = "auto.kafkajs.otel.producer";
const CONSUMER_ORIGIN = "auto.kafkajs.otel.consumer";
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
  const { traceId, parentSpanId, sampled } = core.propagationContextFromHeaders(
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
        traceFlags: sampled ? api.TraceFlags.SAMPLED : api.TraceFlags.NONE
      }
    }
  ];
}
function startConsumerSpan({ topic, message, operationType, links, attributes: attributes$1 }) {
  const operationName = operationType === semconv.MESSAGING_OPERATION_TYPE_VALUE_RECEIVE ? "poll" : operationType;
  return core.startInactiveSpan({
    name: `${operationName} ${topic}`,
    kind: operationType === semconv.MESSAGING_OPERATION_TYPE_VALUE_RECEIVE ? core.SPAN_KIND.CLIENT : core.SPAN_KIND.CONSUMER,
    links,
    attributes: {
      ...attributes$1,
      [attributes.MESSAGING_SYSTEM]: semconv.MESSAGING_SYSTEM_VALUE_KAFKA,
      [attributes.MESSAGING_DESTINATION_NAME]: topic,
      [attributes.MESSAGING_OPERATION_TYPE]: operationType,
      [attributes.MESSAGING_OPERATION_NAME]: operationName,
      [semconv.ATTR_MESSAGING_KAFKA_MESSAGE_KEY]: message?.key ? String(message.key) : void 0,
      [semconv.ATTR_MESSAGING_KAFKA_MESSAGE_TOMBSTONE]: message?.key && message.value === null ? true : void 0,
      [semconv.ATTR_MESSAGING_KAFKA_OFFSET]: message?.offset,
      // Mirror the upstream behavior of only tagging per-message processing spans (not the batch
      // receiving span, which carries no message) with the auto origin.
      ...message ? { [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: CONSUMER_ORIGIN } : {}
    }
  });
}
function startProducerSpan(topic, message) {
  const span = core.startInactiveSpan({
    name: `send ${topic}`,
    kind: core.SPAN_KIND.PRODUCER,
    attributes: {
      [attributes.MESSAGING_SYSTEM]: semconv.MESSAGING_SYSTEM_VALUE_KAFKA,
      [attributes.MESSAGING_DESTINATION_NAME]: topic,
      [semconv.ATTR_MESSAGING_KAFKA_MESSAGE_KEY]: message.key ? String(message.key) : void 0,
      [semconv.ATTR_MESSAGING_KAFKA_MESSAGE_TOMBSTONE]: message.key && message.value === null ? true : void 0,
      [semconv.ATTR_MESSAGING_DESTINATION_PARTITION_ID]: message.partition !== void 0 ? String(message.partition) : void 0,
      [attributes.MESSAGING_OPERATION_NAME]: "send",
      [attributes.MESSAGING_OPERATION_TYPE]: semconv.MESSAGING_OPERATION_TYPE_VALUE_SEND,
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: PRODUCER_ORIGIN
    }
  });
  message.headers = message.headers ?? {};
  const traceData = core.getTraceData({ span });
  if (traceData["sentry-trace"]) {
    message.headers["sentry-trace"] = traceData["sentry-trace"];
  }
  if (traceData.baggage) {
    message.headers["baggage"] = traceData.baggage;
  }
  return span;
}
function endSpansOnPromise(spans, sendPromise) {
  return Promise.resolve(sendPromise).catch((reason) => {
    let errorMessage;
    let errorType = semconv.ERROR_TYPE_VALUE_OTHER;
    if (typeof reason === "string" || reason === void 0) {
      errorMessage = reason;
    } else if (typeof reason === "object" && Object.prototype.hasOwnProperty.call(reason, "message")) {
      errorMessage = reason.message;
      errorType = reason.constructor.name;
    }
    spans.forEach((span) => {
      span.setAttribute(attributes.ERROR_TYPE, errorType);
      span.setStatus({
        code: core.SPAN_STATUS_ERROR,
        message: errorMessage
      });
    });
    throw reason;
  }).finally(() => {
    spans.forEach((span) => span.end());
  });
}

exports.endSpansOnPromise = endSpansOnPromise;
exports.getHeaderAsString = getHeaderAsString;
exports.getLinksFromHeaders = getLinksFromHeaders;
exports.startConsumerSpan = startConsumerSpan;
exports.startProducerSpan = startProducerSpan;
//# sourceMappingURL=utils.js.map
