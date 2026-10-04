Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const debugBuild = require('../../../../debug-build.js');

const INVOKE_COMMAND = "Invoke";
class LambdaServiceExtension {
  requestPreSpanHook(request) {
    const functionName = request.commandInput?.FunctionName;
    const spanAttributes = {};
    let spanName;
    if (request.commandName === INVOKE_COMMAND) {
      spanAttributes[attributes.FAAS_INVOKED_NAME] = functionName;
      spanAttributes[attributes.FAAS_INVOKED_PROVIDER] = "aws";
      spanName = `${functionName} ${INVOKE_COMMAND}`;
    }
    return {
      spanAttributes,
      spanKind: core.SPAN_KIND.CLIENT,
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
      span.setAttribute(attributes.FAAS_EXECUTION, response.requestId);
      if (response.request.region) {
        span.setAttribute(attributes.FAAS_INVOKED_REGION, response.request.region);
      }
    }
  }
}
function injectLambdaPropagationContext(clientContext, span) {
  try {
    const propagatedContext = core.getTraceData({ span });
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
      debugBuild.DEBUG_BUILD && core.debug.warn(
        "[orchestrion:aws-sdk] cannot set trace propagation on lambda invoke parameters due to ClientContext length limitations."
      );
      return clientContext;
    }
    return encodedClientContext;
  } catch (e) {
    debugBuild.DEBUG_BUILD && core.debug.log("[orchestrion:aws-sdk] failed to set trace propagation on lambda ClientContext", e);
    return clientContext;
  }
}

exports.LambdaServiceExtension = LambdaServiceExtension;
//# sourceMappingURL=lambda.js.map
