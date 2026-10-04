Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const subscribeInjection = require('./subscribe-injection.js');

const awsSdkConfig = [
  {
    channelName: "send",
    module: { name: "@smithy/core", versionRange: ">=3.24.0 <4", filePath: "dist-cjs/submodules/client/index.js" },
    functionQuery: { className: "Client", methodName: "send", kind: "Async" }
  },
  {
    channelName: "send",
    module: { name: "@smithy/smithy-client", versionRange: ">=1.0.3 <5", filePath: "dist-cjs/index.js" },
    functionQuery: { className: "Client", methodName: "send", kind: "Async" }
  },
  {
    channelName: "send",
    module: { name: "@aws-sdk/smithy-client", versionRange: "^3.1.0", filePath: "dist-cjs/index.js" },
    functionQuery: { className: "Client", methodName: "send", kind: "Async" }
  }
];
const awsSdkChannels = {
  AWS_SMITHY_CORE_SEND: "orchestrion:@smithy/core:send",
  AWS_SMITHY_CLIENT_SEND: "orchestrion:@smithy/smithy-client:send",
  AWS_SDK_SMITHY_CLIENT_SEND: "orchestrion:@aws-sdk/smithy-client:send"
};
const awsSdkSubscribeInjection = subscribeInjection.toSubscribeInjections(awsSdkConfig);

exports.awsSdkChannels = awsSdkChannels;
exports.awsSdkConfig = awsSdkConfig;
exports.awsSdkSubscribeInjection = awsSdkSubscribeInjection;
//# sourceMappingURL=aws-sdk.js.map
