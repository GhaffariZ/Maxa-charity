Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const ORIGIN = "auto.graphql.diagnostic_channel";
const SPAN_NAME_PARSE = "graphql.parse";
const SPAN_NAME_VALIDATE = "graphql.validate";
const SPAN_NAME_EXECUTE = "graphql.execute";
const SPAN_NAME_RESOLVE = "graphql.resolve";
const GRAPHQL_FIELD_NAME = "graphql.field.name";
const GRAPHQL_FIELD_PATH = "graphql.field.path";
const GRAPHQL_FIELD_TYPE = "graphql.field.type";
const GRAPHQL_PARENT_NAME = "graphql.parent.name";
const GRAPHQL_DATA_SYMBOL = /* @__PURE__ */ Symbol.for("opentelemetry.graphql_data");
const GRAPHQL_PATCHED_SYMBOL = /* @__PURE__ */ Symbol.for("opentelemetry.patched");

exports.GRAPHQL_DATA_SYMBOL = GRAPHQL_DATA_SYMBOL;
exports.GRAPHQL_FIELD_NAME = GRAPHQL_FIELD_NAME;
exports.GRAPHQL_FIELD_PATH = GRAPHQL_FIELD_PATH;
exports.GRAPHQL_FIELD_TYPE = GRAPHQL_FIELD_TYPE;
exports.GRAPHQL_PARENT_NAME = GRAPHQL_PARENT_NAME;
exports.GRAPHQL_PATCHED_SYMBOL = GRAPHQL_PATCHED_SYMBOL;
exports.ORIGIN = ORIGIN;
exports.SPAN_NAME_EXECUTE = SPAN_NAME_EXECUTE;
exports.SPAN_NAME_PARSE = SPAN_NAME_PARSE;
exports.SPAN_NAME_RESOLVE = SPAN_NAME_RESOLVE;
exports.SPAN_NAME_VALIDATE = SPAN_NAME_VALIDATE;
//# sourceMappingURL=constants.js.map
