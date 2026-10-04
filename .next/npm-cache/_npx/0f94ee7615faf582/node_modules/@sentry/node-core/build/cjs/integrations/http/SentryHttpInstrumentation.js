Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const api = require('@opentelemetry/api');
const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const constants = require('./constants.js');
const nodeVersion = require('../../nodeVersion.js');
const node_events = require('node:events');
const http = require('node:http');

const FULLY_SUPPORTS_HTTP_DIAGNOSTICS_CHANNEL = nodeVersion.NODE_VERSION.major === 22 && nodeVersion.NODE_VERSION.minor >= 12 || nodeVersion.NODE_VERSION.major === 23 && nodeVersion.NODE_VERSION.minor >= 2 || nodeVersion.NODE_VERSION.major >= 24;
function instrumentHttpOutgoingRequests(instrumentationOptions = {}) {
  const { outgoingRequestApplyCustomAttributes: applyCustomAttributesOnSpan, ...options } = instrumentationOptions;
  const patchOptions = {
    propagateTrace: options.propagateTraceInOutgoingRequests ?? true,
    applyCustomAttributesOnSpan,
    ...options,
    // oxlint-disable-next-line typescript/no-deprecated
    spans: options.createSpansForOutgoingRequests !== false && (options.spans ?? true),
    ignoreOutgoingRequests(url, request) {
      return core.isTracingSuppressed() || !!options.ignoreOutgoingRequests?.(url, core.getRequestOptions(request));
    },
    outgoingRequestHook(span, request) {
      options.outgoingRequestHook?.(span, request);
      const originalOnce = request.once;
      const newOnce = new Proxy(originalOnce, {
        apply(target, thisArg, args) {
          const [event] = args;
          if (event !== "response") {
            return target.apply(thisArg, args);
          }
          const parentContext = api.context.active();
          const requestContext = api.trace.setSpan(parentContext, span);
          return api.context.with(requestContext, () => {
            return target.apply(thisArg, args);
          });
        }
      });
      request.once = newOnce;
    },
    outgoingResponseHook(span, response) {
      options.outgoingResponseHook?.(span, response);
    },
    errorMonitor: node_events.errorMonitor,
    // Pass these in to detect OTel double-wrapping if we're enabling spans
    http
  };
  if (FULLY_SUPPORTS_HTTP_DIAGNOSTICS_CHANNEL) {
    instrumentHttpOutgoingRequestsViaChannel(patchOptions);
  } else {
    instrumentHttpOutgoingRequestsViaMonkeyPatching(patchOptions);
  }
}
let _currentListener;
function instrumentHttpOutgoingRequestsViaChannel(options) {
  const { [core.HTTP_ON_CLIENT_REQUEST]: onHttpClientRequestCreated } = core.getHttpClientSubscriptions(options);
  if (_currentListener) {
    diagnosticsChannel.unsubscribe(core.HTTP_ON_CLIENT_REQUEST, _currentListener);
  }
  diagnosticsChannel.subscribe(core.HTTP_ON_CLIENT_REQUEST, onHttpClientRequestCreated);
  _currentListener = onHttpClientRequestCreated;
}
function instrumentHttpOutgoingRequestsViaMonkeyPatching(options) {
  core.patchHttpModuleClient(http, options);
}
class SentryHttpInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(constants.INSTRUMENTATION_NAME, core.SDK_VERSION, config);
  }
  /** @inheritdoc */
  init() {
    instrumentHttpOutgoingRequests(this.getConfig());
  }
}

exports.SentryHttpInstrumentation = SentryHttpInstrumentation;
exports.instrumentHttpOutgoingRequests = instrumentHttpOutgoingRequests;
//# sourceMappingURL=SentryHttpInstrumentation.js.map
