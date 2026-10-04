import { m as matcher } from './matcher.js';

const { InstrumentationMatcher } = matcher;
function create(configs, dcModule) {
  return new InstrumentationMatcher(configs, dcModule);
}
var lib = { create };

export { lib as l };
//# sourceMappingURL=index.js.map
