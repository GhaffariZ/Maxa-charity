import { SPAN_KIND, getTraceData, propagationContextFromHeaders } from '@sentry/core';
import { MESSAGING_OPERATION_TYPE, MESSAGING_BATCH_MESSAGE_COUNT, MESSAGING_MESSAGE_ID, URL_FULL, MESSAGING_DESTINATION_NAME, MESSAGING_SYSTEM } from '@sentry/conventions/attributes';
import { addPropagationFieldsToAttributeNames, injectPropagationContext, extractPropagationHeaders } from './MessageAttributes.js';

class SqsServiceExtension {
  requestPreSpanHook(request) {
    const queueUrl = extractQueueUrl(request.commandInput);
    const queueName = extractQueueNameFromUrl(queueUrl);
    let spanKind = SPAN_KIND.CLIENT;
    let spanName;
    const spanAttributes = {
      [MESSAGING_SYSTEM]: "aws_sqs",
      [MESSAGING_DESTINATION_NAME]: queueName,
      [URL_FULL]: queueUrl
    };
    switch (request.commandName) {
      case "ReceiveMessage":
        {
          spanKind = SPAN_KIND.CONSUMER;
          spanName = `${queueName} receive`;
          spanAttributes[MESSAGING_OPERATION_TYPE] = "receive";
          request.commandInput.MessageAttributeNames = addPropagationFieldsToAttributeNames(
            request.commandInput.MessageAttributeNames
          );
        }
        break;
      case "SendMessage":
      case "SendMessageBatch":
        spanKind = SPAN_KIND.PRODUCER;
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
          request.commandInput.MessageAttributes = injectPropagationContext(
            origMessageAttributes,
            getTraceData({ span })
          );
        }
        break;
      case "SendMessageBatch":
        {
          const entries = request.commandInput?.Entries;
          if (Array.isArray(entries)) {
            const traceData = getTraceData({ span });
            entries.forEach((messageParams) => {
              messageParams.MessageAttributes = injectPropagationContext(
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
        span.setAttribute(MESSAGING_MESSAGE_ID, response?.data?.MessageId);
        break;
      case "SendMessageBatch":
        break;
      case "ReceiveMessage": {
        const messages = response?.data?.Messages || [];
        span.setAttribute(MESSAGING_BATCH_MESSAGE_COUNT, messages.length);
        for (const message of messages) {
          linkReceivedMessageToProducer(span, message);
        }
        break;
      }
    }
  }
}
function linkReceivedMessageToProducer(span, message) {
  const headers = extractPropagationHeaders(message);
  if (!headers) {
    return;
  }
  const { parentSpanId, traceId, sampled } = propagationContextFromHeaders(headers.sentryTrace, headers.baggage);
  if (traceId && parentSpanId) {
    span.addLink({
      context: {
        traceId,
        spanId: parentSpanId,
        traceFlags: sampled ? 1 : 0
      },
      attributes: {
        [MESSAGING_MESSAGE_ID]: message.MessageId
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

export { SqsServiceExtension };
//# sourceMappingURL=sqs.js.map
