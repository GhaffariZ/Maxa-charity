import { defineIntegration, extendIntegration, addVercelAiProcessors } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { vercelAiIntegration } from '@sentry/server-utils';
import { INTEGRATION_NAME } from './constants.js';
import { SentryVercelAiInstrumentation } from './instrumentation.js';

const instrumentVercelAi = generateInstrumentOnce(INTEGRATION_NAME, () => new SentryVercelAiInstrumentation({}));
function shouldForceIntegration(client) {
  const modules = client.getIntegrationByName("Modules");
  return !!modules?.getModules?.()?.ai;
}
const _vercelAIIntegration = ((options = {}) => {
  let instrumentation;
  const parentIntegration = vercelAiIntegration(options);
  return extendIntegration(parentIntegration, {
    options,
    setupOnce() {
      instrumentation = instrumentVercelAi();
    },
    afterAllSetup(client) {
      const shouldForce = options.force ?? shouldForceIntegration(client);
      if (shouldForce) {
        addVercelAiProcessors(client);
      } else {
        instrumentation?.callWhenPatched(() => addVercelAiProcessors(client));
      }
    }
  });
});
const vercelAIIntegration = defineIntegration(_vercelAIIntegration);

export { instrumentVercelAi, vercelAIIntegration };
//# sourceMappingURL=index.js.map
