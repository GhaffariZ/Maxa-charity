import type { InstrumentationConfig } from '../apmTypes';
export declare const awsSdkConfig: {
    channelName: string;
    module: {
        name: string;
        versionRange: string;
        filePath: string;
    };
    functionQuery: {
        className: string;
        methodName: string;
        kind: "Async";
    };
}[];
export declare const awsSdkChannels: {
    readonly AWS_SMITHY_CORE_SEND: "orchestrion:@smithy/core:send";
    readonly AWS_SMITHY_CLIENT_SEND: "orchestrion:@smithy/smithy-client:send";
    readonly AWS_SDK_SMITHY_CLIENT_SEND: "orchestrion:@aws-sdk/smithy-client:send";
};
export declare const awsSdkSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=aws-sdk.d.ts.map