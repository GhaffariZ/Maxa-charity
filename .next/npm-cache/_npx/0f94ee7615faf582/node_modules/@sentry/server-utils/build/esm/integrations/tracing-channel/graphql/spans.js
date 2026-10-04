import { GRAPHQL_DOCUMENT, GRAPHQL_OPERATION_NAME, GRAPHQL_OPERATION_TYPE } from '@sentry/conventions/attributes';
import { WEB_SERVER_GRAPHQL_SPAN_OP } from '@sentry/conventions/op';
import { startInactiveSpan, SPAN_STATUS_ERROR, SEMANTIC_ATTRIBUTE_SENTRY_OP, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { collectGraphqlDocument, hasResultErrors, getOperationSpanName, renameRootSpanWithOperation } from '../../../graphql/utils.js';
import { ORIGIN, SPAN_NAME_PARSE, SPAN_NAME_VALIDATE, GRAPHQL_DATA_SYMBOL, SPAN_NAME_EXECUTE } from './constants.js';
import { wrapFieldResolver, wrapFields, getOperation } from './resolvers.js';

const BASE_ATTRIBUTES = {
  [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
  [SEMANTIC_ATTRIBUTE_SENTRY_OP]: WEB_SERVER_GRAPHQL_SPAN_OP
};
function startParseSpan() {
  return startInactiveSpan({ name: SPAN_NAME_PARSE, attributes: { ...BASE_ATTRIBUTES } });
}
function startValidateSpan(documentAST) {
  return startInactiveSpan({
    name: SPAN_NAME_VALIDATE,
    attributes: { ...BASE_ATTRIBUTES, [GRAPHQL_DOCUMENT]: collectGraphqlDocument(documentAST) }
  });
}
function finalizeValidateSpan(span, result) {
  if (Array.isArray(result) && result.length > 0) {
    span.setStatus({ code: SPAN_STATUS_ERROR, message: "invalid_argument" });
  }
}
function normalizeExecuteArgs(argsArray) {
  if (argsArray.length >= 2) {
    return {
      schema: argsArray[0 /* SCHEMA */],
      document: argsArray[1 /* DOCUMENT */],
      contextValue: argsArray[3 /* CONTEXT_VALUE */] ?? {},
      operationName: argsArray[5 /* OPERATION_NAME */],
      fieldResolver: argsArray[6 /* FIELD_RESOLVER */],
      writeBack: (contextValue, fieldResolver) => {
        argsArray[3 /* CONTEXT_VALUE */] = contextValue;
        argsArray[6 /* FIELD_RESOLVER */] = fieldResolver;
      }
    };
  }
  const obj = argsArray[0] ?? {};
  return {
    schema: obj.schema,
    document: obj.document,
    contextValue: obj.contextValue ?? {},
    operationName: obj.operationName,
    fieldResolver: obj.fieldResolver,
    writeBack: (contextValue, fieldResolver) => {
      obj.contextValue = contextValue;
      obj.fieldResolver = fieldResolver;
    }
  };
}
function startExecuteSpan(argsArray, self, config, getConfig) {
  const args = normalizeExecuteArgs(argsArray);
  const { schema, document } = args;
  let { contextValue, fieldResolver } = args;
  const alreadyInstrumented = !!contextValue[GRAPHQL_DATA_SYMBOL];
  if (!config.ignoreResolveSpans && !alreadyInstrumented) {
    const isUsingDefaultResolver = fieldResolver == null;
    const defaultFieldResolver = self?.defaultFieldResolver;
    const fieldResolverForExecute = fieldResolver ?? defaultFieldResolver;
    if (fieldResolverForExecute) {
      fieldResolver = wrapFieldResolver(getConfig, fieldResolverForExecute, isUsingDefaultResolver);
    }
    if (schema) {
      wrapFields(schema.getQueryType(), getConfig);
      wrapFields(schema.getMutationType(), getConfig);
    }
  }
  const operation = getOperation(document, args.operationName);
  const operationType = operation?.operation;
  const operationName = operation?.name?.value ?? args.operationName ?? void 0;
  const span = startInactiveSpan({
    name: getOperationSpanName(operationType, operationName || void 0, SPAN_NAME_EXECUTE),
    attributes: {
      ...BASE_ATTRIBUTES,
      [GRAPHQL_OPERATION_TYPE]: operationType,
      [GRAPHQL_OPERATION_NAME]: operationName || void 0,
      [GRAPHQL_DOCUMENT]: collectGraphqlDocument(document)
    }
  });
  if (config.useOperationNameForRootSpan && operationType) {
    renameRootSpanWithOperation(span, operationType, operationName || void 0);
  }
  contextValue[GRAPHQL_DATA_SYMBOL] = { source: document, span, fields: {} };
  args.writeBack(contextValue, fieldResolver);
  return span;
}
function finalizeExecuteSpan(span, result) {
  if (hasResultErrors(result)) {
    span.setStatus({ code: SPAN_STATUS_ERROR, message: "internal_error" });
  }
}

export { finalizeExecuteSpan, finalizeValidateSpan, startExecuteSpan, startParseSpan, startValidateSpan };
//# sourceMappingURL=spans.js.map
