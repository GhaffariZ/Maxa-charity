import type { InstrumentationConfig } from '..';
export declare const anthropicAiConfig: ({
    channelName: string;
    module: {
        name: string;
        versionRange: string;
        filePath: string;
    };
    functionQuery: {
        className: string;
        methodName: string;
        kind: "Auto";
    };
} | {
    channelName: string;
    module: {
        name: string;
        versionRange: string;
        filePath: string;
    };
    functionQuery: {
        className: string;
        methodName: string;
        kind: "Sync";
    };
})[];
export declare const anthropicAiChannels: {
    readonly ANTHROPIC_CHAT: "orchestrion:@anthropic-ai/sdk:chat";
    readonly ANTHROPIC_MODELS: "orchestrion:@anthropic-ai/sdk:models";
    readonly ANTHROPIC_MESSAGES_STREAM: "orchestrion:@anthropic-ai/sdk:messages-stream";
};
export declare const anthropicAiSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=anthropic-ai.d.ts.map