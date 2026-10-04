import { __module as transforms$1 } from '../../../../_virtual/transforms.js';
import A from '../../../esquery/dist/esquery.esm.min.js';
import '../../../meriyah/dist/meriyah2.js';
import { __exports as meriyah } from '../../../../_virtual/meriyah.js';

const esquery = A;
const { parse } = meriyah;
const tracingChannelPredicate = (node) => node.declarations?.[0]?.id?.properties?.[0]?.value?.name === "tr_ch_apm_tracingChannel";
const CHANNEL_REGEX = /[^\w]/g;
const formatChannelVariable = (channelName) => `tr_ch_apm$${channelName.replace(CHANNEL_REGEX, "_")}`;
const transforms = transforms$1.exports = {
  /**
   * Injects a `tracingChannel` import/require into the program body if one is not
   * already present.
   *
   * @param {{ dcModule: string, sourceType: 'module'|'script' }} state
   * @param {import('estree').Program} node - The program root node.
   */
  tracingChannelImport({ dcModule, moduleType }, node) {
    if (node.body.some(tracingChannelPredicate)) return;
    const options = { module: moduleType === "esm" };
    const index = node.body.findIndex((child) => child.directive === "use strict");
    const dc = moduleType === "esm" ? `import tr_ch_apm_dc from "${dcModule}"` : `const tr_ch_apm_dc = require("${dcModule}")`;
    const tracingChannel = "const { tracingChannel: tr_ch_apm_tracingChannel } = tr_ch_apm_dc";
    const hasSubscribers = `const tr_ch_apm_hasSubscribers = ch => ch.start.hasSubscribers
      || ch.end.hasSubscribers
      || ch.asyncStart.hasSubscribers
      || ch.asyncEnd.hasSubscribers
      || ch.error.hasSubscribers`;
    node.body.splice(
      index + 1,
      0,
      parse(dc, options).body[0],
      parse(tracingChannel, options).body[0],
      parse(hasSubscribers, options).body[0]
    );
  },
  /**
   * Injects a `tracingChannel(...)` variable declaration for the config's channel
   * into the program body, also ensuring the import is present.
   *
   * @param {{ channelName: string, module: { name: string }, dcModule: string, sourceType: 'module'|'script' }} state
   * @param {import('estree').Program} node - The program root node.
   */
  tracingChannelDeclaration(state, node) {
    const { channelName, module: { name } } = state;
    const channelVariable = formatChannelVariable(channelName);
    if (node.body.some((child) => child.declarations?.[0]?.id?.name === channelVariable)) return;
    transforms.tracingChannelImport(state, node);
    const index = node.body.findIndex(tracingChannelPredicate);
    const code = `
      const ${channelVariable} = tr_ch_apm_tracingChannel("orchestrion:${name}:${channelName}")
    `;
    node.body.splice(index + 1, 0, parse(code).body[0]);
  },
  traceCallback: traceAny,
  tracePromise: traceAny,
  traceSync: traceAny,
  traceAuto: traceAny
};
function wrapParams(params) {
  const originalParams = params || [];
  const numberedParams = originalParams.filter((param) => param.type !== "RestElement" && param.type !== "AssignmentPattern").map((_, i) => ({ type: "Identifier", name: `__apm$arg${i}` }));
  return [
    ...numberedParams,
    { type: "RestElement", argument: { type: "Identifier", name: "__apm$args" } }
  ];
}
function traceAny(state, node, _parent, ancestry) {
  const program = ancestry[ancestry.length - 1];
  if (node.type === "ClassDeclaration" || node.type === "ClassExpression") {
    traceInstanceMethod(state, node, program);
  } else {
    traceFunction(state, node, program);
  }
}
function traceFunction(state, node, program) {
  transforms.tracingChannelDeclaration(state, program);
  const { functionQuery: { methodName, privateMethodName, functionName, expressionName, propertyName } } = state;
  const isConstructor = methodName === "constructor" || !methodName && !privateMethodName && !functionName && !expressionName && !propertyName;
  const type = isConstructor ? "ArrowFunctionExpression" : node.type;
  const params = node.params;
  node.body = wrap(state, {
    type,
    params,
    body: node.body,
    async: node.async,
    expression: false,
    generator: node.generator
  }, program);
  node.params = wrapParams(params);
  node.generator = false;
  node.async = false;
  wrapSuper(state, node);
}
function traceInstanceMethod(state, node, program) {
  const { functionQuery, operator } = state;
  const { methodName } = functionQuery;
  if (!methodName) return;
  const classBody = node.body;
  if (classBody.body.some(({ key }) => key.name === methodName)) return;
  let ctor = classBody.body.find(({ kind }) => kind === "constructor");
  transforms.tracingChannelDeclaration(state, program);
  if (!ctor) {
    ctor = parse(
      node.superClass ? "class A extends Object { constructor (...args) { super(...args) } }" : "class A { constructor () {} }"
    ).body[0].body.body[0];
    classBody.body.unshift(ctor);
  }
  const ctorBody = parse(`
    const __apm$${methodName} = this["${methodName}"]
    this["${methodName}"] = function () {}
    if (typeof __apm$${methodName} === 'function') {
      Object.defineProperty(this["${methodName}"], 'length', {
        value: __apm$${methodName}.length,
        configurable: true
      })
    }
  `).body;
  const fn = ctorBody[1].expression.right;
  fn.params = [{ type: "RestElement", argument: { type: "Identifier", name: "__apm$args" } }];
  fn.async = operator === "tracePromise";
  fn.body = wrap(state, { type: "Identifier", name: `__apm$${methodName}` }, program);
  wrapSuper(state, fn);
  ctor.value.body.body.push(...ctorBody);
}
function wrap(state, node, program) {
  const { operator, moduleVersion } = state;
  const { returnKind } = state.functionQuery;
  const iterPatch = returnKind ? generateIterPatch(state, returnKind, program) : "";
  let wrapper;
  if (operator === "traceCallback") wrapper = wrapCallback(state, node, iterPatch);
  if (operator === "tracePromise") wrapper = wrapPromise(state, node, iterPatch);
  if (operator === "traceSync") wrapper = wrapSync(state, node, iterPatch);
  if (operator === "traceAuto") wrapper = wrapAuto(state, node, iterPatch);
  const args = (node.params || []).filter((param) => param.type !== "RestElement" && param.type !== "AssignmentPattern").map((_, i) => `__apm$arg${i}`).concat("...__apm$args").join(", ");
  const block = wrapper.body[0].body;
  const common = parse(node.type === "ArrowFunctionExpression" ? `
    const __apm$arguments = [${args}];
    const __apm$ctx = {
      arguments: __apm$arguments,
      moduleVersion: ${JSON.stringify(moduleVersion)}
    };
    const __apm$traced = () => {
      const __apm$wrapped = () => {};
      return __apm$wrapped(...__apm$arguments);
    };
  ` : `
    const __apm$arguments = [${args}].slice(0, arguments.length);
    const __apm$ctx = {
      arguments: __apm$arguments,
      self: this,
      moduleVersion: ${JSON.stringify(moduleVersion)}
    };
    const __apm$traced = () => {
      const __apm$wrapped = () => {};
      return __apm$wrapped.apply(this, __apm$arguments);
    };
  `).body;
  block.body.unshift(...common);
  esquery.query(block, "[id.name=__apm$wrapped]")[0].init = node;
  return block;
}
function wrapSuper(_state, node) {
  const members = /* @__PURE__ */ new Set();
  esquery.traverse(
    node.body,
    esquery.parse("[object.type=Super]"),
    (node2, parent) => {
      const { name } = node2.property;
      let child;
      if (parent.callee) {
        const { expression } = parse(`__apm$super['${name}'].call(this)`).body[0];
        parent.callee = child = expression.callee;
        parent.arguments.unshift(...expression.arguments);
      } else {
        parent.expression = child = parse(`__apm$super['${name}']`).body[0];
      }
      child.computed = parent.callee.computed;
      child.optional = parent.callee.optional;
      members.add(name);
    }
  );
  for (const name of members) {
    const member = parse(`
      class Wrapper {
        wrapper () {
          __apm$super['${name}'] = super['${name}']
        }
      }
    `).body[0].body.body[0].value.body.body[0];
    node.body.body.unshift(member);
  }
  if (members.size > 0) {
    node.body.body.unshift(parse("const __apm$super = {}").body[0]);
  }
}
function wrapAuto(state, node, iterPatch = "") {
  const cbWrapperAST = wrapCallback(state, node, iterPatch);
  const promiseWrapperAST = wrapPromise(state, node, iterPatch);
  const [
    getCbArg,
    checkHasSubscribers,
    defineWrappedCb,
    checkCbIsFunction,
    spliceCbArg,
    runStores
  ] = cbWrapperAST.body[0].body.body;
  const fallbackToPromise = {
    type: "IfStatement",
    test: checkCbIsFunction.test,
    consequent: { type: "BlockStatement", body: promiseWrapperAST.body[0].body.body },
    alternate: null
  };
  cbWrapperAST.body[0].body.body = [
    getCbArg,
    fallbackToPromise,
    checkHasSubscribers,
    defineWrappedCb,
    spliceCbArg,
    runStores
  ];
  return cbWrapperAST;
}
function wrapCallback(state, node, iterPatch = "") {
  const { channelName, functionQuery: { callbackIndex = -1 } } = state;
  const channelVariable = formatChannelVariable(channelName);
  return parse(`
    function wrapper () {
      const __apm$cb = Array.prototype.at.call(__apm$arguments, ${callbackIndex});

      if (!${channelVariable}.start.hasSubscribers) return __apm$traced();

      function __apm$wrappedCb(err, res) {
        if (err) {
          __apm$ctx.error = err;
          ${channelVariable}.error.publish(__apm$ctx);
        } else {
          __apm$ctx.result = res;
          ${iterPatch}
        }

        ${channelVariable}.asyncStart.runStores(__apm$ctx, () => {
          try {
            if (__apm$cb) {
              return __apm$cb.apply(this, arguments);
            }
          } finally {
            ${channelVariable}.asyncEnd.publish(__apm$ctx);
          }
        });
      }

      if (typeof __apm$cb !== 'function') {
        return __apm$traced();
      }
      Array.prototype.splice.call(__apm$arguments, ${callbackIndex}, 1, __apm$wrappedCb);

      return ${channelVariable}.start.runStores(__apm$ctx, () => {
        try {
          return __apm$traced();
        } catch (err) {
          __apm$ctx.error = err;
          ${channelVariable}.error.publish(__apm$ctx);
          throw err;
        } finally {
         __apm$ctx.self ??= this;
          ${channelVariable}.end.publish(__apm$ctx);
        }
      });
    }
  `);
}
function wrapPromise(state, node, iterPatch = "") {
  const { channelName } = state;
  const channelVariable = formatChannelVariable(channelName);
  return parse(`
    function wrapper () {
      if (!tr_ch_apm_hasSubscribers(${channelVariable})) return __apm$traced();

      return ${channelVariable}.start.runStores(__apm$ctx, () => {
        try {
          let promise = __apm$traced();
          if (typeof promise?.then !== 'function') {
            __apm$ctx.result = promise;
            ${iterPatch}
            return __apm$ctx.result;
          }
          // Mirror Node.js core diagnostics_channel behaviour: for native Promise
          // instances, chain normally (safe since there is no subclass API to
          // preserve). For Promise subclasses and other thenables, side-chain the
          // callbacks for event publishing and return the original so that any
          // subclass-specific methods (e.g. APIPromise.withResponse()) remain
          // accessible to the caller.
          if (promise instanceof Promise && promise.constructor === Promise) {
            return promise.then(
              result => {
                __apm$ctx.result = result;
                ${iterPatch}
                ${channelVariable}.asyncStart.publish(__apm$ctx);
                ${channelVariable}.asyncEnd.publish(__apm$ctx);
                return __apm$ctx.result;
              },
              err => {
                __apm$ctx.error = err;
                ${channelVariable}.error.publish(__apm$ctx);
                ${channelVariable}.asyncStart.publish(__apm$ctx);
                ${channelVariable}.asyncEnd.publish(__apm$ctx);
                throw err;
              }
            );
          }
          promise.then(
            result => {
              __apm$ctx.result = result;
              ${iterPatch}
              ${channelVariable}.asyncStart.publish(__apm$ctx);
              ${channelVariable}.asyncEnd.publish(__apm$ctx);
            },
            err => {
              __apm$ctx.error = err;
              ${channelVariable}.error.publish(__apm$ctx);
              ${channelVariable}.asyncStart.publish(__apm$ctx);
              ${channelVariable}.asyncEnd.publish(__apm$ctx);
            }
          );
          return promise;
        } catch (err) {
          __apm$ctx.error = err;
          ${channelVariable}.error.publish(__apm$ctx);
          throw err;
        } finally {
          __apm$ctx.self ??= this;
          ${channelVariable}.end.publish(__apm$ctx);
        }
      });
    }
  `);
}
function wrapSync(state, node, iterPatch = "") {
  const { channelName } = state;
  const channelVariable = formatChannelVariable(channelName);
  return parse(`
    function wrapper () {
      if (!tr_ch_apm_hasSubscribers(${channelVariable})) return __apm$traced();

      return ${channelVariable}.start.runStores(__apm$ctx, () => {
        try {
          __apm$ctx.result = __apm$traced();
          ${iterPatch}
        } catch (err) {
          __apm$ctx.error = err;
          ${channelVariable}.error.publish(__apm$ctx);
          throw err;
        } finally {
         __apm$ctx.self ??= this;
          ${channelVariable}.end.publish(__apm$ctx);
        }
        return __apm$ctx.result;
      });
    }
  `);
}
function declareIteratorChannel(state, program) {
  const { channelName, module: { name } } = state;
  const iterChannelVariable = formatChannelVariable(channelName + ":next");
  if (program.body.some((child) => child.declarations?.[0]?.id?.name === iterChannelVariable)) return;
  const channelVariable = formatChannelVariable(channelName);
  const index = program.body.findIndex(
    (child) => child.declarations?.[0]?.id?.name === channelVariable
  );
  const code = `const ${iterChannelVariable} = tr_ch_apm_tracingChannel("orchestrion:${name}:${channelName}:next")`;
  program.body.splice(index + 1, 0, parse(code).body[0]);
}
function generateIterPatch(state, returnKind, program) {
  const { channelName } = state;
  const traceMethod = returnKind === "Iterator" ? "traceSync" : "tracePromise";
  const iterChannelVariable = formatChannelVariable(channelName + ":next");
  declareIteratorChannel(state, program);
  return `
    const __apm$iter = __apm$ctx.result;
    if (__apm$iter != null && typeof __apm$iter.next === 'function') {
      const __apm$patchIter = function (method) {
        const __apm$orig = __apm$iter[method];
        if (typeof __apm$orig !== 'function') return;
        __apm$iter[method] = function () {
          const __apm$iterArgs = Array.prototype.slice.call(arguments);
          if (!tr_ch_apm_hasSubscribers(${iterChannelVariable})) return __apm$orig.apply(this, __apm$iterArgs);
          __apm$ctx.method = method;
          __apm$ctx.arguments = __apm$iterArgs;
          return ${iterChannelVariable}.${traceMethod}(__apm$orig, __apm$ctx, this, ...__apm$iterArgs);
        };
      };
      __apm$patchIter('next');
      __apm$patchIter('throw');
      __apm$patchIter('return');
    }
  `;
}

var transformsExports = transforms$1.exports;

export { transformsExports as t };
//# sourceMappingURL=transforms.js.map
