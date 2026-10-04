import { getClient } from '../../currentScopes.js';
import { filterQueryParams } from './filterQueryParams.js';
import { filterUrlQuery } from './filterUrlQuery.js';

function urlQueryParamsBehavior(client) {
  return (client ?? getClient())?.getDataCollectionOptions().urlQueryParams ?? true;
}
function filterCollectedUrl(url, client) {
  return url === void 0 ? void 0 : filterUrlQuery(url, urlQueryParamsBehavior(client));
}
function filterCollectedUrlQuery(query, client) {
  if (!query) {
    return query;
  }
  const hasPrefix = query.startsWith("?");
  const filtered = filterQueryParams(hasPrefix ? query.slice(1) : query, urlQueryParamsBehavior(client));
  return filtered && hasPrefix ? `?${filtered}` : filtered;
}

export { filterCollectedUrl, filterCollectedUrlQuery };
//# sourceMappingURL=filterCollectedUrl.js.map
