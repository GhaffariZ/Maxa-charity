Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const MessageAttributes = require('./MessageAttributes.js');

class SqsServiceExtension {
  requestPreSpanHook(request) {
    const queueUrl = extractQueueUrl(request.commandInput);
    const queueName = extractQueueNameFromUrl(queueUrl);
    let spanKind = core.SPAN_KIND.CLIENT;
    let spanName;
    const spanAttributes = {
      [attributes.MESSAGING_SYSTEM]: "aws_sqs",
      [attributes.MESSAGING_DESTINATION_NAME]: queueName,
      [attributes.URL_FULL]: queueUrl
    };
    switch (request.commandName) {
      case "ReceiveMessage":
        {
          spanKind = core.SPAN_KIND.CONSUMER;
          spanName = `${queueName} receive`;
          spanAttributes[attributes.MESSAGING_OPERATION_TYPE] = "receive";
          request.commandInput.MessageAttributeNames = MessageAttributes.addPropagationFieldsToAttributeNames(
            request.commandInput.MessageAttributeNames
          );
        }
        break;
      case "SendMessage":
      case "SendMessageBatch":
        spanKind = core.SPAN_KIND.PRODUCER;
        spanName = `${queueName} send`;
        break;
    }
    return {
      spanAttributes,
      spanKind,
      spanName
    };
  }
  requestPostSpanHook(request, span) {
    switch (request.commandName) {
      case "SendMessage":
        {
          const origMessageAttributes = request.commandInput.MessageAttributes ?? {};
          request.commandInput.MessageAttributes = MessageAttributes.injectPropagationContext(
            origMessageAttributes,
            core.getTraceData({ span })
          );
        }
        break;
      case "SendMessageBatch":
        {
          const entries = request.commandInput?.Entries;
          if (Array.isArray(entries)) {
            const traceData = core.getTraceData({ span });
            entries.forEach((messageParams) => {
              messageParams.MessageAttributes = MessageAttributes.injectPropagationContext(
                messageParams.MessageAttributes ?? {},
                traceData
              );
            });
          }
        }
        break;
    }
  }
  responseHook(response, span) {
    switch (response.request.commandName) {
      case "SendMessage":
        span.setAttribute(attributes.MESSAGING_MESSAGE_ID, response?.data?.MessageId);
        break;
      case "SendMessageBatch":
        break;
      case "ReceiveMessage": {
        const messages = response?.data?.Messages || [];
        span.setAttribute(attributes.MESSAGING_BATCH_MESSAGE_COUNT, messages.length);
        for (const message of messages) {
          linkReceivedMessageToProducer(span, message);
        }
        break;
      }
    }
  }
}
function linkReceivedMessageToProducer(span, message) {
  const headers = MessageAttributes.extractPropagationHeaders(message);
  if (!headers) {
    return;
  }
  const { parentSpanId, traceId, sampled } = core.propagationContextFromHeaders(headers.sentryTrace, headers.baggage);
  if (traceId && parentSpanId) {
    span.addLink({
      context: {
        traceId,
        spanId: parentSpanId,
        traceFlags: sampled ? 1 : 0
      },
      attributes: {
        [attributes.MESSAGING_MESSAGE_ID]: message.MessageId
      }
    });
  }
}
function extractQueueUrl(commandInput) {
  return commandInput?.QueueUrl;
}
function extractQueueNameFromUrl(queueUrl) {
  if (!queueUrl) return void 0;
  const segments = queueUrl.split("/");
  if (segments.length === 0) return void 0;
  return segments[segments.length - 1];
}

exports.SqsServiceExtension = SqsServiceExtension;
//# sourceMappingURL=sqs.js.map
