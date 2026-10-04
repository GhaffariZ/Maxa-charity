import { SPAN_KIND, getTraceData } from '@sentry/core';
import { MESSAGING_DESTINATION, MESSAGING_DESTINATION_NAME, AWS_SNS_TOPIC_ARN, MESSAGING_SYSTEM } from '@sentry/conventions/attributes';
import { MESSAGING_DESTINATION_KIND_VALUE_TOPIC, ATTR_MESSAGING_DESTINATION_KIND } from '../constants.js';
import { injectPropagationContext } from './MessageAttributes.js';

class SnsServiceExtension {
  requestPreSpanHook(request) {
    let spanKind = SPAN_KIND.CLIENT;
    let spanName = `SNS ${request.commandName}`;
    const spanAttributes = {
      [MESSAGING_SYSTEM]: "aws.sns"
    };
    if (request.commandName === "Publish") {
      spanKind = SPAN_KIND.PRODUCER;
      spanAttributes[ATTR_MESSAGING_DESTINATION_KIND] = MESSAGING_DESTINATION_KIND_VALUE_TOPIC;
      const { TopicArn, TargetArn, PhoneNumber } = request.commandInput;
      const destinationName = extractDestinationName(TopicArn, TargetArn, PhoneNumber);
      spanAttributes[MESSAGING_DESTINATION] = destinationName;
      spanAttributes[MESSAGING_DESTINATION_NAME] = TopicArn || TargetArn || PhoneNumber || "unknown";
      spanName = `${PhoneNumber ? "phone_number" : destinationName} send`;
    }
    const topicArn = request.commandInput?.TopicArn;
    if (topicArn) {
      spanAttributes[AWS_SNS_TOPIC_ARN] = topicArn;
    }
    return {
      spanAttributes,
      spanKind,
      spanName
    };
  }
  requestPostSpanHook(request, span) {
    if (request.commandName === "Publish") {
      const origMessageAttributes = request.commandInput.MessageAttributes ?? {};
      request.commandInput.MessageAttributes = injectPropagationContext(origMessageAttributes, getTraceData({ span }));
    }
  }
  responseHook(response, span) {
    const topicArn = response.data?.TopicArn;
    if (topicArn) {
      span.setAttribute(AWS_SNS_TOPIC_ARN, topicArn);
    }
  }
}
function extractDestinationName(topicArn, targetArn, phoneNumber) {
  if (topicArn || targetArn) {
    const arn = topicArn ?? targetArn;
    try {
      return arn.substring(arn.lastIndexOf(":") + 1);
    } catch {
      return arn;
    }
  } else if (phoneNumber) {
    return phoneNumber;
  } else {
    return "unknown";
  }
}

export { SnsServiceExtension };
//# sourceMappingURL=sns.js.map
