Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const op = require('@sentry/conventions/op');
const core = require('@sentry/core');
const utils = require('../../../graphql/utils.js');
const constants = require('./constants.js');
const resolvers = require('./resolvers.js');

const BASE_ATTRIBUTES = {
  [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: constants.ORIGIN,
  [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: op.WEB_SERVER_GRAPHQL_SPAN_OP
};
function startParseSpan() {
  return core.startInactiveSpan({ name: constants.SPAN_NAME_PARSE, attributes: { ...BASE_ATTRIBUTES } });
}
function startValidateSpan(documentAST) {
  return core.startInactiveSpan({
    name: constants.SPAN_NAME_VALIDATE,
    attributes: { ...BASE_ATTRIBUTES, [attributes.GRAPHQL_DOCUMENT]: utils.collectGraphqlDocument(documentAST) }
  });
}
function finalizeValidateSpan(span, result) {
  if (Array.isArray(result) && result.length > 0) {
    span.setStatus({ code: core.SPAN_STATUS_ERROR, message: "invalid_argument" });
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
  const alreadyInstrumented = !!contextValue[constants.GRAPHQL_DATA_SYMBOL];
  if (!config.ignoreResolveSpans && !alreadyInstrumented) {
    const isUsingDefaultResolver = fieldResolver == null;
    const defaultFieldResolver = self?.defaultFieldResolver;
    const fieldResolverForExecute = fieldResolver ?? defaultFieldResolver;
    if (fieldResolverForExecute) {
      fieldResolver = resolvers.wrapFieldResolver(getConfig, fieldResolverForExecute, isUsingDefaultResolver);
    }
    if (schema) {
      resolvers.wrapFields(schema.getQueryType(), getConfig);
      resolvers.wrapFields(schema.getMutationType(), getConfig);
    }
  }
  const operation = resolvers.getOperation(document, args.operationName);
  const operationType = operation?.operation;
  const operationName = operation?.name?.value ?? args.operationName ?? void 0;
  const span = core.startInactiveSpan({
    name: utils.getOperationSpanName(operationType, operationName || void 0, constants.SPAN_NAME_EXECUTE),
    attributes: {
      ...BASE_ATTRIBUTES,
      [attributes.GRAPHQL_OPERATION_TYPE]: operationType,
      [attributes.GRAPHQL_OPERATION_NAME]: operationName || void 0,
      [attributes.GRAPHQL_DOCUMENT]: utils.collectGraphqlDocument(document)
    }
  });
  if (config.useOperationNameForRootSpan && operationType) {
    utils.renameRootSpanWithOperation(span, operationType, operationName || void 0);
  }
  contextValue[constants.GRAPHQL_DATA_SYMBOL] = { source: document, span, fields: {} };
  args.writeBack(contextValue, fieldResolver);
  return span;
}
function finalizeExecuteSpan(span, result) {
  if (utils.hasResultErrors(result)) {
    span.setStatus({ code: core.SPAN_STATUS_ERROR, message: "internal_error" });
  }
}

exports.finalizeExecuteSpan = finalizeExecuteSpan;
exports.finalizeValidateSpan = finalizeValidateSpan;
exports.startExecuteSpan = startExecuteSpan;
exports.startParseSpan = startParseSpan;
exports.startValidateSpan = startValidateSpan;
//# sourceMappingURL=spans.js.map
