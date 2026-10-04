Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const debugBuild = require('../../../../debug-build.js');

const MAX_MESSAGE_ATTRIBUTES = 10;
const SENTRY_TRACE_HEADER = "sentry-trace";
const BAGGAGE_HEADER = "baggage";
const PROPAGATION_FIELDS = [SENTRY_TRACE_HEADER, BAGGAGE_HEADER];
function injectPropagationContext(attributesMap, traceData) {
  const attributes = attributesMap ?? {};
  const headerKeys = Object.keys(traceData);
  if (Object.keys(attributes).length + headerKeys.length <= MAX_MESSAGE_ATTRIBUTES) {
    for (const key of headerKeys) {
      const value = traceData[key];
      if (value) {
        attributes[key] = { DataType: "String", StringValue: value };
      }
    }
  } else {
    debugBuild.DEBUG_BUILD && core.debug.warn(
      "[orchestrion:aws-sdk] cannot set trace propagation on SQS/SNS message due to maximum amount of MessageAttributes"
    );
  }
  return attributes;
}
function extractPropagationHeaders(message) {
  const carrier = message.MessageAttributes ?? {};
  const sentryTrace = carrier[SENTRY_TRACE_HEADER]?.StringValue ?? carrier[SENTRY_TRACE_HEADER]?.Value;
  if (!sentryTrace) {
    return void 0;
  }
  return {
    sentryTrace,
    baggage: carrier[BAGGAGE_HEADER]?.StringValue ?? carrier[BAGGAGE_HEADER]?.Value
  };
}
function addPropagationFieldsToAttributeNames(messageAttributeNames = []) {
  return core.uniq([...messageAttributeNames, ...PROPAGATION_FIELDS]);
}

exports.MAX_MESSAGE_ATTRIBUTES = MAX_MESSAGE_ATTRIBUTES;
exports.addPropagationFieldsToAttributeNames = addPropagationFieldsToAttributeNames;
exports.extractPropagationHeaders = extractPropagationHeaders;
exports.injectPropagationContext = injectPropagationContext;
//# sourceMappingURL=MessageAttributes.js.map
