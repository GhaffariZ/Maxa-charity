import A from '../../../esquery/dist/esquery.esm.min.js';
import '../../../meriyah/dist/meriyah2.js';
import '../../../astring/dist/astring.js';
import { t as transformsExports } from './transforms.js';
import '../../../source-map/source-map.js';
import { __exports as astring } from '../../../../_virtual/astring.js';
import { __exports as meriyah } from '../../../../_virtual/meriyah.js';
import { __exports as sourceMap } from '../../../../_virtual/source-map.js';

var __typeError = (msg) => {
  throw TypeError(msg);
};
var __accessCheck = (obj, member, msg) => member.has(obj) || __typeError("Cannot " + msg);
var __privateGet = (obj, member, getter) => (__accessCheck(obj, member, "read from private field"), getter ? getter.call(obj) : member.get(obj));
var __privateAdd = (obj, member, value) => member.has(obj) ? __typeError("Cannot add the same private member more than once") : member instanceof WeakSet ? member.add(obj) : member.set(obj, value);
var __privateSet = (obj, member, value, setter) => (__accessCheck(obj, member, "write to private field"), member.set(obj, value), value);
var __privateMethod = (obj, member, method) => (__accessCheck(obj, member, "access private method"), method);
var _moduleName, _version, _filePath, _configs, _dcModule, _customTransforms, _Transformer_instances, visit_fn, getOperator_fn, collectExportAliases_fn, resolveExportAlias_fn, fromFunctionQuery_fn;
const esquery = A;
const { parse } = meriyah;
const { generate } = astring;
const transforms = transformsExports;
let SourceMapConsumer;
let SourceMapGenerator;
class Transformer {
  /**
   * @param {string} moduleName - The npm package name being instrumented.
   * @param {string} version - The installed semver version string.
   * @param {string} filePath - The relative file path within the package.
   * @param {object[]} configs - Instrumentation configuration objects for this file.
   * @param {string} dcModule - The diagnostics_channel module specifier to inject.
   * @param {Record<string, Function>} [customTransforms] - Optional custom operator overrides.
   */
  constructor(moduleName, version, filePath, configs, dcModule, customTransforms = {}) {
    __privateAdd(this, _Transformer_instances);
    __privateAdd(this, _moduleName, null);
    __privateAdd(this, _version, null);
    __privateAdd(this, _filePath, null);
    __privateAdd(this, _configs, []);
    __privateAdd(this, _dcModule, null);
    __privateAdd(this, _customTransforms, {});
    __privateSet(this, _moduleName, moduleName);
    __privateSet(this, _version, version);
    __privateSet(this, _filePath, filePath);
    __privateSet(this, _configs, configs);
    __privateSet(this, _dcModule, dcModule);
    __privateSet(this, _customTransforms, customTransforms);
  }
  /** No-op — freeing resources is not needed for the JavaScript implementation. */
  free() {
  }
  /**
   * The npm package name being instrumented.
   *
   * @returns {string}
   */
  get moduleName() {
    return __privateGet(this, _moduleName);
  }
  /**
   * The relative file path within the npm package being instrumented.
   *
   * @returns {string}
   */
  get filePath() {
    return __privateGet(this, _filePath);
  }
  /**
   * Instruments `code` by injecting diagnostics_channel tracing around the
   * target functions defined by this transformer's configs.
   *
   * @param {string|Buffer} code - Original JavaScript source, or a Buffer containing UTF-8 source.
   * @param {'esm'|'cjs'|'unknown'} moduleType - Whether the source is an ES module or CommonJS.
   * @param {string|object|null} [sourcemap] - Existing source map (raw string or object) to chain from.
   * @returns {{ code: string, map?: string }}
   *   The transformed source and an optional updated source map.
   * @throws {Error} If no injection points are found for any config.
   */
  transform(code, moduleType, sourcemap) {
    if (Buffer.isBuffer(code)) code = code.toString();
    if (!code) return { code };
    let ast;
    let aliases = {};
    let injectionCount = 0;
    for (const config of __privateGet(this, _configs)) {
      const { astQuery, functionQuery = {} } = config;
      if (!ast) {
        const options = {
          loc: true,
          ranges: true,
          raw: true,
          module: moduleType === "esm"
        };
        try {
          ast = parse(code, options);
        } catch {
          ast = parse(code, { ...options, module: !options.module });
        }
        if (moduleType === "esm") {
          aliases = __privateMethod(this, _Transformer_instances, collectExportAliases_fn).call(this, ast);
        }
      }
      const resolvedFunctionQuery = __privateMethod(this, _Transformer_instances, resolveExportAlias_fn).call(this, functionQuery, aliases);
      const query = astQuery || __privateMethod(this, _Transformer_instances, fromFunctionQuery_fn).call(this, resolvedFunctionQuery);
      const state = {
        ...config,
        dcModule: __privateGet(this, _dcModule),
        moduleType,
        moduleVersion: __privateGet(this, _version),
        functionQuery: resolvedFunctionQuery
      };
      state.operator = __privateMethod(this, _Transformer_instances, getOperator_fn).call(this, state);
      esquery.traverse(ast, esquery.parse(query), (...args) => {
        injectionCount++;
        __privateMethod(this, _Transformer_instances, visit_fn).call(this, state, ...args);
      });
    }
    if (injectionCount === 0 && __privateGet(this, _configs).length > 0) {
      const names = __privateGet(this, _configs).map(({ astQuery, functionQuery = {} }) => {
        if (astQuery) return astQuery;
        const resolvedQuery = __privateMethod(this, _Transformer_instances, resolveExportAlias_fn).call(this, functionQuery, aliases);
        const queryName = (q) => q.methodName || q.privateMethodName || q.functionName || q.expressionName || "constructor";
        const originalName = queryName(functionQuery);
        const originalAlias = functionQuery.className || functionQuery.functionName || functionQuery.expressionName;
        const resolvedAlias = resolvedQuery.className || resolvedQuery.functionName || resolvedQuery.expressionName;
        if (originalAlias && originalAlias !== resolvedAlias) {
          return `${originalAlias} (local name: ${resolvedAlias})`;
        }
        return originalName;
      });
      throw new Error(`Failed to find injection points for: ${JSON.stringify(names)}`);
    }
    if (ast) {
      SourceMapConsumer ?? (SourceMapConsumer = sourceMap.SourceMapConsumer);
      SourceMapGenerator ?? (SourceMapGenerator = sourceMap.SourceMapGenerator);
      const file = `${__privateGet(this, _moduleName)}/${__privateGet(this, _filePath)}`;
      let generator;
      if (sourcemap) {
        const consumer = new SourceMapConsumer(sourcemap);
        consumer.file = file;
        generator = SourceMapGenerator.fromSourceMap(consumer);
      } else {
        generator = new SourceMapGenerator({ file });
      }
      const code2 = generate(ast, { sourceMap: generator });
      const map = generator.toString();
      return { code: code2, map };
    }
    return { code };
  }
}
_moduleName = new WeakMap();
_version = new WeakMap();
_filePath = new WeakMap();
_configs = new WeakMap();
_dcModule = new WeakMap();
_customTransforms = new WeakMap();
_Transformer_instances = new WeakSet();
/**
 * Visitor called for each AST node that matches a config's query.
 * Handles index-based filtering and delegates to the appropriate transform.
 *
 * @param {object} state - Merged config + runtime state for this traversal.
 * @param {...unknown} args - `(node, parent, ancestry)` from esquery traverse.
 */
visit_fn = function(state, ...args) {
  const transform = __privateGet(this, _customTransforms)[state.operator] ?? transforms[state.operator];
  const { index = 0 } = state.functionQuery;
  const [node] = args;
  const type = node.init?.type || node.type;
  if (type !== "ClassDeclaration" && type !== "ClassExpression") {
    if (node.type === "VariableDeclarator") return;
    state.functionIndex = ++state.functionIndex || 0;
    if (index !== null && index !== state.functionIndex) return;
  }
  transform(state, ...args);
};
/**
 * Resolves the operator name (transform function key) for a config.
 *
 * If the config has an explicit `transform` name it is used directly;
 * otherwise the operator is derived from the `kind` field of `functionQuery`.
 *
 * @param {{ transform?: string, functionQuery: { kind?: string } }} state
 * @returns {string} Operator name, e.g. `'tracePromise'`.
 */
getOperator_fn = function({ transform, functionQuery: { kind } }) {
  if (transform) return transform;
  switch (kind) {
    case "Async":
      return "tracePromise";
    case "Auto":
      return "traceAuto";
    case "Callback":
      return "traceCallback";
    case "Sync":
      return "traceSync";
    default:
      return "traceSync";
  }
};
/**
 * Collects a map of exported name → local name from `export { local as exported }`
 * declarations so that instrumentation configs that reference export names can be
 * resolved to local identifiers.
 *
 * @param {import('estree').Program} ast
 * @returns {Record<string, string>} Map of exported name to local name.
 */
collectExportAliases_fn = function(ast) {
  const aliases = {};
  for (const node of ast.body) {
    if (node.type === "ExportNamedDeclaration" && !node.source) {
      for (const spec of node.specifiers) {
        if (spec.exported && spec.local) {
          const exportedName = spec.exported.name ?? spec.exported.value;
          const localName = spec.local.name ?? spec.local.value;
          if (exportedName && localName) {
            aliases[exportedName] = localName;
          }
        }
      }
    }
  }
  return aliases;
};
/**
 * If `functionQuery.isExportAlias` is set, replaces the exported identifier in
 * `functionQuery` with the corresponding local name from `aliases`.
 *
 * @param {object} functionQuery
 * @param {Record<string, string>} aliases - Map produced by {@link #collectExportAliases}.
 * @returns {object} Resolved function query (may be the original object if unchanged).
 */
resolveExportAlias_fn = function(functionQuery, aliases) {
  if (!functionQuery.isExportAlias) return functionQuery;
  const { functionName, expressionName, className } = functionQuery;
  if (functionName && aliases[functionName]) {
    return { ...functionQuery, functionName: aliases[functionName] };
  }
  if (expressionName && aliases[expressionName]) {
    return { ...functionQuery, expressionName: aliases[expressionName] };
  }
  if (className && aliases[className]) {
    return { ...functionQuery, className: aliases[className] };
  }
  return functionQuery;
};
/**
 * Builds a comma-separated esquery selector string from a `functionQuery` descriptor.
 *
 * Handles class methods, standalone functions, and expression assignments, producing
 * multiple selector alternatives joined with `, `.
 *
 * @param {object} functionQuery
 * @param {string} [functionQuery.className]
 * @param {string} [functionQuery.methodName]
 * @param {string} [functionQuery.privateMethodName]
 * @param {string} [functionQuery.functionName]
 * @param {string} [functionQuery.expressionName]
 * @returns {string} esquery selector.
 */
fromFunctionQuery_fn = function(functionQuery) {
  const { functionName, expressionName, className, objectName, propertyName } = functionQuery;
  const type = functionQuery.privateMethodName ? "PrivateIdentifier" : "Identifier";
  const queries = [];
  let method = functionQuery.methodName || functionQuery.privateMethodName;
  if (className) {
    method ?? (method = "constructor");
    queries.push(
      `[id.name="${className}"]`,
      `[id.name="${className}"] > ClassExpression`,
      `[id.name="${className}"] > ClassBody > [key.name="${method}"][key.type=${type}] > [async]`,
      `[id.name="${className}"] > ClassExpression > ClassBody > [key.name="${method}"][key.type=${type}] > [async]`
    );
  } else if (method) {
    queries.push(
      `ClassBody > [key.name="${method}"][key.type=${type}] > [async]`,
      `Property[key.name="${method}"][key.type=${type}] > [async]`
    );
  }
  if (functionName) {
    queries.push(`FunctionDeclaration[id.name="${functionName}"][async]`);
  } else if (expressionName) {
    queries.push(
      `FunctionExpression[id.name="${expressionName}"][async]`,
      `ArrowFunctionExpression[id.name="${expressionName}"][async]`,
      `VariableDeclarator[id.name="${expressionName}"] > FunctionExpression[async]`,
      `VariableDeclarator[id.name="${expressionName}"] > ArrowFunctionExpression[async]`,
      `AssignmentExpression[left.property.name="${expressionName}"] > FunctionExpression[async]`,
      `AssignmentExpression[left.property.name="${expressionName}"] > ArrowFunctionExpression[async]`,
      `AssignmentExpression[left.name="${expressionName}"] > FunctionExpression[async]`,
      `AssignmentExpression[left.name="${expressionName}"] > ArrowFunctionExpression[async]`
    );
  }
  if (objectName || propertyName) {
    if (!objectName || !propertyName) {
      throw new Error(
        `functionQuery: 'objectName' and 'propertyName' must be used together (got objectName=${objectName}, propertyName=${propertyName})`
      );
    }
    const objectSelector = objectName === "this" ? "left.object.type=ThisExpression" : `left.object.name="${objectName}"`;
    queries.push(
      `AssignmentExpression[${objectSelector}][left.property.name="${propertyName}"] > [async]`
    );
  }
  return queries.join(", ");
};
var transformer = { Transformer };

export { transformer as t };
//# sourceMappingURL=transformer.js.map
