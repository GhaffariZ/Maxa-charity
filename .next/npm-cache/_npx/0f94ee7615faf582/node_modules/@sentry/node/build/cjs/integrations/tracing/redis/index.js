Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const dc = require('node:diagnostics_channel');
const serverUtils = require('@sentry/server-utils');
const nodeCore = require('@sentry/node-core');
const diagnosticsChannelInjection = require('../../../sdk/diagnosticsChannelInjection.js');
const cache = require('./cache.js');
const ioredisInstrumentation = require('./vendored/ioredis-instrumentation.js');
const redisInstrumentation = require('./vendored/redis-instrumentation.js');

const INTEGRATION_NAME = "Redis";
const instrumentIORedis = nodeCore.generateInstrumentOnce(`${INTEGRATION_NAME}.IORedis`, () => {
  return new ioredisInstrumentation.IORedisInstrumentation({
    responseHook: cache.cacheResponseHook
  });
});
const instrumentRedisModule = nodeCore.generateInstrumentOnce(`${INTEGRATION_NAME}.Redis`, () => {
  return new redisInstrumentation.RedisInstrumentation({
    responseHook: cache.cacheResponseHook
  });
});
const instrumentRedis = Object.assign(
  () => {
    if (!diagnosticsChannelInjection.isDiagnosticsChannelInjectionEnabled() || !dc.tracingChannel) {
      instrumentIORedis();
      instrumentRedisModule();
    }
  },
  { id: INTEGRATION_NAME }
);
const _redisIntegration = ((options = {}) => {
  return core.extendIntegration(serverUtils.redisIntegration({ responseHook: cache.cacheResponseHook }), {
    name: INTEGRATION_NAME,
    setupOnce() {
      cache.setRedisOptions(options);
      instrumentRedis();
    }
  });
});
const redisIntegration = core.defineIntegration(_redisIntegration);

Object.defineProperty(exports, "_redisOptions", {
  enumerable: true,
  get: () => cache._redisOptions
});
exports.cacheResponseHook = cache.cacheResponseHook;
exports.instrumentRedis = instrumentRedis;
exports.redisIntegration = redisIntegration;
//# sourceMappingURL=index.js.map
