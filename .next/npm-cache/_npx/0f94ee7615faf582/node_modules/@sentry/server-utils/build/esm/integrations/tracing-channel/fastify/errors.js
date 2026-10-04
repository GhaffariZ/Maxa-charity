import * as diagnosticsChannel from 'node:diagnostics_channel';
import { DEBUG_BUILD } from '../../../debug-build.js';
import { debug, captureException, getClient } from '@sentry/core';
import { defaultShouldHandleError, INTEGRATION_NAME } from './utils.js';

function getFastifyIntegration() {
  const client = getClient();
  return client?.getIntegrationByName(INTEGRATION_NAME);
}
function subscribeToFastifyErrorChannel() {
  diagnosticsChannel.subscribe("tracing:fastify.request.handler:error", (message) => {
    const { error, request, reply } = message;
    handleFastifyError.call(handleFastifyError, error, request, reply, "diagnostics-channel");
  });
}
function handleFastifyError(error, request, reply, handlerOrigin) {
  const shouldHandleError = getFastifyIntegration()?.getShouldHandleError() || defaultShouldHandleError;
  if (handlerOrigin === "diagnostics-channel") {
    this.diagnosticsChannelExists = true;
  }
  if (this.diagnosticsChannelExists && handlerOrigin === "onError-hook") {
    DEBUG_BUILD && debug.warn(
      "Fastify error handler was already registered via diagnostics channel.",
      "You can safely remove `setupFastifyErrorHandler` call and set `shouldHandleError` on the integration options."
    );
    return;
  }
  if (shouldHandleError(error, request, reply)) {
    captureException(error, {
      mechanism: {
        handled: false,
        type: "auto.function.fastify"
      }
    });
  }
}

export { handleFastifyError, subscribeToFastifyErrorChannel };
//# sourceMappingURL=errors.js.map
