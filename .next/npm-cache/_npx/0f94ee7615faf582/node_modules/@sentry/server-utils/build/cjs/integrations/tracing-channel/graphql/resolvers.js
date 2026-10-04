Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const op = require('@sentry/conventions/op');
const core = require('@sentry/core');
const constants = require('./constants.js');

function isPromise(value) {
  return typeof value?.then === "function";
}
function wrapFields(type, getConfig) {
  if (!type || type[constants.GRAPHQL_PATCHED_SYMBOL]) {
    return;
  }
  type[constants.GRAPHQL_PATCHED_SYMBOL] = true;
  const fields = type.getFields();
  Object.keys(fields).forEach((key) => {
    const field = fields[key];
    if (!field) {
      return;
    }
    if (field.resolve) {
      field.resolve = wrapFieldResolver(getConfig, field.resolve);
    }
    if (field.type) {
      for (const unwrappedType of unwrapType(field.type)) {
        wrapFields(unwrappedType, getConfig);
      }
    }
  });
}
function wrapFieldResolver(getConfig, fieldResolver, isDefaultResolver = false) {
  if (typeof fieldResolver !== "function" || fieldResolver[constants.GRAPHQL_PATCHED_SYMBOL]) {
    return fieldResolver;
  }
  function wrappedFieldResolver(source, args, rawContextValue, info) {
    if (!fieldResolver) {
      return void 0;
    }
    const contextValue = rawContextValue ?? {};
    const config = getConfig();
    if (config.ignoreTrivialResolveSpans && isDefaultResolver && (core.isObjectLike(source) || typeof source === "function")) {
      const property = source[info.fieldName];
      if (typeof property !== "function") {
        return fieldResolver.call(this, source, args, contextValue, info);
      }
    }
    if (!contextValue[constants.GRAPHQL_DATA_SYMBOL]) {
      return fieldResolver.call(this, source, args, contextValue, info);
    }
    const path = pathToArray(info.path);
    const { field, spanAdded } = createFieldIfNotExists(contextValue, info, path);
    const span = field.span;
    return core.withActiveSpan(span, () => {
      try {
        const res = fieldResolver.call(this, source, args, contextValue, info);
        if (isPromise(res)) {
          return res.then(
            (r) => {
              endResolveSpan(span, spanAdded);
              return r;
            },
            (err) => {
              endResolveSpan(span, spanAdded, err);
              throw err;
            }
          );
        }
        endResolveSpan(span, spanAdded);
        return res;
      } catch (err) {
        endResolveSpan(span, spanAdded, err);
        throw err;
      }
    });
  }
  wrappedFieldResolver[constants.GRAPHQL_PATCHED_SYMBOL] = true;
  return wrappedFieldResolver;
}
function endResolveSpan(span, shouldEndSpan, error) {
  if (!shouldEndSpan) {
    return;
  }
  if (error) {
    span.setStatus({ code: core.SPAN_STATUS_ERROR, message: error.message });
  }
  span.end();
}
function createFieldIfNotExists(contextValue, info, path) {
  const existing = getField(contextValue, path);
  if (existing) {
    return { field: existing, spanAdded: false };
  }
  const field = { span: createResolverSpan(info, path, getParentFieldSpan(contextValue, path)) };
  addField(contextValue, path, field);
  return { field, spanAdded: true };
}
function createResolverSpan(info, path, parentSpan) {
  const attributes = {
    [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: constants.ORIGIN,
    [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: op.WEB_SERVER_GRAPHQL_SPAN_OP,
    [constants.GRAPHQL_FIELD_NAME]: info.fieldName,
    [constants.GRAPHQL_FIELD_PATH]: path.join("."),
    [constants.GRAPHQL_FIELD_TYPE]: info.returnType.toString(),
    [constants.GRAPHQL_PARENT_NAME]: info.parentType.name
  };
  return core.startInactiveSpan({ name: `${constants.SPAN_NAME_RESOLVE} ${path.join(".")}`, attributes, parentSpan });
}
function addField(contextValue, path, field) {
  const data = contextValue[constants.GRAPHQL_DATA_SYMBOL];
  if (data) {
    data.fields[path.join(".")] = field;
  }
}
function getField(contextValue, path) {
  return contextValue[constants.GRAPHQL_DATA_SYMBOL]?.fields[path.join(".")];
}
function getParentFieldSpan(contextValue, path) {
  for (let i = path.length - 1; i > 0; i--) {
    const field = getField(contextValue, path.slice(0, i));
    if (field) {
      return field.span;
    }
  }
  return contextValue[constants.GRAPHQL_DATA_SYMBOL]?.span;
}
function pathToArray(path) {
  const flattened = [];
  let curr = path;
  while (curr) {
    flattened.push(String(curr.key));
    curr = curr.prev;
  }
  return flattened.reverse();
}
function unwrapType(type) {
  if ("ofType" in type && type.ofType) {
    return unwrapType(type.ofType);
  }
  if (isGraphQLUnionType(type)) {
    return type.getTypes();
  }
  if (isGraphQLObjectType(type)) {
    return [type];
  }
  return [];
}
function isGraphQLUnionType(type) {
  return "getTypes" in type && typeof type.getTypes === "function";
}
function isGraphQLObjectType(type) {
  return "getFields" in type && typeof type.getFields === "function";
}
function getOperation(document, operationName) {
  const definitions = document?.definitions;
  if (!definitions || !Array.isArray(definitions)) {
    return void 0;
  }
  const isOperation = (def) => !!def?.operation && ["query", "mutation", "subscription"].indexOf(def.operation) !== -1;
  if (operationName) {
    return definitions.filter(isOperation).find((def) => operationName === def?.name?.value);
  }
  return definitions.find(isOperation);
}

exports.getOperation = getOperation;
exports.wrapFieldResolver = wrapFieldResolver;
exports.wrapFields = wrapFields;
//# sourceMappingURL=resolvers.js.map
