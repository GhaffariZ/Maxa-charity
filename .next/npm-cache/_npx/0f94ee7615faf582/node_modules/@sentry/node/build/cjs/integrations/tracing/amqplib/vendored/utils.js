Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const api = require('@opentelemetry/api');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const semconv = require('./semconv.js');

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
      [attributes.MESSAGING_SYSTEM]: product
    };
  } else {
    return {};
  }
};
const getConnectionAttributesFromUrl = (url) => {
  const attributes$1 = {
    [semconv.ATTR_MESSAGING_PROTOCOL_VERSION]: "0.9.1"
    // this is the only protocol supported by the instrumented library
  };
  const resolvedUrl = url || "amqp://localhost";
  if (typeof resolvedUrl === "object") {
    const connectOptions = resolvedUrl;
    const protocol = getProtocol(connectOptions?.protocol);
    attributes$1[semconv.ATTR_MESSAGING_PROTOCOL] = protocol;
    attributes$1[attributes.NET_PEER_NAME] = getHostname(connectOptions?.hostname);
    attributes$1[attributes.NET_PEER_PORT] = getPort(connectOptions.port, protocol);
  } else {
    const censoredUrl = censorPassword(resolvedUrl);
    attributes$1[semconv.ATTR_MESSAGING_URL] = censoredUrl;
    try {
      const urlParts = new URL(censoredUrl);
      const protocol = getProtocol(urlParts.protocol);
      attributes$1[semconv.ATTR_MESSAGING_PROTOCOL] = protocol;
      attributes$1[attributes.NET_PEER_NAME] = getHostname(urlParts.hostname);
      attributes$1[attributes.NET_PEER_PORT] = getPort(urlParts.port ? parseInt(urlParts.port) : void 0, protocol);
    } catch {
    }
  }
  return attributes$1;
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
  const span = core.startInactiveSpan({
    name: `publish ${normalizedExchange}`,
    kind: api.SpanKind.PRODUCER,
    attributes: {
      ...channel.connection[CONNECTION_ATTRIBUTES],
      [semconv.ATTR_MESSAGING_DESTINATION]: exchange,
      [semconv.ATTR_MESSAGING_DESTINATION_KIND]: semconv.MESSAGING_DESTINATION_KIND_VALUE_TOPIC,
      [semconv.ATTR_MESSAGING_RABBITMQ_ROUTING_KEY]: routingKey,
      [semconv.OLD_ATTR_MESSAGING_MESSAGE_ID]: options?.messageId,
      [semconv.ATTR_MESSAGING_CONVERSATION_ID]: options?.correlationId,
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: PUBLISHER_ORIGIN
    }
  });
  const modifiedOptions = options ?? {};
  modifiedOptions.headers = modifiedOptions.headers ?? {};
  const traceData = core.getTraceData({ span });
  if (traceData["sentry-trace"]) {
    modifiedOptions.headers["sentry-trace"] = traceData["sentry-trace"];
  }
  if (traceData.baggage) {
    modifiedOptions.headers["baggage"] = traceData.baggage;
  }
  return { span, modifiedOptions };
}
function startConsumeSpan(queue, msg, channel) {
  return core.startInactiveSpan({
    name: `${queue} process`,
    kind: api.SpanKind.CONSUMER,
    attributes: {
      ...channel?.connection?.[CONNECTION_ATTRIBUTES],
      [semconv.ATTR_MESSAGING_DESTINATION]: msg.fields?.exchange,
      [semconv.ATTR_MESSAGING_DESTINATION_KIND]: semconv.MESSAGING_DESTINATION_KIND_VALUE_TOPIC,
      [semconv.ATTR_MESSAGING_RABBITMQ_ROUTING_KEY]: msg.fields?.routingKey,
      [semconv.ATTR_MESSAGING_OPERATION]: semconv.MESSAGING_OPERATION_VALUE_PROCESS,
      [semconv.OLD_ATTR_MESSAGING_MESSAGE_ID]: msg?.properties.messageId,
      [semconv.ATTR_MESSAGING_CONVERSATION_ID]: msg?.properties.correlationId,
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: CONSUMER_ORIGIN
    }
  });
}

exports.CHANNEL_CONSUME_TIMEOUT_TIMER = CHANNEL_CONSUME_TIMEOUT_TIMER;
exports.CHANNEL_IS_CONFIRM_PUBLISHING = CHANNEL_IS_CONFIRM_PUBLISHING;
exports.CHANNEL_SPANS_NOT_ENDED = CHANNEL_SPANS_NOT_ENDED;
exports.CONNECTION_ATTRIBUTES = CONNECTION_ATTRIBUTES;
exports.MESSAGE_STORED_SPAN = MESSAGE_STORED_SPAN;
exports.getConnectionAttributesFromServer = getConnectionAttributesFromServer;
exports.getConnectionAttributesFromUrl = getConnectionAttributesFromUrl;
exports.getHeaderAsString = getHeaderAsString;
exports.normalizeExchange = normalizeExchange;
exports.startConsumeSpan = startConsumeSpan;
exports.startPublishSpan = startPublishSpan;
//# sourceMappingURL=utils.js.map
