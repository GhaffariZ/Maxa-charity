import { timestampInSeconds, continueTrace, withActiveSpan, SPAN_STATUS_ERROR } from '@sentry/core';
import { EndOperation } from './types.js';
import { getConnectionAttributesFromUrl, getConnectionAttributesFromServer, CONNECTION_ATTRIBUTES, CHANNEL_IS_CONFIRM_PUBLISHING, startPublishSpan, CHANNEL_SPANS_NOT_ENDED, CHANNEL_CONSUME_TIMEOUT_TIMER, getHeaderAsString, startConsumeSpan, MESSAGE_STORED_SPAN } from './utils.js';

const CONSUME_TIMEOUT_MS = 1e3 * 60;
function endConsumerSpan(message, isRejected, operation, requeue) {
  const storedSpan = message[MESSAGE_STORED_SPAN];
  if (!storedSpan) {
    return;
  }
  if (isRejected !== false) {
    storedSpan.setStatus({
      code: SPAN_STATUS_ERROR,
      message: operation !== EndOperation.ChannelClosed && operation !== EndOperation.ChannelError ? `${operation} called on message${requeue === true ? " with requeue" : requeue === false ? " without requeue" : ""}` : operation
    });
  }
  storedSpan.end();
  message[MESSAGE_STORED_SPAN] = void 0;
}
function endAllSpansOnChannel(channel, isRejected, operation, requeue) {
  const spansNotEnded = channel[CHANNEL_SPANS_NOT_ENDED] ?? [];
  spansNotEnded.forEach((msgDetails) => {
    endConsumerSpan(msgDetails.msg, isRejected, operation, requeue);
  });
  channel[CHANNEL_SPANS_NOT_ENDED] = [];
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
    endConsumerSpan(currMessage.msg, null, EndOperation.InstrumentationTimeout, true);
  }
  spansNotEnded.splice(0, i);
}
function getConnectPatch(original) {
  return function patchedConnect(url, socketOptions, openCallback) {
    return original.call(this, url, socketOptions, function(err, conn) {
      if (err == null) {
        const urlAttributes = getConnectionAttributesFromUrl(url);
        const serverAttributes = getConnectionAttributesFromServer(conn);
        conn[CONNECTION_ATTRIBUTES] = {
          ...urlAttributes,
          ...serverAttributes
        };
      }
      openCallback.apply(this, arguments);
    });
  };
}
function getChannelEmitPatch(original) {
  return function emit(eventName) {
    if (eventName === "close") {
      endAllSpansOnChannel(this, true, EndOperation.ChannelClosed, void 0);
      const activeTimer = this[CHANNEL_CONSUME_TIMEOUT_TIMER];
      if (activeTimer) {
        clearInterval(activeTimer);
      }
      this[CHANNEL_CONSUME_TIMEOUT_TIMER] = void 0;
    } else if (eventName === "error") {
      endAllSpansOnChannel(this, true, EndOperation.ChannelError, void 0);
    }
    return original.apply(this, arguments);
  };
}
function getAckAllPatch(isRejected, endOperation) {
  return (original) => function ackAll(requeueOrEmpty) {
    endAllSpansOnChannel(this, isRejected, endOperation, requeueOrEmpty);
    return original.apply(this, arguments);
  };
}
function getAckPatch(isRejected, endOperation) {
  return (original) => function ack(message, allUpToOrRequeue, requeue) {
    const channel = this;
    const requeueResolved = endOperation === EndOperation.Reject ? allUpToOrRequeue : requeue;
    const spansNotEnded = channel[CHANNEL_SPANS_NOT_ENDED] ?? [];
    const msgIndex = spansNotEnded.findIndex((msgDetails) => msgDetails.msg === message);
    if (msgIndex < 0) {
      endConsumerSpan(message, isRejected, endOperation, requeueResolved);
    } else if (endOperation !== EndOperation.Reject && allUpToOrRequeue) {
      for (let i = 0; i <= msgIndex; i++) {
        endConsumerSpan(spansNotEnded[i].msg, isRejected, endOperation, requeueResolved);
      }
      spansNotEnded.splice(0, msgIndex + 1);
    } else {
      endConsumerSpan(message, isRejected, endOperation, requeueResolved);
      spansNotEnded.splice(msgIndex, 1);
    }
    return original.apply(this, arguments);
  };
}
function getConsumePatch(original) {
  return function consume(queue, onMessage, options) {
    const channel = this;
    if (!Object.prototype.hasOwnProperty.call(channel, CHANNEL_SPANS_NOT_ENDED)) {
      const timer = setInterval(() => {
        checkConsumeTimeoutOnChannel(channel);
      }, CONSUME_TIMEOUT_MS);
      timer.unref();
      channel[CHANNEL_CONSUME_TIMEOUT_TIMER] = timer;
      channel[CHANNEL_SPANS_NOT_ENDED] = [];
    }
    const patchedOnMessage = function(msg) {
      if (!msg) {
        return onMessage.call(this, msg);
      }
      const headers = msg.properties.headers ?? {};
      const sentryTrace = getHeaderAsString(headers, "sentry-trace");
      const baggage = getHeaderAsString(headers, "baggage");
      continueTrace({ sentryTrace, baggage }, () => {
        const span = startConsumeSpan(queue, msg, channel);
        if (!options?.noAck) {
          channel[CHANNEL_SPANS_NOT_ENDED].push({ msg, timeOfConsume: timestampInSeconds() });
          msg[MESSAGE_STORED_SPAN] = span;
        }
        withActiveSpan(span, () => {
          onMessage.call(this, msg);
        });
        if (options?.noAck) {
          span.end();
        }
      });
    };
    const callArgs = Array.prototype.slice.call(arguments);
    callArgs[1] = patchedOnMessage;
    return original.apply(this, callArgs);
  };
}
function getConfirmedPublishPatch(original) {
  return function confirmedPublish(exchange, routingKey, content, options, callback) {
    const channel = this;
    const { span, modifiedOptions } = startPublishSpan(exchange, routingKey, channel, options);
    const patchedOnConfirm = function(err, ok) {
      try {
        withActiveSpan(span, () => {
          callback?.call(this, err, ok);
        });
      } finally {
        if (err) {
          span.setStatus({ code: SPAN_STATUS_ERROR, message: "message confirmation has been nack'ed" });
        }
        span.end();
      }
    };
    const argumentsCopy = [...arguments];
    argumentsCopy[3] = modifiedOptions;
    argumentsCopy[4] = patchedOnConfirm;
    channel[CHANNEL_IS_CONFIRM_PUBLISHING] = true;
    try {
      return original.apply(this, argumentsCopy);
    } finally {
      channel[CHANNEL_IS_CONFIRM_PUBLISHING] = false;
    }
  };
}
function getPublishPatch(original) {
  return function publish(exchange, routingKey, content, options) {
    if (this[CHANNEL_IS_CONFIRM_PUBLISHING]) {
      return original.apply(this, arguments);
    }
    const channel = this;
    const { span, modifiedOptions } = startPublishSpan(exchange, routingKey, channel, options);
    const argumentsCopy = [...arguments];
    argumentsCopy[3] = modifiedOptions;
    const originalRes = original.apply(this, argumentsCopy);
    span.end();
    return originalRes;
  };
}

export { getAckAllPatch, getAckPatch, getChannelEmitPatch, getConfirmedPublishPatch, getConnectPatch, getConsumePatch, getPublishPatch };
//# sourceMappingURL=patches.js.map
