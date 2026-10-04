import { parse } from '../../vendored/meriyah/dist/meriyah.js';
import { SUBSCRIBE_INJECTIONS } from '../config/index.js';
import { subscriberExportForModule } from '../config/channel-integration-definitions.js';
import { SUBSCRIBE_TRANSFORM_NAME } from '../config/subscribe-injection.js';

const injectedPrograms = /* @__PURE__ */ new WeakSet();
const SUBSCRIBE_INJECTION_SINK = "globalThis.__SENTRY_ORCHESTRION_INJECT__";
function subscribeSnippet(exportName, esm) {
  const importStmt = esm ? `import { ${exportName}, registerOrchestrionChannelIntegration } from '@sentry/server-utils/orchestrion';` : `const { ${exportName}, registerOrchestrionChannelIntegration } = require('@sentry/server-utils/orchestrion');`;
  return `${importStmt}
${SUBSCRIBE_INJECTION_SINK} = registerOrchestrionChannelIntegration(${JSON.stringify(exportName)}, ${exportName});`;
}
const injectSubscribe = (state, program) => {
  const node = program;
  if (injectedPrograms.has(node)) {
    return;
  }
  const { moduleType, channelName } = state;
  const exportName = channelName ? subscriberExportForModule(channelName) : void 0;
  if (!exportName) {
    return;
  }
  injectedPrograms.add(node);
  const statements = parse(subscribeSnippet(exportName, moduleType === "esm"), {
    module: moduleType === "esm",
    next: true
  }).body;
  const directiveIndex = node.body.findIndex((n) => n.type === "ExpressionStatement" && n.directive === "use strict");
  node.body.splice(directiveIndex + 1, 0, ...statements);
};
function subscribeInjectionOptions() {
  return {
    instrumentations: SUBSCRIBE_INJECTIONS,
    customTransforms: { [SUBSCRIBE_TRANSFORM_NAME]: injectSubscribe }
  };
}

export { subscribeInjectionOptions };
//# sourceMappingURL=subscribeInjection.js.map
