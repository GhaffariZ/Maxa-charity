import type { InstrumentationConfig } from '..';
export declare const langchainConfig: {
    channelName: string;
    module: {
        name: string;
        versionRange: string;
        filePath: string;
    };
    functionQuery: {
        methodName: string;
        kind: "Async";
    };
}[];
export declare const langchainEmbeddingsChannels: string[];
export declare const langchainChannels: {
    readonly LANGCHAIN_CHAT_MODEL_INVOKE: "orchestrion:@langchain/core:chatModelInvoke";
    readonly LANGCHAIN_CHAT_MODEL_STREAM: "orchestrion:@langchain/core:chatModelStream";
};
export declare const langchainSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=langchain.d.ts.map