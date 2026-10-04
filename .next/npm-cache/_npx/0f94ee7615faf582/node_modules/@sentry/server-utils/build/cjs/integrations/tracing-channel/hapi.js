Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const debugBuild = require('../../debug-build.js');
const channels = require('../../orchestrion/channels.js');
const hapiUtils = require('./hapi-utils.js');

function _interopNamespaceDefault(e) {
  const n = Object.create(null, { [Symbol.toStringTag]: { value: 'Module' } });
  if (e) {
    for (const k in e) {
      n[k] = e[k];
    }
  }
  n.default = e;
  return n;
}

const diagnosticsChannel__namespace = /*#__PURE__*/_interopNamespaceDefault(diagnosticsChannel);

const INTEGRATION_NAME = "Hapi";
const _hapiChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:hapi] subscribing to channels "${channels.CHANNELS.HAPI_ROUTE}" / "${channels.CHANNELS.HAPI_EXT}"`);
      diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.HAPI_ROUTE).subscribe({
        start(rawCtx) {
          const ctx = rawCtx;
          hapiUtils.wrapRouteArguments(ctx.arguments, ctx.self?.realm?.plugin);
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
      diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.HAPI_EXT).subscribe({
        start(rawCtx) {
          const ctx = rawCtx;
          hapiUtils.wrapExtArguments(ctx.arguments, ctx.self?.realm?.plugin);
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
const hapiChannelIntegration = core.defineIntegration(_hapiChannelIntegration);

exports.hapiChannelIntegration = hapiChannelIntegration;
//# sourceMappingURL=hapi.js.map
