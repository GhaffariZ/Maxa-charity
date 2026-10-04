import { SpanKind } from '@opentelemetry/api';
import { startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, getTraceData } from '@sentry/core';
import { NET_PEER_NAME, NET_PEER_PORT, MESSAGING_SYSTEM } from '@sentry/conventions/attributes';
import { ATTR_MESSAGING_PROTOCOL, MESSAGING_DESTINATION_KIND_VALUE_TOPIC, ATTR_MESSAGING_CONVERSATION_ID, OLD_ATTR_MESSAGING_MESSAGE_ID, ATTR_MESSAGING_RABBITMQ_ROUTING_KEY, ATTR_MESSAGING_DESTINATION_KIND, ATTR_MESSAGING_DESTINATION, ATTR_MESSAGING_PROTOCOL_VERSION, MESSAGING_OPERATION_VALUE_PROCESS, ATTR_MESSAGING_OPERATION, ATTR_MESSAGING_URL } from './semconv.js';

const PUBLISHER_ORIGIN = "auto.amqplib.otel.publisher";
const CONSUMER_ORIGIN = "auto.amqplib.otel.consumer";
const MESSAGE_STORED_SPAN = /* @__PURE__ */ Symbol("opentelemetry.amqplib.message.stored-span");
const CHANNEL_SPANS_NOT_ENDED = /* @__PURE__ */ Symbol("opentelemetry.amqplib.channel.spans-not-ended");
const CHANNEL_CONSUME_TIMEOUT_TIMER = /* @__PURE__ */ Symbol(
  "opentelemetry.amqplib.channel.consumer-timeout-timer"
);
const CONNECTION_ATTRIBUTES = /* @__PURE__ */ Symbol("opentelemetry.amqplib.connection.attributes");
const CHANNEL_IS_CONFIRM_PUBLISHING = /* @__PURE__ */ Symbol("sentry.amqplib.channel.is-confirm-publishing");
const normalizeExchange = (exchangeName) => exchangeName !== "" ? exchangeName : "<default>";
const censorPassword = (url) => {
  return url.replace(/:[^:@/]*@/, ":***@");
};
const getPort = (portFromUrl, resolvedProtocol) => {
  return portFromUrl || (resolvedProtocol === "AMQP" ? 5672 : 5671);
};
const getProtocol = (protocolFromUrl) => {
  const resolvedProtocol = protocolFromUrl || "amqp";
  const noEndingColon = resolvedProtocol.endsWith(":") ? resolvedProtocol.substring(0, resolvedProtocol.length - 1) : resolvedProtocol;
  return noEndingColon.toUpperCase();
};
const getHostname = (hostnameFromUrl) => {
  return hostnameFromUrl || "localhost";
};
const getConnectionAttributesFromServer = (conn) => {
  const product = conn.serverProperties.product?.toLowerCase?.();
  if (product) {
    return {
      [MESSAGING_SYSTEM]: product
    };
  } else {
    return {};
  }
};
const getConnectionAttributesFromUrl = (url) => {
  const attributes = {
    [ATTR_MESSAGING_PROTOCOL_VERSION]: "0.9.1"
    // this is the only protocol supported by the instrumented library
  };
  const resolvedUrl = url || "amqp://localhost";
  if (typeof resolvedUrl === "object") {
    const connectOptions = resolvedUrl;
    const protocol = getProtocol(connectOptions?.protocol);
    attributes[ATTR_MESSAGING_PROTOCOL] = protocol;
    attributes[NET_PEER_NAME] = getHostname(connectOptions?.hostname);
    attributes[NET_PEER_PORT] = getPort(connectOptions.port, protocol);
  } else {
    const censoredUrl = censorPassword(resolvedUrl);
    attributes[ATTR_MESSAGING_URL] = censoredUrl;
    try {
      const urlParts = new URL(censoredUrl);
      const protocol = getProtocol(urlParts.protocol);
      attributes[ATTR_MESSAGING_PROTOCOL] = protocol;
      attributes[NET_PEER_NAME] = getHostname(urlParts.hostname);
      attributes[NET_PEER_PORT] = getPort(urlParts.port ? parseInt(urlParts.port) : void 0, protocol);
    } catch {
    }
  }
  return attributes;
};
function getHeaderAsString(headers, key) {
  const value = headers?.[key];
  if (value == null) {
    return void 0;
  }
  return Array.isArray(value) ? String(value[0]) : String(value);
}
function startPublishSpan(exchange, routingKey, channel, options) {
  const normalizedExchange = normalizeExchange(exchange);
  const span = startInactiveSpan({
    name: `publish ${normalizedExchange}`,
    kind: SpanKind.PRODUCER,
    attributes: {
      ...channel.connection[CONNECTION_ATTRIBUTES],
      [ATTR_MESSAGING_DESTINATION]: exchange,
      [ATTR_MESSAGING_DESTINATION_KIND]: MESSAGING_DESTINATION_KIND_VALUE_TOPIC,
      [ATTR_MESSAGING_RABBITMQ_ROUTING_KEY]: routingKey,
      [OLD_ATTR_MESSAGING_MESSAGE_ID]: options?.messageId,
      [ATTR_MESSAGING_CONVERSATION_ID]: options?.correlationId,
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: PUBLISHER_ORIGIN
    }
  });
  const modifiedOptions = options ?? {};
  modifiedOptions.headers = modifiedOptions.headers ?? {};
  const traceData = getTraceData({ span });
  if (traceData["sentry-trace"]) {
    modifiedOptions.headers["sentry-trace"] = traceData["sentry-trace"];
  }
  if (traceData.baggage) {
    modifiedOptions.headers["baggage"] = traceData.baggage;
  }
  return { span, modifiedOptions };
}
function startConsumeSpan(queue, msg, channel) {
  return startInactiveSpan({
    name: `${queue} process`,
    kind: SpanKind.CONSUMER,
    attributes: {
      ...channel?.connection?.[CONNECTION_ATTRIBUTES],
      [ATTR_MESSAGING_DESTINATION]: msg.fields?.exchange,
      [ATTR_MESSAGING_DESTINATION_KIND]: MESSAGING_DESTINATION_KIND_VALUE_TOPIC,
      [ATTR_MESSAGING_RABBITMQ_ROUTING_KEY]: msg.fields?.routingKey,
      [ATTR_MESSAGING_OPERATION]: MESSAGING_OPERATION_VALUE_PROCESS,
      [OLD_ATTR_MESSAGING_MESSAGE_ID]: msg?.properties.messageId,
      [ATTR_MESSAGING_CONVERSATION_ID]: msg?.properties.correlationId,
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: CONSUMER_ORIGIN
    }
  });
}

export { CHANNEL_CONSUME_TIMEOUT_TIMER, CHANNEL_IS_CONFIRM_PUBLISHING, CHANNEL_SPANS_NOT_ENDED, CONNECTION_ATTRIBUTES, MESSAGE_STORED_SPAN, getConnectionAttributesFromServer, getConnectionAttributesFromUrl, getHeaderAsString, normalizeExchange, startConsumeSpan, startPublishSpan };
//# sourceMappingURL=utils.js.map
