Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const redisCache = require('../../../utils/redisCache.js');

exports._redisOptions = {};
function setRedisOptions(options) {
  exports._redisOptions = options;
}
const cacheResponseHook = (span, redisCommand, cmdArgs, response) => {
  const safeKey = redisCache.getCacheKeySafely(redisCommand, cmdArgs);
  const cacheOperation = redisCache.getCacheOperation(redisCommand);
  if (!safeKey || !cacheOperation || !exports._redisOptions.cachePrefixes || !redisCache.shouldConsiderForCache(redisCommand, safeKey, exports._redisOptions.cachePrefixes)) {
    return;
  }
  const spanData = core.spanToJSON(span).data;
  const networkPeerAddress = spanData["net.peer.name"] ?? spanData["server.address"];
  const networkPeerPort = spanData["net.peer.port"] ?? spanData["server.port"];
  if (networkPeerPort && networkPeerAddress) {
    span.setAttributes({ "network.peer.address": networkPeerAddress, "network.peer.port": networkPeerPort });
  }
  const cacheItemSize = redisCache.isInCommands(redisCache.REMOVE_COMMANDS, redisCommand) ? void 0 : redisCache.calculateCacheItemSize(response);
  if (cacheItemSize) {
    span.setAttribute(core.SEMANTIC_ATTRIBUTE_CACHE_ITEM_SIZE, cacheItemSize);
  }
  if (redisCache.isInCommands(redisCache.GET_COMMANDS, redisCommand) && cacheItemSize !== void 0) {
    span.setAttribute(core.SEMANTIC_ATTRIBUTE_CACHE_HIT, cacheItemSize > 0);
  }
  span.setAttributes({
    [core.SEMANTIC_ATTRIBUTE_SENTRY_OP]: cacheOperation,
    [core.SEMANTIC_ATTRIBUTE_CACHE_KEY]: safeKey
  });
  const spanDescription = safeKey.join(", ");
  span.updateName(
    exports._redisOptions.maxCacheKeyLength ? core.truncate(spanDescription, exports._redisOptions.maxCacheKeyLength) : spanDescription
  );
};

exports.cacheResponseHook = cacheResponseHook;
exports.setRedisOptions = setRedisOptions;
//# sourceMappingURL=cache.js.map
