import { SPAN_KIND, getTraceData, debug } from '@sentry/core';
import { FAAS_INVOKED_NAME, FAAS_INVOKED_PROVIDER, FAAS_EXECUTION, FAAS_INVOKED_REGION } from '@sentry/conventions/attributes';
import { DEBUG_BUILD } from '../../../../debug-build.js';

const INVOKE_COMMAND = "Invoke";
class LambdaServiceExtension {
  requestPreSpanHook(request) {
    const functionName = request.commandInput?.FunctionName;
    const spanAttributes = {};
    let spanName;
    if (request.commandName === INVOKE_COMMAND) {
      spanAttributes[FAAS_INVOKED_NAME] = functionName;
      spanAttributes[FAAS_INVOKED_PROVIDER] = "aws";
      spanName = `${functionName} ${INVOKE_COMMAND}`;
    }
    return {
      spanAttributes,
      spanKind: SPAN_KIND.CLIENT,
      spanName
    };
  }
  requestPostSpanHook(request, span) {
    if (request.commandName === INVOKE_COMMAND && request.commandInput) {
      request.commandInput.ClientContext = injectLambdaPropagationContext(request.commandInput.ClientContext, span);
    }
  }
  responseHook(response, span) {
    if (response.request.commandName === INVOKE_COMMAND) {
      span.setAttribute(FAAS_EXECUTION, response.requestId);
      if (response.request.region) {
        span.setAttribute(FAAS_INVOKED_REGION, response.request.region);
      }
    }
  }
}
function injectLambdaPropagationContext(clientContext, span) {
  try {
    const propagatedContext = getTraceData({ span });
    const parsedClientContext = clientContext ? JSON.parse(Buffer.from(clientContext, "base64").toString("utf8")) : {};
    const updatedClientContext = {
      ...parsedClientContext,
      custom: {
        ...parsedClientContext.custom,
        ...propagatedContext
      }
    };
    const encodedClientContext = Buffer.from(JSON.stringify(updatedClientContext)).toString("base64");
    if (encodedClientContext.length > 3583) {
      DEBUG_BUILD && debug.warn(
        "[orchestrion:aws-sdk] cannot set trace propagation on lambda invoke parameters due to ClientContext length limitations."
      );
      return clientContext;
    }
    return encodedClientContext;
  } catch (e) {
    DEBUG_BUILD && debug.log("[orchestrion:aws-sdk] failed to set trace propagation on lambda ClientContext", e);
    return clientContext;
  }
}

export { LambdaServiceExtension };
//# sourceMappingURL=lambda.js.map
