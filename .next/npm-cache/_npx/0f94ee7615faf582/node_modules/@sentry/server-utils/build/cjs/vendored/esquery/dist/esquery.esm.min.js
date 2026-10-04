Object.defineProperties(exports, { __esModule: { value: true }, [Symbol.toStringTag]: { value: 'Module' } });

function e(e2, t2) {
  (null == t2 || t2 > e2.length) && (t2 = e2.length);
  for (var r2 = 0, n2 = Array(t2); r2 < t2; r2++) n2[r2] = e2[r2];
  return n2;
}
function t(e2, t2) {
  return (function(e3) {
    if (Array.isArray(e3)) return e3;
  })(e2) || (function(e3, t3) {
    var r2 = null == e3 ? null : "undefined" != typeof Symbol && e3[Symbol.iterator] || e3["@@iterator"];
    if (null != r2) {
      var n2, o2, a2, i2, s2 = [], u2 = true, l2 = false;
      try {
        if (a2 = (r2 = r2.call(e3)).next, 0 === t3) ; else for (; !(u2 = (n2 = a2.call(r2)).done) && (s2.push(n2.value), s2.length !== t3); u2 = true) ;
      } catch (e4) {
        l2 = true, o2 = e4;
      } finally {
        try {
          if (!u2 && null != r2.return && (i2 = r2.return(), Object(i2) !== i2)) return;
        } finally {
          if (l2) throw o2;
        }
      }
      return s2;
    }
  })(e2, t2) || o(e2, t2) || (function() {
    throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  })();
}
function r(t2) {
  return (function(t3) {
    if (Array.isArray(t3)) return e(t3);
  })(t2) || (function(e2) {
    if ("undefined" != typeof Symbol && null != e2[Symbol.iterator] || null != e2["@@iterator"]) return Array.from(e2);
  })(t2) || o(t2) || (function() {
    throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  })();
}
function n(e2) {
  return (n = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(e3) {
    return typeof e3;
  } : function(e3) {
    return e3 && "function" == typeof Symbol && e3.constructor === Symbol && e3 !== Symbol.prototype ? "symbol" : typeof e3;
  })(e2);
}
function o(t2, r2) {
  if (t2) {
    if ("string" == typeof t2) return e(t2, r2);
    var n2 = {}.toString.call(t2).slice(8, -1);
    return "Object" === n2 && t2.constructor && (n2 = t2.constructor.name), "Map" === n2 || "Set" === n2 ? Array.from(t2) : "Arguments" === n2 || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n2) ? e(t2, r2) : void 0;
  }
}
function a(e2, t2) {
  return e2(t2 = { exports: {} }, t2.exports), t2.exports;
}
var i = a((function(e2, t2) {
  !(function e3(t3) {
    var r2, n2, o2, a2, i2, s2;
    function u2(e4) {
      var t4, r3, n3 = {};
      for (t4 in e4) e4.hasOwnProperty(t4) && (r3 = e4[t4], n3[t4] = "object" == typeof r3 && null !== r3 ? u2(r3) : r3);
      return n3;
    }
    function l2(e4, t4) {
      this.parent = e4, this.key = t4;
    }
    function c2(e4, t4, r3, n3) {
      this.node = e4, this.path = t4, this.wrap = r3, this.ref = n3;
    }
    function f2() {
    }
    function p2(e4) {
      return null != e4 && ("object" == typeof e4 && "string" == typeof e4.type);
    }
    function h2(e4, t4) {
      return (e4 === r2.ObjectExpression || e4 === r2.ObjectPattern) && "properties" === t4;
    }
    function y2(e4, t4) {
      for (var r3 = e4.length - 1; r3 >= 0; --r3) if (e4[r3].node === t4) return true;
      return false;
    }
    function d2(e4, t4) {
      return new f2().traverse(e4, t4);
    }
    function m2(e4, t4) {
      var r3;
      return r3 = (function(e5, t5) {
        var r4, n3, o3, a3;
        for (n3 = e5.length, o3 = 0; n3; ) t5(e5[a3 = o3 + (r4 = n3 >>> 1)]) ? n3 = r4 : (o3 = a3 + 1, n3 -= r4 + 1);
        return o3;
      })(t4, (function(t5) {
        return t5.range[0] > e4.range[0];
      })), e4.extendedRange = [e4.range[0], e4.range[1]], r3 !== t4.length && (e4.extendedRange[1] = t4[r3].range[0]), (r3 -= 1) >= 0 && (e4.extendedRange[0] = t4[r3].range[1]), e4;
    }
    return r2 = { AssignmentExpression: "AssignmentExpression", AssignmentPattern: "AssignmentPattern", ArrayExpression: "ArrayExpression", ArrayPattern: "ArrayPattern", ArrowFunctionExpression: "ArrowFunctionExpression", AwaitExpression: "AwaitExpression", BlockStatement: "BlockStatement", BinaryExpression: "BinaryExpression", BreakStatement: "BreakStatement", CallExpression: "CallExpression", CatchClause: "CatchClause", ChainExpression: "ChainExpression", ClassBody: "ClassBody", ClassDeclaration: "ClassDeclaration", ClassExpression: "ClassExpression", ComprehensionBlock: "ComprehensionBlock", ComprehensionExpression: "ComprehensionExpression", ConditionalExpression: "ConditionalExpression", ContinueStatement: "ContinueStatement", DebuggerStatement: "DebuggerStatement", DirectiveStatement: "DirectiveStatement", DoWhileStatement: "DoWhileStatement", EmptyStatement: "EmptyStatement", ExportAllDeclaration: "ExportAllDeclaration", ExportDefaultDeclaration: "ExportDefaultDeclaration", ExportNamedDeclaration: "ExportNamedDeclaration", ExportSpecifier: "ExportSpecifier", ExpressionStatement: "ExpressionStatement", ForStatement: "ForStatement", ForInStatement: "ForInStatement", ForOfStatement: "ForOfStatement", FunctionDeclaration: "FunctionDeclaration", FunctionExpression: "FunctionExpression", GeneratorExpression: "GeneratorExpression", Identifier: "Identifier", IfStatement: "IfStatement", ImportExpression: "ImportExpression", ImportDeclaration: "ImportDeclaration", ImportDefaultSpecifier: "ImportDefaultSpecifier", ImportNamespaceSpecifier: "ImportNamespaceSpecifier", ImportSpecifier: "ImportSpecifier", Literal: "Literal", LabeledStatement: "LabeledStatement", LogicalExpression: "LogicalExpression", MemberExpression: "MemberExpression", MetaProperty: "MetaProperty", MethodDefinition: "MethodDefinition", ModuleSpecifier: "ModuleSpecifier", NewExpression: "NewExpression", ObjectExpression: "ObjectExpression", ObjectPattern: "ObjectPattern", PrivateIdentifier: "PrivateIdentifier", Program: "Program", Property: "Property", PropertyDefinition: "PropertyDefinition", RestElement: "RestElement", ReturnStatement: "ReturnStatement", SequenceExpression: "SequenceExpression", SpreadElement: "SpreadElement", Super: "Super", SwitchStatement: "SwitchStatement", SwitchCase: "SwitchCase", TaggedTemplateExpression: "TaggedTemplateExpression", TemplateElement: "TemplateElement", TemplateLiteral: "TemplateLiteral", ThisExpression: "ThisExpression", ThrowStatement: "ThrowStatement", TryStatement: "TryStatement", UnaryExpression: "UnaryExpression", UpdateExpression: "UpdateExpression", VariableDeclaration: "VariableDeclaration", VariableDeclarator: "VariableDeclarator", WhileStatement: "WhileStatement", WithStatement: "WithStatement", YieldExpression: "YieldExpression" }, o2 = { AssignmentExpression: ["left", "right"], AssignmentPattern: ["left", "right"], ArrayExpression: ["elements"], ArrayPattern: ["elements"], ArrowFunctionExpression: ["params", "body"], AwaitExpression: ["argument"], BlockStatement: ["body"], BinaryExpression: ["left", "right"], BreakStatement: ["label"], CallExpression: ["callee", "arguments"], CatchClause: ["param", "body"], ChainExpression: ["expression"], ClassBody: ["body"], ClassDeclaration: ["id", "superClass", "body"], ClassExpression: ["id", "superClass", "body"], ComprehensionBlock: ["left", "right"], ComprehensionExpression: ["blocks", "filter", "body"], ConditionalExpression: ["test", "consequent", "alternate"], ContinueStatement: ["label"], DebuggerStatement: [], DirectiveStatement: [], DoWhileStatement: ["body", "test"], EmptyStatement: [], ExportAllDeclaration: ["source"], ExportDefaultDeclaration: ["declaration"], ExportNamedDeclaration: ["declaration", "specifiers", "source"], ExportSpecifier: ["exported", "local"], ExpressionStatement: ["expression"], ForStatement: ["init", "test", "update", "body"], ForInStatement: ["left", "right", "body"], ForOfStatement: ["left", "right", "body"], FunctionDeclaration: ["id", "params", "body"], FunctionExpression: ["id", "params", "body"], GeneratorExpression: ["blocks", "filter", "body"], Identifier: [], IfStatement: ["test", "consequent", "alternate"], ImportExpression: ["source"], ImportDeclaration: ["specifiers", "source"], ImportDefaultSpecifier: ["local"], ImportNamespaceSpecifier: ["local"], ImportSpecifier: ["imported", "local"], Literal: [], LabeledStatement: ["label", "body"], LogicalExpression: ["left", "right"], MemberExpression: ["object", "property"], MetaProperty: ["meta", "property"], MethodDefinition: ["key", "value"], ModuleSpecifier: [], NewExpression: ["callee", "arguments"], ObjectExpression: ["properties"], ObjectPattern: ["properties"], PrivateIdentifier: [], Program: ["body"], Property: ["key", "value"], PropertyDefinition: ["key", "value"], RestElement: ["argument"], ReturnStatement: ["argument"], SequenceExpression: ["expressions"], SpreadElement: ["argument"], Super: [], SwitchStatement: ["discriminant", "cases"], SwitchCase: ["test", "consequent"], TaggedTemplateExpression: ["tag", "quasi"], TemplateElement: [], TemplateLiteral: ["quasis", "expressions"], ThisExpression: [], ThrowStatement: ["argument"], TryStatement: ["block", "handler", "finalizer"], UnaryExpression: ["argument"], UpdateExpression: ["argument"], VariableDeclaration: ["declarations"], VariableDeclarator: ["id", "init"], WhileStatement: ["test", "body"], WithStatement: ["object", "body"], YieldExpression: ["argument"] }, n2 = { Break: a2 = {}, Skip: i2 = {}, Remove: s2 = {} }, l2.prototype.replace = function(e4) {
      this.parent[this.key] = e4;
    }, l2.prototype.remove = function() {
      return Array.isArray(this.parent) ? (this.parent.splice(this.key, 1), true) : (this.replace(null), false);
    }, f2.prototype.path = function() {
      var e4, t4, r3, n3, o3;
      function a3(e5, t5) {
        if (Array.isArray(t5)) for (r3 = 0, n3 = t5.length; r3 < n3; ++r3) e5.push(t5[r3]);
        else e5.push(t5);
      }
      if (!this.__current.path) return null;
      for (o3 = [], e4 = 2, t4 = this.__leavelist.length; e4 < t4; ++e4) a3(o3, this.__leavelist[e4].path);
      return a3(o3, this.__current.path), o3;
    }, f2.prototype.type = function() {
      return this.current().type || this.__current.wrap;
    }, f2.prototype.parents = function() {
      var e4, t4, r3;
      for (r3 = [], e4 = 1, t4 = this.__leavelist.length; e4 < t4; ++e4) r3.push(this.__leavelist[e4].node);
      return r3;
    }, f2.prototype.current = function() {
      return this.__current.node;
    }, f2.prototype.__execute = function(e4, t4) {
      var r3, n3;
      return n3 = void 0, r3 = this.__current, this.__current = t4, this.__state = null, e4 && (n3 = e4.call(this, t4.node, this.__leavelist[this.__leavelist.length - 1].node)), this.__current = r3, n3;
    }, f2.prototype.notify = function(e4) {
      this.__state = e4;
    }, f2.prototype.skip = function() {
      this.notify(i2);
    }, f2.prototype.break = function() {
      this.notify(a2);
    }, f2.prototype.remove = function() {
      this.notify(s2);
    }, f2.prototype.__initialize = function(e4, t4) {
      this.visitor = t4, this.root = e4, this.__worklist = [], this.__leavelist = [], this.__current = null, this.__state = null, this.__fallback = null, "iteration" === t4.fallback ? this.__fallback = Object.keys : "function" == typeof t4.fallback && (this.__fallback = t4.fallback), this.__keys = o2, t4.keys && (this.__keys = Object.assign(Object.create(this.__keys), t4.keys));
    }, f2.prototype.traverse = function(e4, t4) {
      var r3, n3, o3, s3, u3, l3, f3, d3, m3, x2, v2, g2;
      for (this.__initialize(e4, t4), g2 = {}, r3 = this.__worklist, n3 = this.__leavelist, r3.push(new c2(e4, null, null, null)), n3.push(new c2(null, null, null, null)); r3.length; ) if ((o3 = r3.pop()) !== g2) {
        if (o3.node) {
          if (l3 = this.__execute(t4.enter, o3), this.__state === a2 || l3 === a2) return;
          if (r3.push(g2), n3.push(o3), this.__state === i2 || l3 === i2) continue;
          if (u3 = (s3 = o3.node).type || o3.wrap, !(x2 = this.__keys[u3])) {
            if (!this.__fallback) throw new Error("Unknown node type " + u3 + ".");
            x2 = this.__fallback(s3);
          }
          for (d3 = x2.length; (d3 -= 1) >= 0; ) if (v2 = s3[f3 = x2[d3]]) {
            if (Array.isArray(v2)) {
              for (m3 = v2.length; (m3 -= 1) >= 0; ) if (v2[m3] && !y2(n3, v2[m3])) {
                if (h2(u3, x2[d3])) o3 = new c2(v2[m3], [f3, m3], "Property", null);
                else {
                  if (!p2(v2[m3])) continue;
                  o3 = new c2(v2[m3], [f3, m3], null, null);
                }
                r3.push(o3);
              }
            } else if (p2(v2)) {
              if (y2(n3, v2)) continue;
              r3.push(new c2(v2, f3, null, null));
            }
          }
        }
      } else if (o3 = n3.pop(), l3 = this.__execute(t4.leave, o3), this.__state === a2 || l3 === a2) return;
    }, f2.prototype.replace = function(e4, t4) {
      var r3, n3, o3, u3, f3, y3, d3, m3, x2, v2, g2, A2, E;
      function b(e5) {
        var t5, n4, o4, a3;
        if (e5.ref.remove()) {
          for (n4 = e5.ref.key, a3 = e5.ref.parent, t5 = r3.length; t5--; ) if ((o4 = r3[t5]).ref && o4.ref.parent === a3) {
            if (o4.ref.key < n4) break;
            --o4.ref.key;
          }
        }
      }
      for (this.__initialize(e4, t4), g2 = {}, r3 = this.__worklist, n3 = this.__leavelist, y3 = new c2(e4, null, null, new l2(A2 = { root: e4 }, "root")), r3.push(y3), n3.push(y3); r3.length; ) if ((y3 = r3.pop()) !== g2) {
        if (void 0 !== (f3 = this.__execute(t4.enter, y3)) && f3 !== a2 && f3 !== i2 && f3 !== s2 && (y3.ref.replace(f3), y3.node = f3), this.__state !== s2 && f3 !== s2 || (b(y3), y3.node = null), this.__state === a2 || f3 === a2) return A2.root;
        if ((o3 = y3.node) && (r3.push(g2), n3.push(y3), this.__state !== i2 && f3 !== i2)) {
          if (u3 = o3.type || y3.wrap, !(x2 = this.__keys[u3])) {
            if (!this.__fallback) throw new Error("Unknown node type " + u3 + ".");
            x2 = this.__fallback(o3);
          }
          for (d3 = x2.length; (d3 -= 1) >= 0; ) if (v2 = o3[E = x2[d3]]) if (Array.isArray(v2)) {
            for (m3 = v2.length; (m3 -= 1) >= 0; ) if (v2[m3]) {
              if (h2(u3, x2[d3])) y3 = new c2(v2[m3], [E, m3], "Property", new l2(v2, m3));
              else {
                if (!p2(v2[m3])) continue;
                y3 = new c2(v2[m3], [E, m3], null, new l2(v2, m3));
              }
              r3.push(y3);
            }
          } else p2(v2) && r3.push(new c2(v2, E, null, new l2(o3, E)));
        }
      } else if (y3 = n3.pop(), void 0 !== (f3 = this.__execute(t4.leave, y3)) && f3 !== a2 && f3 !== i2 && f3 !== s2 && y3.ref.replace(f3), this.__state !== s2 && f3 !== s2 || b(y3), this.__state === a2 || f3 === a2) return A2.root;
      return A2.root;
    }, t3.Syntax = r2, t3.traverse = d2, t3.replace = function(e4, t4) {
      return new f2().replace(e4, t4);
    }, t3.attachComments = function(e4, t4, r3) {
      var o3, a3, i3, s3, l3 = [];
      if (!e4.range) throw new Error("attachComments needs range information");
      if (!r3.length) {
        if (t4.length) {
          for (i3 = 0, a3 = t4.length; i3 < a3; i3 += 1) (o3 = u2(t4[i3])).extendedRange = [0, e4.range[0]], l3.push(o3);
          e4.leadingComments = l3;
        }
        return e4;
      }
      for (i3 = 0, a3 = t4.length; i3 < a3; i3 += 1) l3.push(m2(u2(t4[i3]), r3));
      return s3 = 0, d2(e4, { enter: function(e5) {
        for (var t5; s3 < l3.length && !((t5 = l3[s3]).extendedRange[1] > e5.range[0]); ) t5.extendedRange[1] === e5.range[0] ? (e5.leadingComments || (e5.leadingComments = []), e5.leadingComments.push(t5), l3.splice(s3, 1)) : s3 += 1;
        return s3 === l3.length ? n2.Break : l3[s3].extendedRange[0] > e5.range[1] ? n2.Skip : void 0;
      } }), s3 = 0, d2(e4, { leave: function(e5) {
        for (var t5; s3 < l3.length && (t5 = l3[s3], !(e5.range[1] < t5.extendedRange[0])); ) e5.range[1] === t5.extendedRange[0] ? (e5.trailingComments || (e5.trailingComments = []), e5.trailingComments.push(t5), l3.splice(s3, 1)) : s3 += 1;
        return s3 === l3.length ? n2.Break : l3[s3].extendedRange[0] > e5.range[1] ? n2.Skip : void 0;
      } }), e4;
    }, t3.VisitorKeys = o2, t3.VisitorOption = n2, t3.Controller = f2, t3.cloneEnvironment = function() {
      return e3({});
    }, t3;
  })(t2);
})), s = a((function(e2) {
  e2.exports && (e2.exports = (function() {
    function e3(t2, r2, n2, o2) {
      this.message = t2, this.expected = r2, this.found = n2, this.location = o2, this.name = "SyntaxError", "function" == typeof Error.captureStackTrace && Error.captureStackTrace(this, e3);
    }
    return (function(e4, t2) {
      function r2() {
        this.constructor = e4;
      }
      r2.prototype = t2.prototype, e4.prototype = new r2();
    })(e3, Error), e3.buildMessage = function(e4, t2) {
      var r2 = { literal: function(e5) {
        return '"' + o2(e5.text) + '"';
      }, class: function(e5) {
        var t3, r3 = "";
        for (t3 = 0; t3 < e5.parts.length; t3++) r3 += e5.parts[t3] instanceof Array ? a2(e5.parts[t3][0]) + "-" + a2(e5.parts[t3][1]) : a2(e5.parts[t3]);
        return "[" + (e5.inverted ? "^" : "") + r3 + "]";
      }, any: function(e5) {
        return "any character";
      }, end: function(e5) {
        return "end of input";
      }, other: function(e5) {
        return e5.description;
      } };
      function n2(e5) {
        return e5.charCodeAt(0).toString(16).toUpperCase();
      }
      function o2(e5) {
        return e5.replace(/\\/g, "\\\\").replace(/"/g, '\\"').replace(/\0/g, "\\0").replace(/\t/g, "\\t").replace(/\n/g, "\\n").replace(/\r/g, "\\r").replace(/[\x00-\x0F]/g, (function(e6) {
          return "\\x0" + n2(e6);
        })).replace(/[\x10-\x1F\x7F-\x9F]/g, (function(e6) {
          return "\\x" + n2(e6);
        }));
      }
      function a2(e5) {
        return e5.replace(/\\/g, "\\\\").replace(/\]/g, "\\]").replace(/\^/g, "\\^").replace(/-/g, "\\-").replace(/\0/g, "\\0").replace(/\t/g, "\\t").replace(/\n/g, "\\n").replace(/\r/g, "\\r").replace(/[\x00-\x0F]/g, (function(e6) {
          return "\\x0" + n2(e6);
        })).replace(/[\x10-\x1F\x7F-\x9F]/g, (function(e6) {
          return "\\x" + n2(e6);
        }));
      }
      return "Expected " + (function(e5) {
        var t3, n3, o3, a3 = new Array(e5.length);
        for (t3 = 0; t3 < e5.length; t3++) a3[t3] = (o3 = e5[t3], r2[o3.type](o3));
        if (a3.sort(), a3.length > 0) {
          for (t3 = 1, n3 = 1; t3 < a3.length; t3++) a3[t3 - 1] !== a3[t3] && (a3[n3] = a3[t3], n3++);
          a3.length = n3;
        }
        switch (a3.length) {
          case 1:
            return a3[0];
          case 2:
            return a3[0] + " or " + a3[1];
          default:
            return a3.slice(0, -1).join(", ") + ", or " + a3[a3.length - 1];
        }
      })(e4) + " but " + (function(e5) {
        return e5 ? '"' + o2(e5) + '"' : "end of input";
      })(t2) + " found.";
    }, { SyntaxError: e3, parse: function(t2, r2) {
      r2 = void 0 !== r2 ? r2 : {};
      var n2, o2, a2, i2, s2 = {}, u2 = { start: Ae }, l2 = Ae, c2 = de(" ", false), f2 = /^[^ [\],():#!=><~+.]/, p2 = me([" ", "[", "]", ",", "(", ")", ":", "#", "!", "=", ">", "<", "~", "+", "."], true, false), h2 = de(">", false), y2 = de("~", false), d2 = de("+", false), m2 = de(",", false), x2 = function(e4, t3) {
        return [e4].concat(t3.map((function(e5) {
          return e5[3];
        })));
      }, v2 = de("!", false), g2 = de("*", false), A2 = de("#", false), E = de("[", false), b = de("]", false), S = /^[><!]/, _ = me([">", "<", "!"], false, false), C = de("=", false), P = function(e4) {
        return (e4 || "") + "=";
      }, w = /^[><]/, k = me([">", "<"], false, false), D = de(".", false), I = function(e4, t3, r3) {
        return { type: "attribute", name: e4, operator: t3, value: r3 };
      }, j = de('"', false), T = /^[^\\"]/, F = me(["\\", '"'], true, false), R = de("\\", false), O = { type: "any" }, L = function(e4, t3) {
        return e4 + t3;
      }, M = function(e4) {
        return { type: "literal", value: (t3 = e4.join(""), t3.replace(/\\(.)/g, (function(e5, t4) {
          switch (t4) {
            case "b":
              return "\b";
            case "f":
              return "\f";
            case "n":
              return "\n";
            case "r":
              return "\r";
            case "t":
              return "	";
            case "v":
              return "\v";
            default:
              return t4;
          }
        }))) };
        var t3;
      }, B = de("'", false), U = /^[^\\']/, K = me(["\\", "'"], true, false), N = /^[0-9]/, W = me([["0", "9"]], false, false), V = de("type(", false), q = /^[^ )]/, G = me([" ", ")"], true, false), z = de(")", false), H = /^[imsu]/, Y = me(["i", "m", "s", "u"], false, false), $ = de("/", false), J = /^[^\]\\]/, Q = me(["]", "\\"], true, false), X = /^[^\/\\[]/, Z = me(["/", "\\", "["], true, false), ee = de(":not(", false), te = de(":matches(", false), re = function(e4) {
        return { type: "matches", selectors: e4 };
      }, ne = de(":is(", false), oe = de(":has(", false), ae = de(":first-child", false), ie = de(":last-child", false), se = de(":nth-child(", false), ue = de(":nth-last-child(", false), le = de(":", false), ce = 0, fe = [{ line: 1, column: 1 }], pe = 0, he = [], ye = {};
      if ("startRule" in r2) {
        if (!(r2.startRule in u2)) throw new Error(`Can't start parsing from rule "` + r2.startRule + '".');
        l2 = u2[r2.startRule];
      }
      function de(e4, t3) {
        return { type: "literal", text: e4, ignoreCase: t3 };
      }
      function me(e4, t3, r3) {
        return { type: "class", parts: e4, inverted: t3, ignoreCase: r3 };
      }
      function xe(e4) {
        var r3, n3 = fe[e4];
        if (n3) return n3;
        for (r3 = e4 - 1; !fe[r3]; ) r3--;
        for (n3 = { line: (n3 = fe[r3]).line, column: n3.column }; r3 < e4; ) 10 === t2.charCodeAt(r3) ? (n3.line++, n3.column = 1) : n3.column++, r3++;
        return fe[e4] = n3, n3;
      }
      function ve(e4, t3) {
        var r3 = xe(e4), n3 = xe(t3);
        return { start: { offset: e4, line: r3.line, column: r3.column }, end: { offset: t3, line: n3.line, column: n3.column } };
      }
      function ge(e4) {
        ce < pe || (ce > pe && (pe = ce, he = []), he.push(e4));
      }
      function Ae() {
        var e4, t3, r3, n3, o3 = 36 * ce + 0, a3 = ye[o3];
        return a3 ? (ce = a3.nextPos, a3.result) : (e4 = ce, (t3 = Ee()) !== s2 && (r3 = _e()) !== s2 && Ee() !== s2 ? e4 = t3 = 1 === (n3 = r3).length ? n3[0] : { type: "matches", selectors: n3 } : (ce = e4, e4 = s2), e4 === s2 && (e4 = ce, (t3 = Ee()) !== s2 && (t3 = void 0), e4 = t3), ye[o3] = { nextPos: ce, result: e4 }, e4);
      }
      function Ee() {
        var e4, r3, n3 = 36 * ce + 1, o3 = ye[n3];
        if (o3) return ce = o3.nextPos, o3.result;
        for (e4 = [], 32 === t2.charCodeAt(ce) ? (r3 = " ", ce++) : (r3 = s2, ge(c2)); r3 !== s2; ) e4.push(r3), 32 === t2.charCodeAt(ce) ? (r3 = " ", ce++) : (r3 = s2, ge(c2));
        return ye[n3] = { nextPos: ce, result: e4 }, e4;
      }
      function be() {
        var e4, r3, n3, o3 = 36 * ce + 2, a3 = ye[o3];
        if (a3) return ce = a3.nextPos, a3.result;
        if (r3 = [], f2.test(t2.charAt(ce)) ? (n3 = t2.charAt(ce), ce++) : (n3 = s2, ge(p2)), n3 !== s2) for (; n3 !== s2; ) r3.push(n3), f2.test(t2.charAt(ce)) ? (n3 = t2.charAt(ce), ce++) : (n3 = s2, ge(p2));
        else r3 = s2;
        return r3 !== s2 && (r3 = r3.join("")), e4 = r3, ye[o3] = { nextPos: ce, result: e4 }, e4;
      }
      function Se() {
        var e4, r3, n3, o3 = 36 * ce + 3, a3 = ye[o3];
        return a3 ? (ce = a3.nextPos, a3.result) : (e4 = ce, (r3 = Ee()) !== s2 ? (62 === t2.charCodeAt(ce) ? (n3 = ">", ce++) : (n3 = s2, ge(h2)), n3 !== s2 && Ee() !== s2 ? e4 = r3 = "child" : (ce = e4, e4 = s2)) : (ce = e4, e4 = s2), e4 === s2 && (e4 = ce, (r3 = Ee()) !== s2 ? (126 === t2.charCodeAt(ce) ? (n3 = "~", ce++) : (n3 = s2, ge(y2)), n3 !== s2 && Ee() !== s2 ? e4 = r3 = "sibling" : (ce = e4, e4 = s2)) : (ce = e4, e4 = s2), e4 === s2 && (e4 = ce, (r3 = Ee()) !== s2 ? (43 === t2.charCodeAt(ce) ? (n3 = "+", ce++) : (n3 = s2, ge(d2)), n3 !== s2 && Ee() !== s2 ? e4 = r3 = "adjacent" : (ce = e4, e4 = s2)) : (ce = e4, e4 = s2), e4 === s2 && (e4 = ce, 32 === t2.charCodeAt(ce) ? (r3 = " ", ce++) : (r3 = s2, ge(c2)), r3 !== s2 && (n3 = Ee()) !== s2 ? e4 = r3 = "descendant" : (ce = e4, e4 = s2)))), ye[o3] = { nextPos: ce, result: e4 }, e4);
      }
      function _e() {
        var e4, r3, n3, o3, a3, i3, u3, l3, c3 = 36 * ce + 5, f3 = ye[c3];
        if (f3) return ce = f3.nextPos, f3.result;
        if (e4 = ce, (r3 = Pe()) !== s2) {
          for (n3 = [], o3 = ce, (a3 = Ee()) !== s2 ? (44 === t2.charCodeAt(ce) ? (i3 = ",", ce++) : (i3 = s2, ge(m2)), i3 !== s2 && (u3 = Ee()) !== s2 && (l3 = Pe()) !== s2 ? o3 = a3 = [a3, i3, u3, l3] : (ce = o3, o3 = s2)) : (ce = o3, o3 = s2); o3 !== s2; ) n3.push(o3), o3 = ce, (a3 = Ee()) !== s2 ? (44 === t2.charCodeAt(ce) ? (i3 = ",", ce++) : (i3 = s2, ge(m2)), i3 !== s2 && (u3 = Ee()) !== s2 && (l3 = Pe()) !== s2 ? o3 = a3 = [a3, i3, u3, l3] : (ce = o3, o3 = s2)) : (ce = o3, o3 = s2);
          n3 !== s2 ? e4 = r3 = x2(r3, n3) : (ce = e4, e4 = s2);
        } else ce = e4, e4 = s2;
        return ye[c3] = { nextPos: ce, result: e4 }, e4;
      }
      function Ce() {
        var e4, t3, r3, n3, o3, a3 = 36 * ce + 6, i3 = ye[a3];
        return i3 ? (ce = i3.nextPos, i3.result) : (e4 = ce, (t3 = Se()) === s2 && (t3 = null), t3 !== s2 && (r3 = Pe()) !== s2 ? (o3 = r3, e4 = t3 = (n3 = t3) ? { type: n3, left: { type: "exactNode" }, right: o3 } : o3) : (ce = e4, e4 = s2), ye[a3] = { nextPos: ce, result: e4 }, e4);
      }
      function Pe() {
        var e4, t3, r3, n3, o3, a3, i3, u3 = 36 * ce + 7, l3 = ye[u3];
        if (l3) return ce = l3.nextPos, l3.result;
        if (e4 = ce, (t3 = we()) !== s2) {
          for (r3 = [], n3 = ce, (o3 = Se()) !== s2 && (a3 = we()) !== s2 ? n3 = o3 = [o3, a3] : (ce = n3, n3 = s2); n3 !== s2; ) r3.push(n3), n3 = ce, (o3 = Se()) !== s2 && (a3 = we()) !== s2 ? n3 = o3 = [o3, a3] : (ce = n3, n3 = s2);
          r3 !== s2 ? (i3 = t3, e4 = t3 = r3.reduce((function(e5, t4) {
            return { type: t4[0], left: e5, right: t4[1] };
          }), i3)) : (ce = e4, e4 = s2);
        } else ce = e4, e4 = s2;
        return ye[u3] = { nextPos: ce, result: e4 }, e4;
      }
      function we() {
        var e4, r3, n3, o3, a3, i3, u3, l3 = 36 * ce + 8, c3 = ye[l3];
        if (c3) return ce = c3.nextPos, c3.result;
        if (e4 = ce, 33 === t2.charCodeAt(ce) ? (r3 = "!", ce++) : (r3 = s2, ge(v2)), r3 === s2 && (r3 = null), r3 !== s2) {
          if (n3 = [], (o3 = ke()) !== s2) for (; o3 !== s2; ) n3.push(o3), o3 = ke();
          else n3 = s2;
          n3 !== s2 ? (a3 = r3, u3 = 1 === (i3 = n3).length ? i3[0] : { type: "compound", selectors: i3 }, a3 && (u3.subject = true), e4 = r3 = u3) : (ce = e4, e4 = s2);
        } else ce = e4, e4 = s2;
        return ye[l3] = { nextPos: ce, result: e4 }, e4;
      }
      function ke() {
        var e4, r3 = 36 * ce + 9, n3 = ye[r3];
        return n3 ? (ce = n3.nextPos, n3.result) : ((e4 = (function() {
          var e5, r4, n4 = 36 * ce + 10, o3 = ye[n4];
          return o3 ? (ce = o3.nextPos, o3.result) : (42 === t2.charCodeAt(ce) ? (r4 = "*", ce++) : (r4 = s2, ge(g2)), r4 !== s2 && (r4 = { type: "wildcard", value: r4 }), e5 = r4, ye[n4] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3 = 36 * ce + 11, a3 = ye[o3];
          return a3 ? (ce = a3.nextPos, a3.result) : (e5 = ce, 35 === t2.charCodeAt(ce) ? (r4 = "#", ce++) : (r4 = s2, ge(A2)), r4 === s2 && (r4 = null), r4 !== s2 && (n4 = be()) !== s2 ? e5 = r4 = { type: "identifier", value: n4 } : (ce = e5, e5 = s2), ye[o3] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3, a3 = 36 * ce + 12, i3 = ye[a3];
          return i3 ? (ce = i3.nextPos, i3.result) : (e5 = ce, 91 === t2.charCodeAt(ce) ? (r4 = "[", ce++) : (r4 = s2, ge(E)), r4 !== s2 && Ee() !== s2 && (n4 = (function() {
            var e6, r5, n5, o4, a4 = 36 * ce + 16, i4 = ye[a4];
            return i4 ? (ce = i4.nextPos, i4.result) : (e6 = ce, (r5 = De()) !== s2 && Ee() !== s2 && (n5 = (function() {
              var e7, r6, n6, o5 = 36 * ce + 14, a5 = ye[o5];
              return a5 ? (ce = a5.nextPos, a5.result) : (e7 = ce, 33 === t2.charCodeAt(ce) ? (r6 = "!", ce++) : (r6 = s2, ge(v2)), r6 === s2 && (r6 = null), r6 !== s2 ? (61 === t2.charCodeAt(ce) ? (n6 = "=", ce++) : (n6 = s2, ge(C)), n6 !== s2 ? (r6 = P(r6), e7 = r6) : (ce = e7, e7 = s2)) : (ce = e7, e7 = s2), ye[o5] = { nextPos: ce, result: e7 }, e7);
            })()) !== s2 && Ee() !== s2 ? ((o4 = (function() {
              var e7, r6, n6, o5, a5, i5 = 36 * ce + 20, u3 = ye[i5];
              if (u3) return ce = u3.nextPos, u3.result;
              if (e7 = ce, "type(" === t2.substr(ce, 5) ? (r6 = "type(", ce += 5) : (r6 = s2, ge(V)), r6 !== s2) if (Ee() !== s2) {
                if (n6 = [], q.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(G)), o5 !== s2) for (; o5 !== s2; ) n6.push(o5), q.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(G));
                else n6 = s2;
                n6 !== s2 && (o5 = Ee()) !== s2 ? (41 === t2.charCodeAt(ce) ? (a5 = ")", ce++) : (a5 = s2, ge(z)), a5 !== s2 ? (r6 = { type: "type", value: n6.join("") }, e7 = r6) : (ce = e7, e7 = s2)) : (ce = e7, e7 = s2);
              } else ce = e7, e7 = s2;
              else ce = e7, e7 = s2;
              return ye[i5] = { nextPos: ce, result: e7 }, e7;
            })()) === s2 && (o4 = (function() {
              var e7, r6, n6, o5, a5, i5, u3 = 36 * ce + 22, l3 = ye[u3];
              if (l3) return ce = l3.nextPos, l3.result;
              if (e7 = ce, 47 === t2.charCodeAt(ce) ? (r6 = "/", ce++) : (r6 = s2, ge($)), r6 !== s2) {
                if (n6 = [], (o5 = Ie()) === s2 && (o5 = je()) === s2 && (o5 = Te()), o5 !== s2) for (; o5 !== s2; ) n6.push(o5), (o5 = Ie()) === s2 && (o5 = je()) === s2 && (o5 = Te());
                else n6 = s2;
                n6 !== s2 ? (47 === t2.charCodeAt(ce) ? (o5 = "/", ce++) : (o5 = s2, ge($)), o5 !== s2 ? ((a5 = (function() {
                  var e8, r7, n7 = 36 * ce + 21, o6 = ye[n7];
                  if (o6) return ce = o6.nextPos, o6.result;
                  if (e8 = [], H.test(t2.charAt(ce)) ? (r7 = t2.charAt(ce), ce++) : (r7 = s2, ge(Y)), r7 !== s2) for (; r7 !== s2; ) e8.push(r7), H.test(t2.charAt(ce)) ? (r7 = t2.charAt(ce), ce++) : (r7 = s2, ge(Y));
                  else e8 = s2;
                  return ye[n7] = { nextPos: ce, result: e8 }, e8;
                })()) === s2 && (a5 = null), a5 !== s2 ? (i5 = a5, r6 = { type: "regexp", value: new RegExp(n6.join(""), i5 ? i5.join("") : "") }, e7 = r6) : (ce = e7, e7 = s2)) : (ce = e7, e7 = s2)) : (ce = e7, e7 = s2);
              } else ce = e7, e7 = s2;
              return ye[u3] = { nextPos: ce, result: e7 }, e7;
            })()), o4 !== s2 ? (r5 = I(r5, n5, o4), e6 = r5) : (ce = e6, e6 = s2)) : (ce = e6, e6 = s2), e6 === s2 && (e6 = ce, (r5 = De()) !== s2 && Ee() !== s2 && (n5 = (function() {
              var e7, r6, n6, o5 = 36 * ce + 13, a5 = ye[o5];
              return a5 ? (ce = a5.nextPos, a5.result) : (e7 = ce, S.test(t2.charAt(ce)) ? (r6 = t2.charAt(ce), ce++) : (r6 = s2, ge(_)), r6 === s2 && (r6 = null), r6 !== s2 ? (61 === t2.charCodeAt(ce) ? (n6 = "=", ce++) : (n6 = s2, ge(C)), n6 !== s2 ? (r6 = P(r6), e7 = r6) : (ce = e7, e7 = s2)) : (ce = e7, e7 = s2), e7 === s2 && (w.test(t2.charAt(ce)) ? (e7 = t2.charAt(ce), ce++) : (e7 = s2, ge(k))), ye[o5] = { nextPos: ce, result: e7 }, e7);
            })()) !== s2 && Ee() !== s2 ? ((o4 = (function() {
              var e7, r6, n6, o5, a5, i5, u3 = 36 * ce + 17, l3 = ye[u3];
              if (l3) return ce = l3.nextPos, l3.result;
              if (e7 = ce, 34 === t2.charCodeAt(ce) ? (r6 = '"', ce++) : (r6 = s2, ge(j)), r6 !== s2) {
                for (n6 = [], T.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(F)), o5 === s2 && (o5 = ce, 92 === t2.charCodeAt(ce) ? (a5 = "\\", ce++) : (a5 = s2, ge(R)), a5 !== s2 ? (t2.length > ce ? (i5 = t2.charAt(ce), ce++) : (i5 = s2, ge(O)), i5 !== s2 ? (a5 = L(a5, i5), o5 = a5) : (ce = o5, o5 = s2)) : (ce = o5, o5 = s2)); o5 !== s2; ) n6.push(o5), T.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(F)), o5 === s2 && (o5 = ce, 92 === t2.charCodeAt(ce) ? (a5 = "\\", ce++) : (a5 = s2, ge(R)), a5 !== s2 ? (t2.length > ce ? (i5 = t2.charAt(ce), ce++) : (i5 = s2, ge(O)), i5 !== s2 ? (a5 = L(a5, i5), o5 = a5) : (ce = o5, o5 = s2)) : (ce = o5, o5 = s2));
                n6 !== s2 ? (34 === t2.charCodeAt(ce) ? (o5 = '"', ce++) : (o5 = s2, ge(j)), o5 !== s2 ? (r6 = M(n6), e7 = r6) : (ce = e7, e7 = s2)) : (ce = e7, e7 = s2);
              } else ce = e7, e7 = s2;
              if (e7 === s2) if (e7 = ce, 39 === t2.charCodeAt(ce) ? (r6 = "'", ce++) : (r6 = s2, ge(B)), r6 !== s2) {
                for (n6 = [], U.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(K)), o5 === s2 && (o5 = ce, 92 === t2.charCodeAt(ce) ? (a5 = "\\", ce++) : (a5 = s2, ge(R)), a5 !== s2 ? (t2.length > ce ? (i5 = t2.charAt(ce), ce++) : (i5 = s2, ge(O)), i5 !== s2 ? (a5 = L(a5, i5), o5 = a5) : (ce = o5, o5 = s2)) : (ce = o5, o5 = s2)); o5 !== s2; ) n6.push(o5), U.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(K)), o5 === s2 && (o5 = ce, 92 === t2.charCodeAt(ce) ? (a5 = "\\", ce++) : (a5 = s2, ge(R)), a5 !== s2 ? (t2.length > ce ? (i5 = t2.charAt(ce), ce++) : (i5 = s2, ge(O)), i5 !== s2 ? (a5 = L(a5, i5), o5 = a5) : (ce = o5, o5 = s2)) : (ce = o5, o5 = s2));
                n6 !== s2 ? (39 === t2.charCodeAt(ce) ? (o5 = "'", ce++) : (o5 = s2, ge(B)), o5 !== s2 ? (r6 = M(n6), e7 = r6) : (ce = e7, e7 = s2)) : (ce = e7, e7 = s2);
              } else ce = e7, e7 = s2;
              return ye[u3] = { nextPos: ce, result: e7 }, e7;
            })()) === s2 && (o4 = (function() {
              var e7, r6, n6, o5, a5, i5, u3, l3 = 36 * ce + 18, c3 = ye[l3];
              if (c3) return ce = c3.nextPos, c3.result;
              for (e7 = ce, r6 = ce, n6 = [], N.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(W)); o5 !== s2; ) n6.push(o5), N.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(W));
              if (n6 !== s2 ? (46 === t2.charCodeAt(ce) ? (o5 = ".", ce++) : (o5 = s2, ge(D)), o5 !== s2 ? r6 = n6 = [n6, o5] : (ce = r6, r6 = s2)) : (ce = r6, r6 = s2), r6 === s2 && (r6 = null), r6 !== s2) {
                if (n6 = [], N.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(W)), o5 !== s2) for (; o5 !== s2; ) n6.push(o5), N.test(t2.charAt(ce)) ? (o5 = t2.charAt(ce), ce++) : (o5 = s2, ge(W));
                else n6 = s2;
                n6 !== s2 ? (i5 = n6, u3 = (a5 = r6) ? [].concat.apply([], a5).join("") : "", r6 = { type: "literal", value: parseFloat(u3 + i5.join("")) }, e7 = r6) : (ce = e7, e7 = s2);
              } else ce = e7, e7 = s2;
              return ye[l3] = { nextPos: ce, result: e7 }, e7;
            })()) === s2 && (o4 = (function() {
              var e7, t3, r6 = 36 * ce + 19, n6 = ye[r6];
              return n6 ? (ce = n6.nextPos, n6.result) : ((t3 = be()) !== s2 && (t3 = { type: "literal", value: t3 }), e7 = t3, ye[r6] = { nextPos: ce, result: e7 }, e7);
            })()), o4 !== s2 ? (r5 = I(r5, n5, o4), e6 = r5) : (ce = e6, e6 = s2)) : (ce = e6, e6 = s2), e6 === s2 && (e6 = ce, (r5 = De()) !== s2 && (r5 = { type: "attribute", name: r5 }), e6 = r5)), ye[a4] = { nextPos: ce, result: e6 }, e6);
          })()) !== s2 && Ee() !== s2 ? (93 === t2.charCodeAt(ce) ? (o3 = "]", ce++) : (o3 = s2, ge(b)), o3 !== s2 ? e5 = r4 = n4 : (ce = e5, e5 = s2)) : (ce = e5, e5 = s2), ye[a3] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3, a3, i3, u3, l3, c3 = 36 * ce + 26, f3 = ye[c3];
          if (f3) return ce = f3.nextPos, f3.result;
          if (e5 = ce, 46 === t2.charCodeAt(ce) ? (r4 = ".", ce++) : (r4 = s2, ge(D)), r4 !== s2) if ((n4 = be()) !== s2) {
            for (o3 = [], a3 = ce, 46 === t2.charCodeAt(ce) ? (i3 = ".", ce++) : (i3 = s2, ge(D)), i3 !== s2 && (u3 = be()) !== s2 ? a3 = i3 = [i3, u3] : (ce = a3, a3 = s2); a3 !== s2; ) o3.push(a3), a3 = ce, 46 === t2.charCodeAt(ce) ? (i3 = ".", ce++) : (i3 = s2, ge(D)), i3 !== s2 && (u3 = be()) !== s2 ? a3 = i3 = [i3, u3] : (ce = a3, a3 = s2);
            o3 !== s2 ? (l3 = n4, r4 = { type: "field", name: o3.reduce((function(e6, t3) {
              return e6 + t3[0] + t3[1];
            }), l3) }, e5 = r4) : (ce = e5, e5 = s2);
          } else ce = e5, e5 = s2;
          else ce = e5, e5 = s2;
          return ye[c3] = { nextPos: ce, result: e5 }, e5;
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3, a3 = 36 * ce + 27, i3 = ye[a3];
          return i3 ? (ce = i3.nextPos, i3.result) : (e5 = ce, ":not(" === t2.substr(ce, 5) ? (r4 = ":not(", ce += 5) : (r4 = s2, ge(ee)), r4 !== s2 && Ee() !== s2 && (n4 = _e()) !== s2 && Ee() !== s2 ? (41 === t2.charCodeAt(ce) ? (o3 = ")", ce++) : (o3 = s2, ge(z)), o3 !== s2 ? e5 = r4 = { type: "not", selectors: n4 } : (ce = e5, e5 = s2)) : (ce = e5, e5 = s2), ye[a3] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3, a3 = 36 * ce + 28, i3 = ye[a3];
          return i3 ? (ce = i3.nextPos, i3.result) : (e5 = ce, ":matches(" === t2.substr(ce, 9) ? (r4 = ":matches(", ce += 9) : (r4 = s2, ge(te)), r4 !== s2 && Ee() !== s2 && (n4 = _e()) !== s2 && Ee() !== s2 ? (41 === t2.charCodeAt(ce) ? (o3 = ")", ce++) : (o3 = s2, ge(z)), o3 !== s2 ? (r4 = re(n4), e5 = r4) : (ce = e5, e5 = s2)) : (ce = e5, e5 = s2), ye[a3] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3, a3 = 36 * ce + 29, i3 = ye[a3];
          return i3 ? (ce = i3.nextPos, i3.result) : (e5 = ce, ":is(" === t2.substr(ce, 4) ? (r4 = ":is(", ce += 4) : (r4 = s2, ge(ne)), r4 !== s2 && Ee() !== s2 && (n4 = _e()) !== s2 && Ee() !== s2 ? (41 === t2.charCodeAt(ce) ? (o3 = ")", ce++) : (o3 = s2, ge(z)), o3 !== s2 ? (r4 = re(n4), e5 = r4) : (ce = e5, e5 = s2)) : (ce = e5, e5 = s2), ye[a3] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3, a3 = 36 * ce + 30, i3 = ye[a3];
          return i3 ? (ce = i3.nextPos, i3.result) : (e5 = ce, ":has(" === t2.substr(ce, 5) ? (r4 = ":has(", ce += 5) : (r4 = s2, ge(oe)), r4 !== s2 && Ee() !== s2 && (n4 = (function() {
            var e6, r5, n5, o4, a4, i4, u3, l3, c3 = 36 * ce + 4, f3 = ye[c3];
            if (f3) return ce = f3.nextPos, f3.result;
            if (e6 = ce, (r5 = Ce()) !== s2) {
              for (n5 = [], o4 = ce, (a4 = Ee()) !== s2 ? (44 === t2.charCodeAt(ce) ? (i4 = ",", ce++) : (i4 = s2, ge(m2)), i4 !== s2 && (u3 = Ee()) !== s2 && (l3 = Ce()) !== s2 ? o4 = a4 = [a4, i4, u3, l3] : (ce = o4, o4 = s2)) : (ce = o4, o4 = s2); o4 !== s2; ) n5.push(o4), o4 = ce, (a4 = Ee()) !== s2 ? (44 === t2.charCodeAt(ce) ? (i4 = ",", ce++) : (i4 = s2, ge(m2)), i4 !== s2 && (u3 = Ee()) !== s2 && (l3 = Ce()) !== s2 ? o4 = a4 = [a4, i4, u3, l3] : (ce = o4, o4 = s2)) : (ce = o4, o4 = s2);
              n5 !== s2 ? e6 = r5 = x2(r5, n5) : (ce = e6, e6 = s2);
            } else ce = e6, e6 = s2;
            return ye[c3] = { nextPos: ce, result: e6 }, e6;
          })()) !== s2 && Ee() !== s2 ? (41 === t2.charCodeAt(ce) ? (o3 = ")", ce++) : (o3 = s2, ge(z)), o3 !== s2 ? e5 = r4 = { type: "has", selectors: n4 } : (ce = e5, e5 = s2)) : (ce = e5, e5 = s2), ye[a3] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4 = 36 * ce + 31, o3 = ye[n4];
          return o3 ? (ce = o3.nextPos, o3.result) : (":first-child" === t2.substr(ce, 12) ? (r4 = ":first-child", ce += 12) : (r4 = s2, ge(ae)), r4 !== s2 && (r4 = Fe(1)), e5 = r4, ye[n4] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4 = 36 * ce + 32, o3 = ye[n4];
          return o3 ? (ce = o3.nextPos, o3.result) : (":last-child" === t2.substr(ce, 11) ? (r4 = ":last-child", ce += 11) : (r4 = s2, ge(ie)), r4 !== s2 && (r4 = Re(1)), e5 = r4, ye[n4] = { nextPos: ce, result: e5 }, e5);
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3, a3, i3 = 36 * ce + 33, u3 = ye[i3];
          if (u3) return ce = u3.nextPos, u3.result;
          if (e5 = ce, ":nth-child(" === t2.substr(ce, 11) ? (r4 = ":nth-child(", ce += 11) : (r4 = s2, ge(se)), r4 !== s2) if (Ee() !== s2) {
            if (n4 = [], N.test(t2.charAt(ce)) ? (o3 = t2.charAt(ce), ce++) : (o3 = s2, ge(W)), o3 !== s2) for (; o3 !== s2; ) n4.push(o3), N.test(t2.charAt(ce)) ? (o3 = t2.charAt(ce), ce++) : (o3 = s2, ge(W));
            else n4 = s2;
            n4 !== s2 && (o3 = Ee()) !== s2 ? (41 === t2.charCodeAt(ce) ? (a3 = ")", ce++) : (a3 = s2, ge(z)), a3 !== s2 ? (r4 = Fe(parseInt(n4.join(""), 10)), e5 = r4) : (ce = e5, e5 = s2)) : (ce = e5, e5 = s2);
          } else ce = e5, e5 = s2;
          else ce = e5, e5 = s2;
          return ye[i3] = { nextPos: ce, result: e5 }, e5;
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3, a3, i3 = 36 * ce + 34, u3 = ye[i3];
          if (u3) return ce = u3.nextPos, u3.result;
          if (e5 = ce, ":nth-last-child(" === t2.substr(ce, 16) ? (r4 = ":nth-last-child(", ce += 16) : (r4 = s2, ge(ue)), r4 !== s2) if (Ee() !== s2) {
            if (n4 = [], N.test(t2.charAt(ce)) ? (o3 = t2.charAt(ce), ce++) : (o3 = s2, ge(W)), o3 !== s2) for (; o3 !== s2; ) n4.push(o3), N.test(t2.charAt(ce)) ? (o3 = t2.charAt(ce), ce++) : (o3 = s2, ge(W));
            else n4 = s2;
            n4 !== s2 && (o3 = Ee()) !== s2 ? (41 === t2.charCodeAt(ce) ? (a3 = ")", ce++) : (a3 = s2, ge(z)), a3 !== s2 ? (r4 = Re(parseInt(n4.join(""), 10)), e5 = r4) : (ce = e5, e5 = s2)) : (ce = e5, e5 = s2);
          } else ce = e5, e5 = s2;
          else ce = e5, e5 = s2;
          return ye[i3] = { nextPos: ce, result: e5 }, e5;
        })()) === s2 && (e4 = (function() {
          var e5, r4, n4, o3 = 36 * ce + 35, a3 = ye[o3];
          return a3 ? (ce = a3.nextPos, a3.result) : (e5 = ce, 58 === t2.charCodeAt(ce) ? (r4 = ":", ce++) : (r4 = s2, ge(le)), r4 !== s2 && (n4 = be()) !== s2 ? e5 = r4 = { type: "class", name: n4 } : (ce = e5, e5 = s2), ye[o3] = { nextPos: ce, result: e5 }, e5);
        })()), ye[r3] = { nextPos: ce, result: e4 }, e4);
      }
      function De() {
        var e4, r3, n3, o3, a3, i3, u3, l3, c3 = 36 * ce + 15, f3 = ye[c3];
        if (f3) return ce = f3.nextPos, f3.result;
        if (e4 = ce, (r3 = be()) !== s2) {
          for (n3 = [], o3 = ce, 46 === t2.charCodeAt(ce) ? (a3 = ".", ce++) : (a3 = s2, ge(D)), a3 !== s2 && (i3 = be()) !== s2 ? o3 = a3 = [a3, i3] : (ce = o3, o3 = s2); o3 !== s2; ) n3.push(o3), o3 = ce, 46 === t2.charCodeAt(ce) ? (a3 = ".", ce++) : (a3 = s2, ge(D)), a3 !== s2 && (i3 = be()) !== s2 ? o3 = a3 = [a3, i3] : (ce = o3, o3 = s2);
          n3 !== s2 ? (u3 = r3, l3 = n3, e4 = r3 = [].concat.apply([u3], l3).join("")) : (ce = e4, e4 = s2);
        } else ce = e4, e4 = s2;
        return ye[c3] = { nextPos: ce, result: e4 }, e4;
      }
      function Ie() {
        var e4, r3, n3, o3, a3 = 36 * ce + 23, i3 = ye[a3];
        if (i3) return ce = i3.nextPos, i3.result;
        if (e4 = ce, 91 === t2.charCodeAt(ce) ? (r3 = "[", ce++) : (r3 = s2, ge(E)), r3 !== s2) {
          if (n3 = [], J.test(t2.charAt(ce)) ? (o3 = t2.charAt(ce), ce++) : (o3 = s2, ge(Q)), o3 === s2 && (o3 = je()), o3 !== s2) for (; o3 !== s2; ) n3.push(o3), J.test(t2.charAt(ce)) ? (o3 = t2.charAt(ce), ce++) : (o3 = s2, ge(Q)), o3 === s2 && (o3 = je());
          else n3 = s2;
          n3 !== s2 ? (93 === t2.charCodeAt(ce) ? (o3 = "]", ce++) : (o3 = s2, ge(b)), o3 !== s2 ? e4 = r3 = "[" + n3.join("") + "]" : (ce = e4, e4 = s2)) : (ce = e4, e4 = s2);
        } else ce = e4, e4 = s2;
        return ye[a3] = { nextPos: ce, result: e4 }, e4;
      }
      function je() {
        var e4, r3, n3, o3 = 36 * ce + 24, a3 = ye[o3];
        return a3 ? (ce = a3.nextPos, a3.result) : (e4 = ce, 92 === t2.charCodeAt(ce) ? (r3 = "\\", ce++) : (r3 = s2, ge(R)), r3 !== s2 ? (t2.length > ce ? (n3 = t2.charAt(ce), ce++) : (n3 = s2, ge(O)), n3 !== s2 ? e4 = r3 = "\\" + n3 : (ce = e4, e4 = s2)) : (ce = e4, e4 = s2), ye[o3] = { nextPos: ce, result: e4 }, e4);
      }
      function Te() {
        var e4, r3, n3, o3 = 36 * ce + 25, a3 = ye[o3];
        if (a3) return ce = a3.nextPos, a3.result;
        if (r3 = [], X.test(t2.charAt(ce)) ? (n3 = t2.charAt(ce), ce++) : (n3 = s2, ge(Z)), n3 !== s2) for (; n3 !== s2; ) r3.push(n3), X.test(t2.charAt(ce)) ? (n3 = t2.charAt(ce), ce++) : (n3 = s2, ge(Z));
        else r3 = s2;
        return r3 !== s2 && (r3 = r3.join("")), e4 = r3, ye[o3] = { nextPos: ce, result: e4 }, e4;
      }
      function Fe(e4) {
        return { type: "nth-child", index: { type: "literal", value: e4 } };
      }
      function Re(e4) {
        return { type: "nth-last-child", index: { type: "literal", value: e4 } };
      }
      if ((n2 = l2()) !== s2 && ce === t2.length) return n2;
      throw n2 !== s2 && ce < t2.length && ge({ type: "end" }), o2 = he, a2 = pe < t2.length ? t2.charAt(pe) : null, i2 = pe < t2.length ? ve(pe, pe + 1) : ve(pe, pe), new e3(e3.buildMessage(o2, a2), o2, a2, i2);
    } };
  })());
}));
function u(e2, t2) {
  for (var r2 = 0; r2 < t2.length; ++r2) {
    if (null == e2) return e2;
    e2 = e2[t2[r2]];
  }
  return e2;
}
var l = "function" == typeof WeakMap ? /* @__PURE__ */ new WeakMap() : null;
function c(e2) {
  if (null == e2) return function() {
    return true;
  };
  if (null != l) {
    var t2 = l.get(e2);
    return null != t2 || (t2 = f(e2), l.set(e2, t2)), t2;
  }
  return f(e2);
}
function f(e2) {
  switch (e2.type) {
    case "wildcard":
      return function() {
        return true;
      };
    case "identifier":
      var t2 = e2.value.toLowerCase();
      return function(e3, r3, n2) {
        var o3 = n2 && n2.nodeTypeKey || "type";
        return t2 === e3[o3].toLowerCase();
      };
    case "exactNode":
      return function(e3, t3) {
        return 0 === t3.length;
      };
    case "field":
      var r2 = e2.name.split(".");
      return function(e3, t3) {
        return (function e4(t4, r3, n2, o3) {
          for (var a3 = r3, i2 = o3; i2 < n2.length; ++i2) {
            if (null == a3) return false;
            var s3 = a3[n2[i2]];
            if (Array.isArray(s3)) {
              for (var u2 = 0; u2 < s3.length; ++u2) if (e4(t4, s3[u2], n2, i2 + 1)) return true;
              return false;
            }
            a3 = s3;
          }
          return t4 === a3;
        })(e3, t3[r2.length - 1], r2, 0);
      };
    case "matches":
      var o2 = e2.selectors.map(c);
      return function(e3, t3, r3) {
        for (var n2 = 0; n2 < o2.length; ++n2) if (o2[n2](e3, t3, r3)) return true;
        return false;
      };
    case "compound":
      var a2 = e2.selectors.map(c);
      return function(e3, t3, r3) {
        for (var n2 = 0; n2 < a2.length; ++n2) if (!a2[n2](e3, t3, r3)) return false;
        return true;
      };
    case "not":
      var s2 = e2.selectors.map(c);
      return function(e3, t3, r3) {
        for (var n2 = 0; n2 < s2.length; ++n2) if (s2[n2](e3, t3, r3)) return false;
        return true;
      };
    case "has":
      var l2 = e2.selectors.map(c);
      return function(e3, t3, r3) {
        var n2 = false, o3 = [];
        return i.traverse(e3, { enter: function(e4, t4) {
          null != t4 && o3.unshift(t4);
          for (var a3 = 0; a3 < l2.length; ++a3) if (l2[a3](e4, o3, r3)) return n2 = true, void this.break();
        }, leave: function() {
          o3.shift();
        }, keys: r3 && r3.visitorKeys, fallback: r3 && r3.fallback || "iteration" }), n2;
      };
    case "child":
      var f2 = c(e2.left), p2 = c(e2.right);
      return function(e3, t3, r3) {
        return !!(t3.length > 0 && p2(e3, t3, r3)) && f2(t3[0], t3.slice(1), r3);
      };
    case "descendant":
      var h2 = c(e2.left), x2 = c(e2.right);
      return function(e3, t3, r3) {
        if (x2(e3, t3, r3)) {
          for (var n2 = 0, o3 = t3.length; n2 < o3; ++n2) if (h2(t3[n2], t3.slice(n2 + 1), r3)) return true;
        }
        return false;
      };
    case "attribute":
      var v2 = e2.name.split(".");
      switch (e2.operator) {
        case void 0:
          return function(e3) {
            return null != u(e3, v2);
          };
        case "=":
          switch (e2.value.type) {
            case "regexp":
              return function(t3) {
                var r3 = u(t3, v2);
                return "string" == typeof r3 && e2.value.value.test(r3);
              };
            case "literal":
              var g2 = "".concat(e2.value.value);
              return function(e3) {
                return g2 === "".concat(u(e3, v2));
              };
            case "type":
              return function(t3) {
                return e2.value.value === n(u(t3, v2));
              };
          }
          throw new Error("Unknown selector value type: ".concat(e2.value.type));
        case "!=":
          switch (e2.value.type) {
            case "regexp":
              return function(t3) {
                return !e2.value.value.test(u(t3, v2));
              };
            case "literal":
              var A2 = "".concat(e2.value.value);
              return function(e3) {
                return A2 !== "".concat(u(e3, v2));
              };
            case "type":
              return function(t3) {
                return e2.value.value !== n(u(t3, v2));
              };
          }
          throw new Error("Unknown selector value type: ".concat(e2.value.type));
        case "<=":
          return function(t3) {
            return u(t3, v2) <= e2.value.value;
          };
        case "<":
          return function(t3) {
            return u(t3, v2) < e2.value.value;
          };
        case ">":
          return function(t3) {
            return u(t3, v2) > e2.value.value;
          };
        case ">=":
          return function(t3) {
            return u(t3, v2) >= e2.value.value;
          };
      }
      throw new Error("Unknown operator: ".concat(e2.operator));
    case "sibling":
      var E = c(e2.left), b = c(e2.right);
      return function(t3, r3, n2) {
        return b(t3, r3, n2) && y(t3, E, r3, "LEFT_SIDE", n2) || e2.left.subject && E(t3, r3, n2) && y(t3, b, r3, "RIGHT_SIDE", n2);
      };
    case "adjacent":
      var S = c(e2.left), _ = c(e2.right);
      return function(t3, r3, n2) {
        return _(t3, r3, n2) && d(t3, S, r3, "LEFT_SIDE", n2) || e2.right.subject && S(t3, r3, n2) && d(t3, _, r3, "RIGHT_SIDE", n2);
      };
    case "nth-child":
      var C = e2.index.value, P = c(e2.right);
      return function(e3, t3, r3) {
        return P(e3, t3, r3) && m(e3, t3, C, r3);
      };
    case "nth-last-child":
      var w = -e2.index.value, k = c(e2.right);
      return function(e3, t3, r3) {
        return k(e3, t3, r3) && m(e3, t3, w, r3);
      };
    case "class":
      var D = e2.name.toLowerCase();
      return function(t3, r3, n2) {
        if (n2 && n2.matchClass) return n2.matchClass(e2.name, t3, r3);
        if (n2 && n2.nodeTypeKey) return false;
        switch (D) {
          case "statement":
            if ("Statement" === t3.type.slice(-9)) return true;
          case "declaration":
            return "Declaration" === t3.type.slice(-11);
          case "pattern":
            if ("Pattern" === t3.type.slice(-7)) return true;
          case "expression":
            return "Expression" === t3.type.slice(-10) || "Literal" === t3.type.slice(-7) || "Identifier" === t3.type && (0 === r3.length || "MetaProperty" !== r3[0].type) || "MetaProperty" === t3.type;
          case "function":
            return "FunctionDeclaration" === t3.type || "FunctionExpression" === t3.type || "ArrowFunctionExpression" === t3.type;
        }
        throw new Error("Unknown class name: ".concat(e2.name));
      };
  }
  throw new Error("Unknown selector type: ".concat(e2.type));
}
function p(e2, t2) {
  var r2 = t2 && t2.nodeTypeKey || "type", n2 = e2[r2];
  return t2 && t2.visitorKeys && t2.visitorKeys[n2] ? t2.visitorKeys[n2] : i.VisitorKeys[n2] ? i.VisitorKeys[n2] : t2 && "function" == typeof t2.fallback ? t2.fallback(e2) : Object.keys(e2).filter((function(e3) {
    return e3 !== r2;
  }));
}
function h(e2, t2) {
  var r2 = t2 && t2.nodeTypeKey || "type";
  return null !== e2 && "object" === n(e2) && "string" == typeof e2[r2];
}
function y(e2, r2, n2, o2, a2) {
  var i2 = t(n2, 1)[0];
  if (!i2) return false;
  for (var s2 = p(i2, a2), u2 = 0; u2 < s2.length; ++u2) {
    var l2 = i2[s2[u2]];
    if (Array.isArray(l2)) {
      var c2 = l2.indexOf(e2);
      if (c2 < 0) continue;
      var f2 = void 0, y2 = void 0;
      "LEFT_SIDE" === o2 ? (f2 = 0, y2 = c2) : (f2 = c2 + 1, y2 = l2.length);
      for (var d2 = f2; d2 < y2; ++d2) if (h(l2[d2], a2) && r2(l2[d2], n2, a2)) return true;
    }
  }
  return false;
}
function d(e2, r2, n2, o2, a2) {
  var i2 = t(n2, 1)[0];
  if (!i2) return false;
  for (var s2 = p(i2, a2), u2 = 0; u2 < s2.length; ++u2) {
    var l2 = i2[s2[u2]];
    if (Array.isArray(l2)) {
      var c2 = l2.indexOf(e2);
      if (c2 < 0) continue;
      if ("LEFT_SIDE" === o2 && c2 > 0 && h(l2[c2 - 1], a2) && r2(l2[c2 - 1], n2, a2)) return true;
      if ("RIGHT_SIDE" === o2 && c2 < l2.length - 1 && h(l2[c2 + 1], a2) && r2(l2[c2 + 1], n2, a2)) return true;
    }
  }
  return false;
}
function m(e2, r2, n2, o2) {
  if (0 === n2) return false;
  var a2 = t(r2, 1)[0];
  if (!a2) return false;
  for (var i2 = p(a2, o2), s2 = 0; s2 < i2.length; ++s2) {
    var u2 = a2[i2[s2]];
    if (Array.isArray(u2)) {
      var l2 = n2 < 0 ? u2.length + n2 : n2 - 1;
      if (l2 >= 0 && l2 < u2.length && u2[l2] === e2) return true;
    }
  }
  return false;
}
function x(e2, t2, o2, a2) {
  if (t2) {
    var s2 = [], u2 = c(t2), l2 = (function e3(t3, o3) {
      if (null == t3 || "object" != n(t3)) return [];
      null == o3 && (o3 = t3);
      for (var a3 = t3.subject ? [o3] : [], i2 = Object.keys(t3), s3 = 0; s3 < i2.length; ++s3) {
        var u3 = i2[s3], l3 = t3[u3];
        a3.push.apply(a3, r(e3(l3, "left" === u3 ? l3 : o3)));
      }
      return a3;
    })(t2).map(c);
    i.traverse(e2, { enter: function(e3, t3) {
      if (null != t3 && s2.unshift(t3), u2(e3, s2, a2)) if (l2.length) for (var r2 = 0, n2 = l2.length; r2 < n2; ++r2) {
        l2[r2](e3, s2, a2) && o2(e3, t3, s2);
        for (var i2 = 0, c2 = s2.length; i2 < c2; ++i2) {
          var f2 = s2.slice(i2 + 1);
          l2[r2](s2[i2], f2, a2) && o2(s2[i2], t3, f2);
        }
      }
      else o2(e3, t3, s2);
    }, leave: function() {
      s2.shift();
    }, keys: a2 && a2.visitorKeys, fallback: a2 && a2.fallback || "iteration" });
  }
}
function v(e2, t2, r2) {
  var n2 = [];
  return x(e2, t2, (function(e3) {
    n2.push(e3);
  }), r2), n2;
}
function g(e2) {
  return s.parse(e2);
}
function A(e2, t2, r2) {
  return v(e2, g(t2), r2);
}
A.parse = g, A.match = v, A.traverse = x, A.matches = function(e2, t2, r2, n2) {
  return !t2 || !!e2 && (r2 || (r2 = []), c(t2)(e2, r2, n2));
}, A.query = A;

exports.default = A;
//# sourceMappingURL=esquery.esm.min.js.map
