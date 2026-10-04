import { CLOUD_REGION, RPC_SERVICE, RPC_METHOD, RPC_SYSTEM } from '@sentry/conventions/attributes';

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
    [RPC_SYSTEM]: "aws-api",
    [RPC_METHOD]: normalizedRequest.commandName,
    [RPC_SERVICE]: normalizedRequest.serviceName,
    [CLOUD_REGION]: normalizedRequest.region
  };
}

export { extractAttributesFromNormalizedRequest, normalizeV3Request, removeSuffixFromStringIfExists };
//# sourceMappingURL=utils.js.map
