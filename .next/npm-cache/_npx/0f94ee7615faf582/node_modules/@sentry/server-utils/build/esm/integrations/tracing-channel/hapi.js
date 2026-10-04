import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, debug } from '@sentry/core';
import { DEBUG_BUILD } from '../../debug-build.js';
import { CHANNELS } from '../../orchestrion/channels.js';
import { wrapRouteArguments, wrapExtArguments } from './hapi-utils.js';

const INTEGRATION_NAME = "Hapi";
const _hapiChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log(`[orchestrion:hapi] subscribing to channels "${CHANNELS.HAPI_ROUTE}" / "${CHANNELS.HAPI_EXT}"`);
      diagnosticsChannel.tracingChannel(CHANNELS.HAPI_ROUTE).subscribe({
        start(rawCtx) {
          const ctx = rawCtx;
          wrapRouteArguments(ctx.arguments, ctx.self?.realm?.plugin);
        },
        end() {
        },
        asyncStart() {
        },
        asyncEnd() {
        },
        error() {
        }
      });
      diagnosticsChannel.tracingChannel(CHANNELS.HAPI_EXT).subscribe({
        start(rawCtx) {
          const ctx = rawCtx;
          wrapExtArguments(ctx.arguments, ctx.self?.realm?.plugin);
        },
        end() {
        },
        asyncStart() {
        },
        asyncEnd() {
        },
        error() {
        }
      });
    }
  };
});
const hapiChannelIntegration = defineIntegration(_hapiChannelIntegration);

export { hapiChannelIntegration };
//# sourceMappingURL=hapi.js.map
