Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const createMissingInstrumentationContext = (pkg) => {
  let isCjs;
  isCjs = true;
  return {
    package: pkg,
    "javascript.is_cjs": isCjs
  };
};

exports.createMissingInstrumentationContext = createMissingInstrumentationContext;
//# sourceMappingURL=createMissingInstrumentationContext.js.map
