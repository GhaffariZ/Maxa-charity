import { toSubscribeInjections } from './subscribe-injection.js';

const module$1 = (filePath) => ({
  name: "@langchain/langgraph",
  versionRange: ">=0.0.0 <2.0.0",
  filePath
});
const compileConfig = ["dist/graph/state.cjs", "dist/graph/state.js"].map((filePath) => ({
  channelName: "stateGraphCompile",
  module: module$1(filePath),
  functionQuery: { className: "StateGraph", methodName: "compile", kind: "Sync" }
}));
const createReactAgentConfig = ["dist/prebuilt/react_agent_executor.cjs", "dist/prebuilt/react_agent_executor.js"].map(
  (filePath) => ({
    channelName: "createReactAgent",
    module: module$1(filePath),
    functionQuery: { functionName: "createReactAgent", kind: "Sync" }
  })
);
const langgraphConfig = [...compileConfig, ...createReactAgentConfig];
const langgraphChannels = {
  LANGGRAPH_STATE_GRAPH_COMPILE: "orchestrion:@langchain/langgraph:stateGraphCompile",
  LANGGRAPH_CREATE_REACT_AGENT: "orchestrion:@langchain/langgraph:createReactAgent"
};
const langgraphSubscribeInjection = toSubscribeInjections(langgraphConfig);

export { langgraphChannels, langgraphConfig, langgraphSubscribeInjection };
//# sourceMappingURL=langgraph.js.map
