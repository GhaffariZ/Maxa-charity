import { WEB_SERVER_GRAPHQL_SPAN_OP } from '@sentry/conventions/op';
import { isObjectLike, withActiveSpan, SPAN_STATUS_ERROR, startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_OP, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { GRAPHQL_PATCHED_SYMBOL, GRAPHQL_DATA_SYMBOL, GRAPHQL_PARENT_NAME, GRAPHQL_FIELD_TYPE, GRAPHQL_FIELD_PATH, GRAPHQL_FIELD_NAME, ORIGIN, SPAN_NAME_RESOLVE } from './constants.js';

function isPromise(value) {
  return typeof value?.then === "function";
}
function wrapFields(type, getConfig) {
  if (!type || type[GRAPHQL_PATCHED_SYMBOL]) {
    return;
  }
  type[GRAPHQL_PATCHED_SYMBOL] = true;
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
  if (typeof fieldResolver !== "function" || fieldResolver[GRAPHQL_PATCHED_SYMBOL]) {
    return fieldResolver;
  }
  function wrappedFieldResolver(source, args, rawContextValue, info) {
    if (!fieldResolver) {
      return void 0;
    }
    const contextValue = rawContextValue ?? {};
    const config = getConfig();
    if (config.ignoreTrivialResolveSpans && isDefaultResolver && (isObjectLike(source) || typeof source === "function")) {
      const property = source[info.fieldName];
      if (typeof property !== "function") {
        return fieldResolver.call(this, source, args, contextValue, info);
      }
    }
    if (!contextValue[GRAPHQL_DATA_SYMBOL]) {
      return fieldResolver.call(this, source, args, contextValue, info);
    }
    const path = pathToArray(info.path);
    const { field, spanAdded } = createFieldIfNotExists(contextValue, info, path);
    const span = field.span;
    return withActiveSpan(span, () => {
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
  wrappedFieldResolver[GRAPHQL_PATCHED_SYMBOL] = true;
  return wrappedFieldResolver;
}
function endResolveSpan(span, shouldEndSpan, error) {
  if (!shouldEndSpan) {
    return;
  }
  if (error) {
    span.setStatus({ code: SPAN_STATUS_ERROR, message: error.message });
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
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN,
    [SEMANTIC_ATTRIBUTE_SENTRY_OP]: WEB_SERVER_GRAPHQL_SPAN_OP,
    [GRAPHQL_FIELD_NAME]: info.fieldName,
    [GRAPHQL_FIELD_PATH]: path.join("."),
    [GRAPHQL_FIELD_TYPE]: info.returnType.toString(),
    [GRAPHQL_PARENT_NAME]: info.parentType.name
  };
  return startInactiveSpan({ name: `${SPAN_NAME_RESOLVE} ${path.join(".")}`, attributes, parentSpan });
}
function addField(contextValue, path, field) {
  const data = contextValue[GRAPHQL_DATA_SYMBOL];
  if (data) {
    data.fields[path.join(".")] = field;
  }
}
function getField(contextValue, path) {
  return contextValue[GRAPHQL_DATA_SYMBOL]?.fields[path.join(".")];
}
function getParentFieldSpan(contextValue, path) {
  for (let i = path.length - 1; i > 0; i--) {
    const field = getField(contextValue, path.slice(0, i));
    if (field) {
      return field.span;
    }
  }
  return contextValue[GRAPHQL_DATA_SYMBOL]?.span;
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

export { getOperation, wrapFieldResolver, wrapFields };
//# sourceMappingURL=resolvers.js.map
