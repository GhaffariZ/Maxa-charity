Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const InstrumentationNodeModuleFile = require('../../InstrumentationNodeModuleFile.js');
const _enum = require('./enum.js');
const AttributeNames = require('./enums/AttributeNames.js');
const symbols = require('./symbols.js');
const internalTypes = require('./internal-types.js');
const utils = require('./utils.js');
const core = require('@sentry/core');
const opentelemetry = require('@sentry/opentelemetry');

const PACKAGE_NAME = "@sentry/instrumentation-graphql";
const ORIGIN = "auto.graphql.otel.graphql";
const DEFAULT_CONFIG = {
  ignoreResolveSpans: false
};
const supportedVersions = [">=14.0.0 <17"];
class GraphQLInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, { ...DEFAULT_CONFIG, ...config });
  }
  setConfig(config = {}) {
    super.setConfig({ ...DEFAULT_CONFIG, ...config });
  }
  init() {
    const module = new instrumentation.InstrumentationNodeModuleDefinition("graphql", supportedVersions);
    module.files.push(this._addPatchingExecute());
    module.files.push(this._addPatchingParser());
    module.files.push(this._addPatchingValidate());
    return module;
  }
  _addPatchingExecute() {
    return new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      "graphql/execution/execute.js",
      supportedVersions,
      // cannot make it work with appropriate type as execute function has 2
      //types and/cannot import function but only types
      (moduleExports) => {
        if (instrumentation.isWrapped(moduleExports.execute)) {
          this._unwrap(moduleExports, "execute");
        }
        this._wrap(moduleExports, "execute", this._patchExecute(moduleExports.defaultFieldResolver));
        return moduleExports;
      },
      (moduleExports) => {
        if (moduleExports) {
          this._unwrap(moduleExports, "execute");
        }
      }
    );
  }
  _addPatchingParser() {
    return new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      "graphql/language/parser.js",
      supportedVersions,
      (moduleExports) => {
        if (instrumentation.isWrapped(moduleExports.parse)) {
          this._unwrap(moduleExports, "parse");
        }
        this._wrap(moduleExports, "parse", this._patchParse());
        return moduleExports;
      },
      (moduleExports) => {
        if (moduleExports) {
          this._unwrap(moduleExports, "parse");
        }
      }
    );
  }
  _addPatchingValidate() {
    return new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      "graphql/validation/validate.js",
      supportedVersions,
      (moduleExports) => {
        if (instrumentation.isWrapped(moduleExports.validate)) {
          this._unwrap(moduleExports, "validate");
        }
        this._wrap(moduleExports, "validate", this._patchValidate());
        return moduleExports;
      },
      (moduleExports) => {
        if (moduleExports) {
          this._unwrap(moduleExports, "validate");
        }
      }
    );
  }
  _patchExecute(defaultFieldResolved) {
    const instrumentation$1 = this;
    return function execute(original) {
      return function patchExecute() {
        let processedArgs;
        if (arguments.length >= 2) {
          const args = arguments;
          processedArgs = instrumentation$1._wrapExecuteArgs(
            args[0],
            args[1],
            args[2],
            args[3],
            args[4],
            args[5],
            args[6],
            args[7],
            defaultFieldResolved
          );
        } else {
          const args = arguments[0];
          processedArgs = instrumentation$1._wrapExecuteArgs(
            args.schema,
            args.document,
            args.rootValue,
            args.contextValue,
            args.variableValues,
            args.operationName,
            args.fieldResolver,
            args.typeResolver,
            defaultFieldResolved
          );
        }
        const operation = utils.getOperation(processedArgs.document, processedArgs.operationName);
        const span = instrumentation$1._createExecuteSpan(operation, processedArgs);
        processedArgs.contextValue[symbols.OTEL_GRAPHQL_DATA_SYMBOL] = {
          source: processedArgs.document ? processedArgs.document || processedArgs.document[symbols.OTEL_GRAPHQL_DATA_SYMBOL] : void 0,
          span,
          fields: {}
        };
        return core.withActiveSpan(span, () => {
          return instrumentation.safeExecuteInTheMiddle(
            () => {
              return original.apply(this, [processedArgs]);
            },
            (err, result) => {
              instrumentation$1._handleExecutionResult(span, err, result);
            }
          );
        });
      };
    };
  }
  _handleExecutionResult(span, err, result) {
    if (result === void 0 || err) {
      utils.endSpan(span, err);
      return;
    }
    if (utils.isPromise(result)) {
      result.then(
        (resultData) => {
          this._updateSpanFromResult(span, resultData);
          utils.endSpan(span);
        },
        (error) => {
          utils.endSpan(span, error);
        }
      );
    } else {
      this._updateSpanFromResult(span, result);
      utils.endSpan(span);
    }
  }
  /**
   * Applies Sentry-specific span mutations based on the GraphQL execution result:
   * - Marks the execute span as errored if the result contains errors (and no status was set yet)
   * - Optionally renames the containing root span to include the GraphQL operation name(s)
   */
  _updateSpanFromResult(span, result) {
    if (result.errors?.length && !core.spanToJSON(span).status) {
      span.setStatus({ code: core.SPAN_STATUS_ERROR });
    }
    if (!this.getConfig().useOperationNameForRootSpan) {
      return;
    }
    const attributes = core.spanToJSON(span).data;
    const operationType = attributes[AttributeNames.AttributeNames.OPERATION_TYPE];
    const operationName = attributes[AttributeNames.AttributeNames.OPERATION_NAME];
    if (!operationType) {
      return;
    }
    const rootSpan = core.getRootSpan(span);
    const rootSpanAttributes = core.spanToJSON(rootSpan).data;
    const existingOperations = rootSpanAttributes[opentelemetry.SEMANTIC_ATTRIBUTE_SENTRY_GRAPHQL_OPERATION] || [];
    const newOperation = operationName ? `${operationType} ${operationName}` : `${operationType}`;
    if (Array.isArray(existingOperations)) {
      existingOperations.push(newOperation);
      rootSpan.setAttribute(opentelemetry.SEMANTIC_ATTRIBUTE_SENTRY_GRAPHQL_OPERATION, existingOperations);
    } else if (typeof existingOperations === "string") {
      rootSpan.setAttribute(opentelemetry.SEMANTIC_ATTRIBUTE_SENTRY_GRAPHQL_OPERATION, [existingOperations, newOperation]);
    } else {
      rootSpan.setAttribute(opentelemetry.SEMANTIC_ATTRIBUTE_SENTRY_GRAPHQL_OPERATION, newOperation);
    }
    if (!core.spanToJSON(rootSpan).data["original-description"]) {
      rootSpan.setAttribute("original-description", core.spanToJSON(rootSpan).description);
    }
    rootSpan.updateName(
      `${core.spanToJSON(rootSpan).data["original-description"]} (${getGraphqlOperationNamesFromAttribute(
        existingOperations
      )})`
    );
  }
  _patchParse() {
    const instrumentation = this;
    return function parse(original) {
      return function patchParse(source, options) {
        return instrumentation._parse(this, original, source, options);
      };
    };
  }
  _patchValidate() {
    const instrumentation = this;
    return function validate(original) {
      return function patchValidate(schema, documentAST, rules, options, typeInfo) {
        return instrumentation._validate(this, original, schema, documentAST, rules, typeInfo, options);
      };
    };
  }
  _parse(obj, original, source, options) {
    const span = core.startInactiveSpan({ name: _enum.SpanNames.PARSE });
    return core.withActiveSpan(span, () => {
      return instrumentation.safeExecuteInTheMiddle(
        () => {
          return original.call(obj, source, options);
        },
        (err, result) => {
          if (result) {
            const operation = utils.getOperation(result);
            if (!operation) {
              span.updateName(_enum.SpanNames.SCHEMA_PARSE);
            } else if (result.loc) {
              utils.addSpanSource(span, result.loc);
            }
          }
          utils.endSpan(span, err);
        }
      );
    });
  }
  _validate(obj, original, schema, documentAST, rules, typeInfo, options) {
    const span = core.startInactiveSpan({ name: _enum.SpanNames.VALIDATE });
    return core.withActiveSpan(span, () => {
      return instrumentation.safeExecuteInTheMiddle(
        () => {
          return original.call(obj, schema, documentAST, rules, options, typeInfo);
        },
        (err, _errors) => {
          if (!documentAST.loc) {
            span.updateName(_enum.SpanNames.SCHEMA_VALIDATE);
          }
          utils.endSpan(span, err);
        }
      );
    });
  }
  _createExecuteSpan(operation, processedArgs) {
    const span = core.startInactiveSpan({
      name: _enum.SpanNames.EXECUTE,
      attributes: { [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN }
    });
    if (operation) {
      const { operation: operationType, name: nameNode } = operation;
      span.setAttribute(AttributeNames.AttributeNames.OPERATION_TYPE, operationType);
      const operationName = nameNode?.value;
      if (operationName) {
        span.setAttribute(AttributeNames.AttributeNames.OPERATION_NAME, operationName);
        span.updateName(`${operationType} ${operationName}`);
      } else {
        span.updateName(operationType);
      }
    } else {
      let operationName = " ";
      if (processedArgs.operationName) {
        operationName = ` "${processedArgs.operationName}" `;
      }
      operationName = internalTypes.OPERATION_NOT_SUPPORTED.replace("$operationName$", operationName);
      span.setAttribute(AttributeNames.AttributeNames.OPERATION_NAME, operationName);
    }
    if (processedArgs.document?.loc) {
      utils.addSpanSource(span, processedArgs.document.loc);
    }
    return span;
  }
  _wrapExecuteArgs(schema, document, rootValue, contextValue, variableValues, operationName, fieldResolver, typeResolver, defaultFieldResolved) {
    if (!contextValue) {
      contextValue = {};
    }
    if (contextValue[symbols.OTEL_GRAPHQL_DATA_SYMBOL] || this.getConfig().ignoreResolveSpans) {
      return {
        schema,
        document,
        rootValue,
        contextValue,
        variableValues,
        operationName,
        fieldResolver,
        typeResolver
      };
    }
    const isUsingDefaultResolver = fieldResolver == null;
    const fieldResolverForExecute = fieldResolver ?? defaultFieldResolved;
    fieldResolver = utils.wrapFieldResolver(() => this.getConfig(), fieldResolverForExecute, isUsingDefaultResolver);
    if (schema) {
      utils.wrapFields(schema.getQueryType(), () => this.getConfig());
      utils.wrapFields(schema.getMutationType(), () => this.getConfig());
    }
    return {
      schema,
      document,
      rootValue,
      contextValue,
      variableValues,
      operationName,
      fieldResolver,
      typeResolver
    };
  }
}
function getGraphqlOperationNamesFromAttribute(attr) {
  if (Array.isArray(attr)) {
    const sorted = attr.slice().sort();
    if (sorted.length <= 5) {
      return sorted.join(", ");
    } else {
      return `${sorted.slice(0, 5).join(", ")}, +${sorted.length - 5}`;
    }
  }
  return `${attr}`;
}

exports.GraphQLInstrumentation = GraphQLInstrumentation;
//# sourceMappingURL=instrumentation.js.map
