Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');

function removeSuffixFromStringIfExists(str, suffixToRemove) {
  const suffixLength = suffixToRemove.length;
  return str?.slice(-suffixLength) === suffixToRemove ? str.slice(0, -suffixLength) : str;
}
function normalizeV3Request(serviceName, commandNameWithSuffix, commandInput, region) {
  return {
    serviceName: serviceName?.replace(/\s+/g, ""),
    commandName: removeSuffixFromStringIfExists(commandNameWithSuffix, "Command"),
    commandInput,
    region
  };
}
function extractAttributesFromNormalizedRequest(normalizedRequest) {
  return {
    // oxlint-disable-next-line typescript/no-deprecated -- old-semconv rpc.system, matched to the OTel aws-sdk integration
    [attributes.RPC_SYSTEM]: "aws-api",
    [attributes.RPC_METHOD]: normalizedRequest.commandName,
    [attributes.RPC_SERVICE]: normalizedRequest.serviceName,
    [attributes.CLOUD_REGION]: normalizedRequest.region
  };
}

exports.extractAttributesFromNormalizedRequest = extractAttributesFromNormalizedRequest;
exports.normalizeV3Request = normalizeV3Request;
exports.removeSuffixFromStringIfExists = removeSuffixFromStringIfExists;
//# sourceMappingURL=utils.js.map
