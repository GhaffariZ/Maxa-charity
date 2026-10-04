Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const filterQueryParams = require('./filterQueryParams.js');

function filterUrlQuery(url, behavior) {
  const fragmentStart = url.indexOf("#");
  const queryEnd = fragmentStart === -1 ? url.length : fragmentStart;
  const queryStart = url.indexOf("?");
  if (queryStart === -1 || queryStart > queryEnd) {
    return url;
  }
  const prefix = url.slice(0, queryStart);
  const query = url.slice(queryStart + 1, queryEnd);
  const suffix = url.slice(queryEnd);
  const filtered = filterQueryParams.filterQueryParams(query, behavior);
  return filtered ? `${prefix}?${filtered}${suffix}` : `${prefix}${suffix}`;
}

exports.filterUrlQuery = filterUrlQuery;
//# sourceMappingURL=filterUrlQuery.js.map
