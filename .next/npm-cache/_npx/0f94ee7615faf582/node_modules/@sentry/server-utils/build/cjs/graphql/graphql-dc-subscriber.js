Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const attributes = require('@sentry/conventions/attributes');
const op = require('@sentry/conventions/op');
const core = require('@sentry/core');
const tracingChannel = require('../tracing-channel.js');
const utils = require('./utils.js');

const GRAPHQL_DC_CHANNEL_PARSE = "graphql:parse";
const GRAPHQL_DC_CHANNEL_VALIDATE = "graphql:validate";
const GRAPHQL_DC_CHANNEL_EXECUTE = "graphql:execute";
const GRAPHQL_DC_CHANNEL_SUBSCRIBE = "graphql:subscribe";
const GRAPHQL_DC_CHANNEL_RESOLVE = "graphql:resolve";
const ORIGIN = "auto.graphql.diagnostic_channel";
const SPAN_NAME_PARSE = "graphql.parse";
const SPAN_NAME_VALIDATE = "graphql.validate";
const SPAN_NAME_EXECUTE = "graphql.execute";
const SPAN_NAME_SUBSCRIBE = "graphql.subscribe";
const SPAN_NAME_RESOLVE = "graphql.resolve";
const GRAPHQL_FIELD_NAME = "graphql.field.name";
const GRAPHQL_FIELD_PATH = "graphql.field.path";
const GRAPHQL_FIELD_TYPE = "graphql.field.type";
const GRAPHQL_PARENT_NAME = "graphql.parent.name";
function subscribeGraphqlDiagnosticChannels(tracingChannel, options = {}) {
  const ignoreResolveSpans = options.ignoreResolveSpans !== false;
  const ignoreTrivialResolveSpans = options.ignoreTrivialResolveSpans !== false;
  const useOperationNameForRootSpan = options.useOperationNameForRootSpan !== false;
  setupParseChannel(tracingChannel);
  setupValidateChannel(tracingChannel);
  setupOperationChannel(tracingChannel, GRAPHQL_DC_CHANNEL_EXECUTE, SPAN_NAME_EXECUTE, useOperationNameForRootSpan);
  setupOperationChannel(tracingChannel, GRAPHQL_DC_CHANNEL_SUBSCRIBE, SPAN_NAME_SUBSCRIBE, useOperationNameForRootSpan);
  if (!ignoreResolveSpans) {
    setupResolveChannel(tracingChannel, ignoreTrivialResolveSpans);
  }
}
function setupParseChannel(tracingChannel$1) {
  tracingChannel.bindTracingChannelToSpan(
    tracingChannel$1(GRAPHQL_DC_CHANNEL_PARSE),
    () => core.startInactiveSpan({
      name: SPAN_NAME_PARSE,
      attributes: {
        [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: op.WEB_SERVER_GRAPHQL_SPAN_OP
      }
    })
  );
}
function setupValidateChannel(tracingChannel$1) {
  tracingChannel.bindTracingChannelToSpan(
    tracingChannel$1(GRAPHQL_DC_CHANNEL_VALIDATE),
    (data) => {
      return core.startInactiveSpan({
        name: SPAN_NAME_VALIDATE,
        attributes: {
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: op.WEB_SERVER_GRAPHQL_SPAN_OP,
          [attributes.GRAPHQL_DOCUMENT]: utils.collectGraphqlDocument(data.document)
        }
      });
    },
    {
      beforeSpanEnd: (span, data) => {
        if (Array.isArray(data.result) && data.result.length > 0) {
          span.setStatus({ code: core.SPAN_STATUS_ERROR, message: "invalid_argument" });
        }
      }
    }
  );
}
function setupOperationChannel(tracingChannel$1, channelName, fallbackName, useOperationNameForRootSpan) {
  tracingChannel.bindTracingChannelToSpan(
    tracingChannel$1(channelName),
    (data) => {
      const span = core.startInactiveSpan({
        name: utils.getOperationSpanName(data.operationType, data.operationName, fallbackName),
        attributes: {
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
          [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: op.WEB_SERVER_GRAPHQL_SPAN_OP,
          [attributes.GRAPHQL_OPERATION_TYPE]: data.operationType,
          [attributes.GRAPHQL_OPERATION_NAME]: data.operationName || void 0,
          [attributes.GRAPHQL_DOCUMENT]: utils.collectGraphqlDocument(data.document)
        }
      });
      if (useOperationNameForRootSpan && data.operationType) {
        utils.renameRootSpanWithOperation(span, data.operationType, data.operationName);
      }
      return span;
    },
    {
      beforeSpanEnd: (span, data) => {
        if (utils.hasResultErrors(data.result)) {
          span.setStatus({ code: core.SPAN_STATUS_ERROR, message: "internal_error" });
        }
      }
    }
  );
}
function setupResolveChannel(tracingChannel$1, ignoreTrivialResolveSpans) {
  tracingChannel.bindTracingChannelToSpan(tracingChannel$1(GRAPHQL_DC_CHANNEL_RESOLVE), (data) => {
    if (ignoreTrivialResolveSpans && data.isDefaultResolver) {
      return void 0;
    }
    return core.startInactiveSpan({
      name: `${SPAN_NAME_RESOLVE} ${data.fieldPath}`,
      attributes: {
        [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
        [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: op.WEB_SERVER_GRAPHQL_SPAN_OP,
        [GRAPHQL_FIELD_NAME]: data.fieldName,
        [GRAPHQL_FIELD_PATH]: data.fieldPath,
        [GRAPHQL_FIELD_TYPE]: data.fieldType,
        [GRAPHQL_PARENT_NAME]: data.parentType
      }
    });
  });
}

exports.GRAPHQL_DC_CHANNEL_EXECUTE = GRAPHQL_DC_CHANNEL_EXECUTE;
exports.GRAPHQL_DC_CHANNEL_PARSE = GRAPHQL_DC_CHANNEL_PARSE;
exports.GRAPHQL_DC_CHANNEL_RESOLVE = GRAPHQL_DC_CHANNEL_RESOLVE;
exports.GRAPHQL_DC_CHANNEL_SUBSCRIBE = GRAPHQL_DC_CHANNEL_SUBSCRIBE;
exports.GRAPHQL_DC_CHANNEL_VALIDATE = GRAPHQL_DC_CHANNEL_VALIDATE;
exports.subscribeGraphqlDiagnosticChannels = subscribeGraphqlDiagnosticChannels;
//# sourceMappingURL=graphql-dc-subscriber.js.map
