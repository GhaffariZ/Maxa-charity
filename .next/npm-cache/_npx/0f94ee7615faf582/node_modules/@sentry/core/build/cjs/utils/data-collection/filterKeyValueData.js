Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const filteringSnippets = require('./filtering-snippets.js');

function isSensitiveKey(lower, denySnippets) {
  return denySnippets.some((snippet) => lower.includes(snippet));
}
function shouldFilterDataKey(key, behavior, additionalDenyTerms) {
  if (behavior === false) {
    return true;
  }
  const lowerKey = key.toLowerCase();
  const denySnippets = additionalDenyTerms != null ? [...filteringSnippets.SENSITIVE_KEY_SNIPPETS, ...additionalDenyTerms] : filteringSnippets.SENSITIVE_KEY_SNIPPETS;
  if (isSensitiveKey(lowerKey, denySnippets)) {
    return true;
  }
  if (behavior === true) {
    return false;
  }
  const terms = "deny" in behavior ? behavior.deny : behavior.allow;
  const matchesConfiguredTerm = terms.some((term) => lowerKey.includes(term.toLowerCase()));
  return "deny" in behavior ? matchesConfiguredTerm : !matchesConfiguredTerm;
}
function filterKeyValueData(data, behavior, additionalDenyTerms) {
  if (behavior === false) {
    return {};
  }
  const result = {};
  for (const key of Object.keys(data)) {
    result[key] = shouldFilterDataKey(key, behavior, additionalDenyTerms) ? filteringSnippets.FILTERED_VALUE : data[key];
  }
  return result;
}

exports.filterKeyValueData = filterKeyValueData;
exports.shouldFilterDataKey = shouldFilterDataKey;
//# sourceMappingURL=filterKeyValueData.js.map
