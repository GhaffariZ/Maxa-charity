import { FAAS_TRIGGER, FAAS_NAME } from '@sentry/conventions/attributes';
import { startSpanManual, SPAN_KIND, SPAN_STATUS_ERROR, captureException, flush, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';

const FUNCTIONS_ORIGIN = "auto.firebase.orchestrion.functions";
const WRAPPED = "__sentryFirebaseWrapped";
function wrapFunctionsRegistration(data, triggerType) {
  const args = data.arguments;
  if (!Array.isArray(args) || args.length === 0) {
    return;
  }
  const handlerIndex = typeof args[0] === "function" ? 0 : 1;
  const handler = args[handlerIndex];
  if (typeof handler !== "function" || handler[WRAPPED]) {
    return;
  }
  args[handlerIndex] = wrapHandler(handler, triggerType);
}
function wrapHandler(handler, triggerType) {
  const wrapped = async function(...handlerArgs) {
    const functionName = process.env.FUNCTION_TARGET || process.env.K_SERVICE || "unknown";
    const attributes = {
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: FUNCTIONS_ORIGIN,
      [FAAS_NAME]: functionName,
      [FAAS_TRIGGER]: triggerType,
      "faas.provider": "firebase"
    };
    if (process.env.GCLOUD_PROJECT) {
      attributes["cloud.project_id"] = process.env.GCLOUD_PROJECT;
    }
    if (process.env.EVENTARC_CLOUD_EVENT_SOURCE) {
      attributes["cloud.event_source"] = process.env.EVENTARC_CLOUD_EVENT_SOURCE;
    }
    return startSpanManual(
      {
        name: `firebase.function.${triggerType}`,
        op: "function.firebase",
        kind: SPAN_KIND.SERVER,
        attributes
      },
      async (span) => {
        try {
          const result = await handler.apply(this, handlerArgs);
          span.end();
          return result;
        } catch (error) {
          span.setStatus({ code: SPAN_STATUS_ERROR });
          captureException(error, {
            mechanism: {
              type: FUNCTIONS_ORIGIN,
              handled: false
            }
          });
          span.end();
          await flush(2e3);
          throw error;
        }
      }
    );
  };
  wrapped[WRAPPED] = true;
  return wrapped;
}

export { wrapFunctionsRegistration };
//# sourceMappingURL=functions.js.map
