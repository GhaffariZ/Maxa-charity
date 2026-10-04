import { spanToJSON, SEMANTIC_ATTRIBUTE_CACHE_ITEM_SIZE, SEMANTIC_ATTRIBUTE_CACHE_HIT, SEMANTIC_ATTRIBUTE_CACHE_KEY, SEMANTIC_ATTRIBUTE_SENTRY_OP, truncate } from '@sentry/core';
import { getCacheKeySafely, getCacheOperation, shouldConsiderForCache, isInCommands, calculateCacheItemSize, REMOVE_COMMANDS, GET_COMMANDS } from '../../../utils/redisCache.js';

let _redisOptions = {};
function setRedisOptions(options) {
  _redisOptions = options;
}
const cacheResponseHook = (span, redisCommand, cmdArgs, response) => {
  const safeKey = getCacheKeySafely(redisCommand, cmdArgs);
  const cacheOperation = getCacheOperation(redisCommand);
  if (!safeKey || !cacheOperation || !_redisOptions.cachePrefixes || !shouldConsiderForCache(redisCommand, safeKey, _redisOptions.cachePrefixes)) {
    return;
  }
  const spanData = spanToJSON(span).data;
  const networkPeerAddress = spanData["net.peer.name"] ?? spanData["server.address"];
  const networkPeerPort = spanData["net.peer.port"] ?? spanData["server.port"];
  if (networkPeerPort && networkPeerAddress) {
    span.setAttributes({ "network.peer.address": networkPeerAddress, "network.peer.port": networkPeerPort });
  }
  const cacheItemSize = isInCommands(REMOVE_COMMANDS, redisCommand) ? void 0 : calculateCacheItemSize(response);
  if (cacheItemSize) {
    span.setAttribute(SEMANTIC_ATTRIBUTE_CACHE_ITEM_SIZE, cacheItemSize);
  }
  if (isInCommands(GET_COMMANDS, redisCommand) && cacheItemSize !== void 0) {
    span.setAttribute(SEMANTIC_ATTRIBUTE_CACHE_HIT, cacheItemSize > 0);
  }
  span.setAttributes({
    [SEMANTIC_ATTRIBUTE_SENTRY_OP]: cacheOperation,
    [SEMANTIC_ATTRIBUTE_CACHE_KEY]: safeKey
  });
  const spanDescription = safeKey.join(", ");
  span.updateName(
    _redisOptions.maxCacheKeyLength ? truncate(spanDescription, _redisOptions.maxCacheKeyLength) : spanDescription
  );
};

export { _redisOptions, cacheResponseHook, setRedisOptions };
//# sourceMappingURL=cache.js.map
