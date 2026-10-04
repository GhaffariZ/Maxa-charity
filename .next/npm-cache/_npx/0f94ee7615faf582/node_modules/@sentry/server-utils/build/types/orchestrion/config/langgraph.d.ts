import type { InstrumentationConfig } from '..';
export declare const langgraphConfig: ({
    channelName: string;
    module: import("../apmTypes").ModuleMatcher;
    functionQuery: {
        className: string;
        methodName: string;
        kind: "Sync";
    };
} | {
    channelName: string;
    module: import("../apmTypes").ModuleMatcher;
    functionQuery: {
        functionName: string;
        kind: "Sync";
    };
})[];
export declare const langgraphChannels: {
    readonly LANGGRAPH_STATE_GRAPH_COMPILE: "orchestrion:@langchain/langgraph:stateGraphCompile";
    readonly LANGGRAPH_CREATE_REACT_AGENT: "orchestrion:@langchain/langgraph:createReactAgent";
};
export declare const langgraphSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=langgraph.d.ts.map