import { defineIntegration, extendIntegration } from '@sentry/core';
import * as dc from 'node:diagnostics_channel';
import { redisIntegration as redisIntegration$1 } from '@sentry/server-utils';
import { generateInstrumentOnce } from '@sentry/node-core';
import { isDiagnosticsChannelInjectionEnabled } from '../../../sdk/diagnosticsChannelInjection.js';
import { cacheResponseHook, setRedisOptions } from './cache.js';
export { _redisOptions } from './cache.js';
import { IORedisInstrumentation } from './vendored/ioredis-instrumentation.js';
import { RedisInstrumentation } from './vendored/redis-instrumentation.js';

const INTEGRATION_NAME = "Redis";
const instrumentIORedis = generateInstrumentOnce(`${INTEGRATION_NAME}.IORedis`, () => {
  return new IORedisInstrumentation({
    responseHook: cacheResponseHook
  });
});
const instrumentRedisModule = generateInstrumentOnce(`${INTEGRATION_NAME}.Redis`, () => {
  return new RedisInstrumentation({
    responseHook: cacheResponseHook
  });
});
const instrumentRedis = Object.assign(
  () => {
    if (!isDiagnosticsChannelInjectionEnabled() || !dc.tracingChannel) {
      instrumentIORedis();
      instrumentRedisModule();
    }
  },
  { id: INTEGRATION_NAME }
);
const _redisIntegration = ((options = {}) => {
  return extendIntegration(redisIntegration$1({ responseHook: cacheResponseHook }), {
    name: INTEGRATION_NAME,
    setupOnce() {
      setRedisOptions(options);
      instrumentRedis();
    }
  });
});
const redisIntegration = defineIntegration(_redisIntegration);

export { cacheResponseHook, instrumentRedis, redisIntegration };
//# sourceMappingURL=index.js.map
