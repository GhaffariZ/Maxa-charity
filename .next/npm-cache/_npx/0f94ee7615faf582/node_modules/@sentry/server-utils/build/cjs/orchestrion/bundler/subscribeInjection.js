Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const meriyah = require('../../vendored/meriyah/dist/meriyah.js');
const index = require('../config/index.js');
const channelIntegrationDefinitions = require('../config/channel-integration-definitions.js');
const subscribeInjection = require('../config/subscribe-injection.js');

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
  const exportName = channelName ? channelIntegrationDefinitions.subscriberExportForModule(channelName) : void 0;
  if (!exportName) {
    return;
  }
  injectedPrograms.add(node);
  const statements = meriyah.parse(subscribeSnippet(exportName, moduleType === "esm"), {
    module: moduleType === "esm",
    next: true
  }).body;
  const directiveIndex = node.body.findIndex((n) => n.type === "ExpressionStatement" && n.directive === "use strict");
  node.body.splice(directiveIndex + 1, 0, ...statements);
};
function subscribeInjectionOptions() {
  return {
    instrumentations: index.SUBSCRIBE_INJECTIONS,
    customTransforms: { [subscribeInjection.SUBSCRIBE_TRANSFORM_NAME]: injectSubscribe }
  };
}

exports.subscribeInjectionOptions = subscribeInjectionOptions;
//# sourceMappingURL=subscribeInjection.js.map
