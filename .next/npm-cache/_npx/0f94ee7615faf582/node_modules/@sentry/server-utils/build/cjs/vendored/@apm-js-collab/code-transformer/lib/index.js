Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const matcher = require('./matcher.js');

const { InstrumentationMatcher } = matcher.matcher;
function create(configs, dcModule) {
  return new InstrumentationMatcher(configs, dcModule);
}
var lib = { create };

exports.lib = lib;
//# sourceMappingURL=index.js.map
