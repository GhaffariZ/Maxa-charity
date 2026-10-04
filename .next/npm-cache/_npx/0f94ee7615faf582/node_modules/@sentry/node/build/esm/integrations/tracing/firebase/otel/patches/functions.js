import { InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { InstrumentationNodeModuleFile } from '../../../InstrumentationNodeModuleFile.js';
import { startSpanManual, SPAN_KIND, SPAN_STATUS_ERROR, captureException, flush, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';

function patchFunctions(functionsSupportedVersions, wrap, unwrap) {
  const moduleFunctionsCJS = new InstrumentationNodeModuleDefinition("firebase-functions", functionsSupportedVersions);
  const modulesToInstrument = [
    { name: "firebase-functions/lib/v2/providers/https.js", triggerType: "function" },
    { name: "firebase-functions/lib/v2/providers/firestore.js", triggerType: "firestore" },
    { name: "firebase-functions/lib/v2/providers/scheduler.js", triggerType: "scheduler" },
    { name: "firebase-functions/lib/v2/storage.js", triggerType: "storage" }
  ];
  modulesToInstrument.forEach(({ name, triggerType }) => {
    moduleFunctionsCJS.files.push(
      new InstrumentationNodeModuleFile(
        name,
        functionsSupportedVersions,
        (moduleExports) => wrapCommonFunctions(moduleExports, wrap, unwrap, triggerType),
        (moduleExports) => unwrapCommonFunctions(moduleExports, unwrap)
      )
    );
  });
  return moduleFunctionsCJS;
}
function patchV2Functions(triggerType) {
  return function v2FunctionsWrapper(original) {
    return function(...args) {
      const handler = typeof args[0] === "function" ? args[0] : args[1];
      const documentOrOptions = typeof args[0] === "function" ? void 0 : args[0];
      if (!handler) {
        return original.call(this, ...args);
      }
      const wrappedHandler = async function(...handlerArgs) {
        const functionName = process.env.FUNCTION_TARGET || process.env.K_SERVICE || "unknown";
        const attributes = {
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.firebase.otel.functions",
          "faas.name": functionName,
          "faas.trigger": triggerType,
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
            op: "http.request",
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
                  type: "auto.firebase.otel.functions",
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
      if (documentOrOptions) {
        return original.call(this, documentOrOptions, wrappedHandler);
      } else {
        return original.call(this, wrappedHandler);
      }
    };
  };
}
function wrapCommonFunctions(moduleExports, wrap, unwrap, triggerType) {
  unwrapCommonFunctions(moduleExports, unwrap);
  switch (triggerType) {
    case "function":
      wrap(moduleExports, "onRequest", patchV2Functions("http.request"));
      wrap(moduleExports, "onCall", patchV2Functions("http.call"));
      break;
    case "firestore":
      wrap(moduleExports, "onDocumentCreated", patchV2Functions("firestore.document.created"));
      wrap(moduleExports, "onDocumentUpdated", patchV2Functions("firestore.document.updated"));
      wrap(moduleExports, "onDocumentDeleted", patchV2Functions("firestore.document.deleted"));
      wrap(moduleExports, "onDocumentWritten", patchV2Functions("firestore.document.written"));
      wrap(moduleExports, "onDocumentCreatedWithAuthContext", patchV2Functions("firestore.document.created"));
      wrap(moduleExports, "onDocumentUpdatedWithAuthContext", patchV2Functions("firestore.document.updated"));
      wrap(moduleExports, "onDocumentDeletedWithAuthContext", patchV2Functions("firestore.document.deleted"));
      wrap(moduleExports, "onDocumentWrittenWithAuthContext", patchV2Functions("firestore.document.written"));
      break;
    case "scheduler":
      wrap(moduleExports, "onSchedule", patchV2Functions("scheduler.scheduled"));
      break;
    case "storage":
      wrap(moduleExports, "onObjectFinalized", patchV2Functions("storage.object.finalized"));
      wrap(moduleExports, "onObjectArchived", patchV2Functions("storage.object.archived"));
      wrap(moduleExports, "onObjectDeleted", patchV2Functions("storage.object.deleted"));
      wrap(moduleExports, "onObjectMetadataUpdated", patchV2Functions("storage.object.metadataUpdated"));
      break;
  }
  return moduleExports;
}
function unwrapCommonFunctions(moduleExports, unwrap) {
  const methods = [
    "onSchedule",
    "onRequest",
    "onCall",
    "onObjectFinalized",
    "onObjectArchived",
    "onObjectDeleted",
    "onObjectMetadataUpdated",
    "onDocumentCreated",
    "onDocumentUpdated",
    "onDocumentDeleted",
    "onDocumentWritten",
    "onDocumentCreatedWithAuthContext",
    "onDocumentUpdatedWithAuthContext",
    "onDocumentDeletedWithAuthContext",
    "onDocumentWrittenWithAuthContext"
  ];
  for (const method of methods) {
    if (isWrapped(moduleExports[method])) {
      unwrap(moduleExports, method);
    }
  }
  return moduleExports;
}

export { patchFunctions, patchV2Functions };
//# sourceMappingURL=functions.js.map
