Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const currentScopes = require('../../currentScopes.js');
const filterQueryParams = require('./filterQueryParams.js');
const filterUrlQuery = require('./filterUrlQuery.js');

function urlQueryParamsBehavior(client) {
  return (client ?? currentScopes.getClient())?.getDataCollectionOptions().urlQueryParams ?? true;
}
function filterCollectedUrl(url, client) {
  return url === void 0 ? void 0 : filterUrlQuery.filterUrlQuery(url, urlQueryParamsBehavior(client));
}
function filterCollectedUrlQuery(query, client) {
  if (!query) {
    return query;
  }
  const hasPrefix = query.startsWith("?");
  const filtered = filterQueryParams.filterQueryParams(hasPrefix ? query.slice(1) : query, urlQueryParamsBehavior(client));
  return filtered && hasPrefix ? `?${filtered}` : filtered;
}

exports.filterCollectedUrl = filterCollectedUrl;
exports.filterCollectedUrlQuery = filterCollectedUrlQuery;
//# sourceMappingURL=filterCollectedUrl.js.map
