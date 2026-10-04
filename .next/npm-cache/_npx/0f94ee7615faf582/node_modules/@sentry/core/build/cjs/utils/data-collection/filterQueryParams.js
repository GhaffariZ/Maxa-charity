Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const filteringSnippets = require('./filtering-snippets.js');
const filterKeyValueData = require('./filterKeyValueData.js');

function filterQueryParams(queryString, behavior) {
  if (!queryString || behavior === false) {
    return void 0;
  }
  return queryString.split("&").map((pair) => {
    const separatorIndex = pair.indexOf("=");
    const encodedKey = separatorIndex === -1 ? pair : pair.slice(0, separatorIndex);
    const key = new URLSearchParams(`${encodedKey}=`).keys().next().value;
    return key !== void 0 && filterKeyValueData.shouldFilterDataKey(key, behavior) ? `${encodedKey}=${filteringSnippets.FILTERED_VALUE}` : pair;
  }).join("&");
}

exports.filterQueryParams = filterQueryParams;
//# sourceMappingURL=filterQueryParams.js.map
