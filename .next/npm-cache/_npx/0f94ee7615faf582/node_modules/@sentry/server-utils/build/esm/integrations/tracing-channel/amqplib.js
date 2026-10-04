import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, debug, waitForTracingChannelBinding, continueTrace, timestampInSeconds, startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, getTraceData, SPAN_STATUS_ERROR } from '@sentry/core';
import { MESSAGING_SYSTEM, NETWORK_PROTOCOL_NAME, SERVER_ADDRESS, SERVER_PORT, NET_PEER_NAME, NET_PEER_PORT, MESSAGING_MESSAGE_ID, MESSAGING_OPERATION_TYPE, MESSAGING_DESTINATION_NAME, NETWORK_PROTOCOL_VERSION, URL_FULL } from '@sentry/conventions/attributes';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../tracing-channel.js';

const INTEGRATION_NAME = "Amqplib";
const PUBLISHER_ORIGIN = "auto.amqplib.orchestrion.publisher";
const CONSUMER_ORIGIN = "auto.amqplib.orchestrion.consumer";
const ATTR_MESSAGING_OPERATION = "messaging.operation";
const ATTR_MESSAGING_DESTINATION = "messaging.destination";
const ATTR_MESSAGING_DESTINATION_KIND = "messaging.destination_kind";
const ATTR_MESSAGING_RABBITMQ_ROUTING_KEY = "messaging.rabbitmq.routing_key";
const ATTR_MESSAGING_PROTOCOL = "messaging.protocol";
const ATTR_MESSAGING_PROTOCOL_VERSION_LEGACY = "messaging.protocol_version";
const ATTR_MESSAGING_URL = "messaging.url";
const ATTR_MESSAGING_MESSAGE_ID = "messaging.message_id";
const ATTR_MESSAGING_CONVERSATION_ID_LEGACY = "messaging.conversation_id";
const ATTR_MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY = "messaging.rabbitmq.destination.routing_key";
const ATTR_MESSAGING_CONVERSATION_ID = "messaging.message.conversation_id";
const MESSAGING_DESTINATION_KIND_VALUE_TOPIC = "topic";
const MESSAGING_OPERATION_VALUE_PROCESS = "process";
const MESSAGING_OPERATION_VALUE_SEND = "send";
const CONSUME_TIMEOUT_MS = 1e3 * 60;
const END_OP = {
  Ack: "ack",
  AckAll: "ackAll",
  Reject: "reject",
  Nack: "nack",
  NackAll: "nackAll",
  ChannelClosed: "channel closed",
  ChannelError: "channel error",
  InstrumentationTimeout: "instrumentation timeout"
};
const MESSAGE_STORED_SPAN = /* @__PURE__ */ Symbol("sentry.amqplib.message.stored-span");
const CHANNEL_SPANS_NOT_ENDED = /* @__PURE__ */ Symbol("sentry.amqplib.channel.spans-not-ended");
const CHANNEL_CONSUME_TIMEOUT_TIMER = /* @__PURE__ */ Symbol("sentry.amqplib.channel.consume-timeout-timer");
const CHANNEL_CONSUMER_INFO = /* @__PURE__ */ Symbol("sentry.amqplib.channel.consumer-info");
const CHANNEL_IS_CONFIRM_PUBLISHING = /* @__PURE__ */ Symbol("sentry.amqplib.channel.is-confirm-publishing");
const CONNECTION_ATTRIBUTES = /* @__PURE__ */ Symbol("sentry.amqplib.connection.attributes");
const NOOP = () => {
};
let subscribed = false;
const _amqplibChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel || subscribed) {
        return;
      }
      subscribed = true;
      DEBUG_BUILD && debug.log("[orchestrion:amqplib] subscribing to amqplib tracing channels");
      waitForTracingChannelBinding(() => {
        subscribeConnect();
        subscribePublish();
        subscribeConfirmPublish();
        subscribeConsume();
        subscribeDispatch();
        subscribeSettle();
      });
    }
  };
});
function subscribePublish() {
  bindTracingChannelToSpan(diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_PUBLISH), (data) => {
    if (data.self?.[CHANNEL_IS_CONFIRM_PUBLISHING]) {
      return void 0;
    }
    return startPublishSpan(data);
  });
}
function subscribeConfirmPublish() {
  const channel = diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_CONFIRM_PUBLISH);
  bindTracingChannelToSpan(channel, (data) => {
    if (data.self) {
      data.self[CHANNEL_IS_CONFIRM_PUBLISHING] = true;
    }
    return startPublishSpan(data);
  });
  channel.end.subscribe((message) => {
    const self = message.self;
    if (self) {
      self[CHANNEL_IS_CONFIRM_PUBLISHING] = false;
    }
  });
}
function subscribeConsume() {
  const channel = diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_CONSUME);
  channel.start.subscribe(NOOP);
  channel.asyncEnd.subscribe((message) => {
    const data = message;
    const consumerChannel = data.self;
    const result = data.result;
    const consumerTag = result?.consumerTag;
    if (!consumerChannel || !consumerTag) {
      return;
    }
    ensureChannelState(consumerChannel);
    const queueArg = data.arguments[0];
    const queue = typeof queueArg === "string" ? queueArg : "<unknown>";
    const options = data.arguments[2];
    consumerChannel[CHANNEL_CONSUMER_INFO]?.set(consumerTag, { noAck: !!options?.noAck, queue });
  });
}
function subscribeDispatch() {
  bindTracingChannelToSpan(
    diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_DISPATCH),
    (data) => {
      const channel = data.self;
      const fields = data.arguments[0];
      const msg = data.arguments[1];
      if (!channel || !msg) {
        return void 0;
      }
      ensureChannelState(channel);
      const info = fields?.consumerTag ? channel[CHANNEL_CONSUMER_INFO]?.get(fields.consumerTag) : void 0;
      const queue = info?.queue ?? msg.fields?.routingKey ?? "<unknown>";
      const noAck = info?.noAck ?? false;
      const headers = msg.properties?.headers;
      const sentryTrace = getHeaderAsString(headers, "sentry-trace");
      const baggage = getHeaderAsString(headers, "baggage");
      const span = continueTrace({ sentryTrace, baggage }, () => startConsumeSpan(queue, msg, channel));
      if (!noAck) {
        channel[CHANNEL_SPANS_NOT_ENDED]?.push({ msg, timeOfConsume: timestampInSeconds() });
        msg[MESSAGE_STORED_SPAN] = span;
      }
      data._sentryNoAck = noAck;
      return span;
    },
    {
      // Manual-ack consumers: the span outlives the dispatch call and is ended by ack/nack/reject
      // (or timeout/close), so take ownership and don't let the helper end it here. noAck consumers
      // have no settle call, so let the helper end the span when dispatch returns.
      deferSpanEnd({ data }) {
        return !data._sentryNoAck;
      }
    }
  );
}
function subscribeSettle() {
  diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_ACK).start.subscribe((message) => handleAck(message, false, END_OP.Ack));
  diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_NACK).start.subscribe((message) => handleAck(message, true, END_OP.Nack));
  diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_REJECT).start.subscribe((message) => handleAck(message, true, END_OP.Reject));
  diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_ACK_ALL).start.subscribe((message) => {
    const data = message;
    if (data.self) {
      endAllSpansOnChannel(data.self, false, END_OP.AckAll, void 0);
    }
  });
  diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_NACK_ALL).start.subscribe((message) => {
    const data = message;
    if (data.self) {
      endAllSpansOnChannel(data.self, true, END_OP.NackAll, data.arguments[0]);
    }
  });
}
function subscribeConnect() {
  const channel = diagnosticsChannel.tracingChannel(CHANNELS.AMQPLIB_CONNECT);
  channel.start.subscribe(NOOP);
  channel.asyncEnd.subscribe((message) => {
    const data = message;
    const conn = data.result;
    if (!conn || typeof conn !== "object") {
      return;
    }
    conn[CONNECTION_ATTRIBUTES] = {
      ...getConnectionAttributesFromUrl(data.arguments?.[0]),
      ...getConnectionAttributesFromServer(conn)
    };
  });
}
function handleAck(data, isRejected, endOperation) {
  const channel = data.self;
  if (!channel) {
    return;
  }
  const message = data.arguments[0];
  if (!message) {
    return;
  }
  const allUpToOrRequeue = data.arguments[1];
  const requeue = data.arguments[2];
  const requeueResolved = endOperation === END_OP.Reject ? allUpToOrRequeue : requeue;
  const spansNotEnded = channel[CHANNEL_SPANS_NOT_ENDED] ?? [];
  const msgIndex = spansNotEnded.findIndex((msgDetails) => msgDetails.msg === message);
  if (msgIndex < 0) {
    endConsumerSpan(message, isRejected, endOperation, requeueResolved);
  } else if (endOperation !== END_OP.Reject && allUpToOrRequeue) {
    for (let i = 0; i <= msgIndex; i++) {
      endConsumerSpan(spansNotEnded[i].msg, isRejected, endOperation, requeueResolved);
    }
    spansNotEnded.splice(0, msgIndex + 1);
  } else {
    endConsumerSpan(message, isRejected, endOperation, requeueResolved);
    spansNotEnded.splice(msgIndex, 1);
  }
}
function ensureChannelState(channel) {
  if (Object.prototype.hasOwnProperty.call(channel, CHANNEL_SPANS_NOT_ENDED)) {
    return;
  }
  channel[CHANNEL_SPANS_NOT_ENDED] = [];
  channel[CHANNEL_CONSUMER_INFO] = /* @__PURE__ */ new Map();
  const timer = setInterval(() => checkConsumeTimeoutOnChannel(channel), CONSUME_TIMEOUT_MS);
  timer.unref?.();
  channel[CHANNEL_CONSUME_TIMEOUT_TIMER] = timer;
  if (typeof channel.on === "function") {
    channel.on("close", () => {
      endAllSpansOnChannel(channel, true, END_OP.ChannelClosed, void 0);
      clearConsumeTimeoutTimer(channel);
    });
    channel.on("error", () => {
      endAllSpansOnChannel(channel, true, END_OP.ChannelError, void 0);
      clearConsumeTimeoutTimer(channel);
    });
  }
}
function clearConsumeTimeoutTimer(channel) {
  const activeTimer = channel[CHANNEL_CONSUME_TIMEOUT_TIMER];
  if (activeTimer) {
    clearInterval(activeTimer);
    channel[CHANNEL_CONSUME_TIMEOUT_TIMER] = void 0;
  }
}
function checkConsumeTimeoutOnChannel(channel) {
  const currentTime = timestampInSeconds();
  const spansNotEnded = channel[CHANNEL_SPANS_NOT_ENDED] ?? [];
  let i;
  for (i = 0; i < spansNotEnded.length; i++) {
    const currMessage = spansNotEnded[i];
    const timeFromConsumeMs = (currentTime - currMessage.timeOfConsume) * 1e3;
    if (timeFromConsumeMs < CONSUME_TIMEOUT_MS) {
      break;
    }
    endConsumerSpan(currMessage.msg, null, END_OP.InstrumentationTimeout, true);
  }
  spansNotEnded.splice(0, i);
}
function endAllSpansOnChannel(channel, isRejected, operation, requeue) {
  const spansNotEnded = channel[CHANNEL_SPANS_NOT_ENDED] ?? [];
  spansNotEnded.forEach((msgDetails) => {
    endConsumerSpan(msgDetails.msg, isRejected, operation, requeue);
  });
  channel[CHANNEL_SPANS_NOT_ENDED] = [];
}
function endConsumerSpan(message, isRejected, operation, requeue) {
  const storedSpan = message[MESSAGE_STORED_SPAN];
  if (!storedSpan) {
    return;
  }
  if (isRejected !== false) {
    storedSpan.setStatus({
      code: SPAN_STATUS_ERROR,
      message: operation !== END_OP.ChannelClosed && operation !== END_OP.ChannelError ? `${operation} called on message${requeue === true ? " with requeue" : requeue === false ? " without requeue" : ""}` : operation
    });
  }
  storedSpan.end();
  message[MESSAGE_STORED_SPAN] = void 0;
}
function startPublishSpan(data) {
  const exchangeArg = data.arguments[0];
  const routingKeyArg = data.arguments[1];
  const exchange = typeof exchangeArg === "string" ? exchangeArg : "";
  const routingKey = typeof routingKeyArg === "string" ? routingKeyArg : "";
  let options = data.arguments[3];
  const span = startInactiveSpan({
    name: `publish ${normalizeExchange(exchange)}`,
    op: "message",
    kind: SPAN_KIND.PRODUCER,
    attributes: {
      ...getStoredConnectionAttributes(data.self),
      [ATTR_MESSAGING_DESTINATION]: exchange,
      // TODO(v11) remove this attribute
      [MESSAGING_DESTINATION_NAME]: exchange,
      [ATTR_MESSAGING_DESTINATION_KIND]: MESSAGING_DESTINATION_KIND_VALUE_TOPIC,
      // TODO(v11) remove this attribute
      [ATTR_MESSAGING_RABBITMQ_ROUTING_KEY]: routingKey,
      // TODO(v11) remove this attribute
      [ATTR_MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY]: routingKey,
      [MESSAGING_OPERATION_TYPE]: MESSAGING_OPERATION_VALUE_SEND,
      [ATTR_MESSAGING_MESSAGE_ID]: options?.messageId,
      // todo(v11) remove this attribute
      [MESSAGING_MESSAGE_ID]: options?.messageId,
      [ATTR_MESSAGING_CONVERSATION_ID_LEGACY]: options?.correlationId,
      // todo(v11) remove this attribute
      [ATTR_MESSAGING_CONVERSATION_ID]: options?.correlationId,
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: PUBLISHER_ORIGIN
    }
  });
  if (!options || typeof options !== "object") {
    options = {};
    data.arguments[3] = options;
  }
  const headers = options.headers && typeof options.headers === "object" ? options.headers : options.headers = {};
  const traceData = getTraceData({ span });
  if (traceData["sentry-trace"]) {
    headers["sentry-trace"] = traceData["sentry-trace"];
  }
  if (traceData.baggage) {
    headers["baggage"] = traceData.baggage;
  }
  return span;
}
function startConsumeSpan(queue, msg, channel) {
  return startInactiveSpan({
    name: `${queue} process`,
    op: "message",
    kind: SPAN_KIND.CONSUMER,
    attributes: {
      ...getStoredConnectionAttributes(channel),
      [ATTR_MESSAGING_DESTINATION]: msg.fields?.exchange,
      // TODO(v11) remove this attribute
      [MESSAGING_DESTINATION_NAME]: msg.fields?.exchange,
      [ATTR_MESSAGING_DESTINATION_KIND]: MESSAGING_DESTINATION_KIND_VALUE_TOPIC,
      // TODO(v11) remove this attribute
      [ATTR_MESSAGING_RABBITMQ_ROUTING_KEY]: msg.fields?.routingKey,
      // TODO(v11) remove this attribute
      [ATTR_MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY]: msg.fields?.routingKey,
      [ATTR_MESSAGING_OPERATION]: MESSAGING_OPERATION_VALUE_PROCESS,
      // TODO(v11) remove this attribute
      [MESSAGING_OPERATION_TYPE]: MESSAGING_OPERATION_VALUE_PROCESS,
      [ATTR_MESSAGING_MESSAGE_ID]: msg.properties?.messageId,
      // todo(v11) remove this attribute
      [MESSAGING_MESSAGE_ID]: msg.properties?.messageId,
      [ATTR_MESSAGING_CONVERSATION_ID_LEGACY]: msg.properties?.correlationId,
      // todo(v11) remove this attribute
      [ATTR_MESSAGING_CONVERSATION_ID]: msg.properties?.correlationId,
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: CONSUMER_ORIGIN
    }
  });
}
function getStoredConnectionAttributes(channel) {
  const connection = channel?.connection;
  const stored = connection?.[CONNECTION_ATTRIBUTES];
  if (stored) {
    return stored;
  }
  const product = connection?.serverProperties?.product ?? connection?.connection?.serverProperties?.product;
  if (typeof product === "string" && product) {
    return { [MESSAGING_SYSTEM]: product.toLowerCase() };
  }
  return {};
}
function getConnectionAttributesFromServer(conn) {
  const product = conn.serverProperties?.product ?? conn.connection?.serverProperties?.product;
  if (typeof product === "string" && product) {
    return { [MESSAGING_SYSTEM]: product.toLowerCase() };
  }
  return {};
}
function getConnectionAttributesFromUrl(url) {
  const attributes = {
    // The only protocol supported by the instrumented library.
    [ATTR_MESSAGING_PROTOCOL_VERSION_LEGACY]: "0.9.1",
    // TODO(v11): remove this attribute
    [NETWORK_PROTOCOL_VERSION]: "0.9.1"
  };
  const resolvedUrl = url || "amqp://localhost";
  if (typeof resolvedUrl === "object") {
    const connectOptions = resolvedUrl;
    const protocol = getProtocol(connectOptions.protocol);
    const hostname = getHostname(connectOptions.hostname);
    const port = getPort(connectOptions.port, protocol);
    attributes[ATTR_MESSAGING_PROTOCOL] = protocol;
    attributes[NETWORK_PROTOCOL_NAME] = protocol;
    attributes[SERVER_ADDRESS] = hostname;
    attributes[SERVER_PORT] = port;
    attributes[NET_PEER_NAME] = hostname;
    attributes[NET_PEER_PORT] = port;
  } else if (typeof resolvedUrl === "string") {
    const censoredUrl = censorPassword(resolvedUrl);
    attributes[ATTR_MESSAGING_URL] = censoredUrl;
    attributes[URL_FULL] = censoredUrl;
    try {
      const urlParts = new URL(censoredUrl);
      const protocol = getProtocol(urlParts.protocol);
      const hostname = getHostname(urlParts.hostname);
      const port = getPort(urlParts.port ? parseInt(urlParts.port, 10) : void 0, protocol);
      attributes[ATTR_MESSAGING_PROTOCOL] = protocol;
      attributes[NETWORK_PROTOCOL_NAME] = protocol;
      attributes[SERVER_ADDRESS] = hostname;
      attributes[SERVER_PORT] = port;
      attributes[NET_PEER_NAME] = hostname;
      attributes[NET_PEER_PORT] = port;
    } catch {
    }
  }
  return attributes;
}
function normalizeExchange(exchangeName) {
  return exchangeName !== "" ? exchangeName : "<default>";
}
function censorPassword(url) {
  return url.replace(/:[^:@/]*@/, ":***@");
}
function getPort(portFromUrl, resolvedProtocol) {
  return portFromUrl || (resolvedProtocol === "AMQP" ? 5672 : 5671);
}
function getProtocol(protocolFromUrl) {
  const resolvedProtocol = protocolFromUrl || "amqp";
  const noEndingColon = resolvedProtocol.endsWith(":") ? resolvedProtocol.substring(0, resolvedProtocol.length - 1) : resolvedProtocol;
  return noEndingColon.toUpperCase();
}
function getHostname(hostnameFromUrl) {
  return hostnameFromUrl || "localhost";
}
function getHeaderAsString(headers, key) {
  const value = headers?.[key];
  if (value == null) {
    return void 0;
  }
  return Array.isArray(value) ? String(value[0]) : String(value);
}
const amqplibChannelIntegration = defineIntegration(_amqplibChannelIntegration);

export { amqplibChannelIntegration };
//# sourceMappingURL=amqplib.js.map
