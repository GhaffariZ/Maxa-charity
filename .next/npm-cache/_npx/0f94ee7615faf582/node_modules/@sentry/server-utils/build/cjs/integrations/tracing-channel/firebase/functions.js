Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');

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
    const attributes$1 = {
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: FUNCTIONS_ORIGIN,
      [attributes.FAAS_NAME]: functionName,
      [attributes.FAAS_TRIGGER]: triggerType,
      "faas.provider": "firebase"
    };
    if (process.env.GCLOUD_PROJECT) {
      attributes$1["cloud.project_id"] = process.env.GCLOUD_PROJECT;
    }
    if (process.env.EVENTARC_CLOUD_EVENT_SOURCE) {
      attributes$1["cloud.event_source"] = process.env.EVENTARC_CLOUD_EVENT_SOURCE;
    }
    return core.startSpanManual(
      {
        name: `firebase.function.${triggerType}`,
        op: "function.firebase",
        kind: core.SPAN_KIND.SERVER,
        attributes: attributes$1
      },
      async (span) => {
        try {
          const result = await handler.apply(this, handlerArgs);
          span.end();
          return result;
        } catch (error) {
          span.setStatus({ code: core.SPAN_STATUS_ERROR });
          core.captureException(error, {
            mechanism: {
              type: FUNCTIONS_ORIGIN,
              handled: false
            }
          });
          span.end();
          await core.flush(2e3);
          throw error;
        }
      }
    );
  };
  wrapped[WRAPPED] = true;
  return wrapped;
}

exports.wrapFunctionsRegistration = wrapFunctionsRegistration;
//# sourceMappingURL=functions.js.map
