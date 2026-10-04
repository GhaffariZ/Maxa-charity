Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const constants = require('../constants.js');
const MessageAttributes = require('./MessageAttributes.js');

class SnsServiceExtension {
  requestPreSpanHook(request) {
    let spanKind = core.SPAN_KIND.CLIENT;
    let spanName = `SNS ${request.commandName}`;
    const spanAttributes = {
      [attributes.MESSAGING_SYSTEM]: "aws.sns"
    };
    if (request.commandName === "Publish") {
      spanKind = core.SPAN_KIND.PRODUCER;
      spanAttributes[constants.ATTR_MESSAGING_DESTINATION_KIND] = constants.MESSAGING_DESTINATION_KIND_VALUE_TOPIC;
      const { TopicArn, TargetArn, PhoneNumber } = request.commandInput;
      const destinationName = extractDestinationName(TopicArn, TargetArn, PhoneNumber);
      spanAttributes[attributes.MESSAGING_DESTINATION] = destinationName;
      spanAttributes[attributes.MESSAGING_DESTINATION_NAME] = TopicArn || TargetArn || PhoneNumber || "unknown";
      spanName = `${PhoneNumber ? "phone_number" : destinationName} send`;
    }
    const topicArn = request.commandInput?.TopicArn;
    if (topicArn) {
      spanAttributes[attributes.AWS_SNS_TOPIC_ARN] = topicArn;
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
      request.commandInput.MessageAttributes = MessageAttributes.injectPropagationContext(origMessageAttributes, core.getTraceData({ span }));
    }
  }
  responseHook(response, span) {
    const topicArn = response.data?.TopicArn;
    if (topicArn) {
      span.setAttribute(attributes.AWS_SNS_TOPIC_ARN, topicArn);
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

exports.SnsServiceExtension = SnsServiceExtension;
//# sourceMappingURL=sns.js.map
