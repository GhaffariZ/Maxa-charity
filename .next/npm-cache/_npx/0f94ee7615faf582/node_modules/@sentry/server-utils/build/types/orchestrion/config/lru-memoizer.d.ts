import type { InstrumentationConfig } from '..';
export declare const lruMemoizerConfig: {
    channelName: string;
    module: {
        name: string;
        versionRange: string;
        filePath: string;
    };
    functionQuery: {
        functionName: string;
        kind: "Callback";
    };
}[];
export declare const lruMemoizerChannels: {
    readonly LRU_MEMOIZER_LOAD: "orchestrion:lru-memoizer:load";
};
export declare const lruMemoizerSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=lru-memoizer.d.ts.map