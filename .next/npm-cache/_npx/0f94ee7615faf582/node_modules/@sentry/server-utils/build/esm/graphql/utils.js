import { SENTRY_GRAPHQL_OPERATION } from '@sentry/conventions/attributes';
import { getClient, isObjectLike, getRootSpan, spanToJSON } from '@sentry/core';

const ORIGINAL_DESCRIPTION_ATTRIBUTE = "original-description";
const REDACTED_LITERAL_KINDS = /* @__PURE__ */ new Set(["Int", "Float", "String", "BlockString"]);
function renameRootSpanWithOperation(span, operationType, operationName) {
  const rootSpan = getRootSpan(span);
  if (rootSpan === span) {
    return;
  }
  const rootSpanJson = spanToJSON(rootSpan);
  const newOperation = operationName ? `${operationType} ${operationName}` : operationType;
  const existingOperations = rootSpanJson.data[SENTRY_GRAPHQL_OPERATION];
  let operations;
  if (Array.isArray(existingOperations)) {
    operations = [...existingOperations, newOperation];
  } else if (typeof existingOperations === "string") {
    operations = [existingOperations, newOperation];
  } else {
    operations = newOperation;
  }
  rootSpan.setAttribute(SENTRY_GRAPHQL_OPERATION, operations);
  const originalDescription = rootSpanJson.data[ORIGINAL_DESCRIPTION_ATTRIBUTE] ?? rootSpanJson.description;
  if (!rootSpanJson.data[ORIGINAL_DESCRIPTION_ATTRIBUTE]) {
    rootSpan.setAttribute(ORIGINAL_DESCRIPTION_ATTRIBUTE, originalDescription);
  }
  rootSpan.updateName(`${originalDescription} (${getGraphqlOperationNamesFromAttribute(operations)})`);
}
function getGraphqlOperationNamesFromAttribute(attr) {
  if (Array.isArray(attr)) {
    const sorted = attr.slice().sort();
    if (sorted.length <= 5) {
      return sorted.join(", ");
    }
    return `${sorted.slice(0, 5).join(", ")}, +${sorted.length - 5}`;
  }
  return attr;
}
function getOperationSpanName(operationType, operationName, fallbackName) {
  if (operationType && operationName) {
    return `${operationType} ${operationName}`;
  }
  if (operationType) {
    return operationType;
  }
  return fallbackName;
}
function hasResultErrors(result) {
  if (isObjectLike(result) && "errors" in result) {
    const errors = result.errors;
    return Array.isArray(errors) && errors.length > 0;
  }
  return false;
}
function redactGraphqlDocument(document) {
  const loc = document?.loc;
  const body = loc?.source?.body;
  if (typeof body !== "string" || !loc?.startToken) {
    return void 0;
  }
  try {
    const ranges = [];
    for (let token = loc.startToken; token; token = token.next) {
      if (REDACTED_LITERAL_KINDS.has(token.kind)) {
        ranges.push({ start: token.start, end: token.end, kind: token.kind });
      }
    }
    let out = body;
    for (let i = ranges.length - 1; i >= 0; i--) {
      const { start, end, kind } = ranges[i];
      const replacement = kind === "String" || kind === "BlockString" ? '"*"' : "*";
      out = out.slice(0, start) + replacement + out.slice(end);
    }
    return out;
  } catch {
    return void 0;
  }
}
function collectGraphqlDocument(document) {
  if (getClient()?.getDataCollectionOptions().graphQL.document !== true) {
    return void 0;
  }
  return redactGraphqlDocument(document);
}

export { collectGraphqlDocument, getOperationSpanName, hasResultErrors, renameRootSpanWithOperation };
//# sourceMappingURL=utils.js.map
