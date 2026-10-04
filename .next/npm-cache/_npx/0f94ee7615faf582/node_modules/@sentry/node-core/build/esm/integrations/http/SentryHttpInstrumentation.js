import { subscribe, unsubscribe } from 'node:diagnostics_channel';
import { context, trace } from '@opentelemetry/api';
import { InstrumentationBase } from '@opentelemetry/instrumentation';
import { getHttpClientSubscriptions, HTTP_ON_CLIENT_REQUEST, patchHttpModuleClient, SDK_VERSION, isTracingSuppressed, getRequestOptions } from '@sentry/core';
import { INSTRUMENTATION_NAME } from './constants.js';
import { NODE_VERSION } from '../../nodeVersion.js';
import { errorMonitor } from 'node:events';
import * as http from 'node:http';

const FULLY_SUPPORTS_HTTP_DIAGNOSTICS_CHANNEL = NODE_VERSION.major === 22 && NODE_VERSION.minor >= 12 || NODE_VERSION.major === 23 && NODE_VERSION.minor >= 2 || NODE_VERSION.major >= 24;
function instrumentHttpOutgoingRequests(instrumentationOptions = {}) {
  const { outgoingRequestApplyCustomAttributes: applyCustomAttributesOnSpan, ...options } = instrumentationOptions;
  const patchOptions = {
    propagateTrace: options.propagateTraceInOutgoingRequests ?? true,
    applyCustomAttributesOnSpan,
    ...options,
    // oxlint-disable-next-line typescript/no-deprecated
    spans: options.createSpansForOutgoingRequests !== false && (options.spans ?? true),
    ignoreOutgoingRequests(url, request) {
      return isTracingSuppressed() || !!options.ignoreOutgoingRequests?.(url, getRequestOptions(request));
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
          const parentContext = context.active();
          const requestContext = trace.setSpan(parentContext, span);
          return context.with(requestContext, () => {
            return target.apply(thisArg, args);
          });
        }
      });
      request.once = newOnce;
    },
    outgoingResponseHook(span, response) {
      options.outgoingResponseHook?.(span, response);
    },
    errorMonitor,
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
  const { [HTTP_ON_CLIENT_REQUEST]: onHttpClientRequestCreated } = getHttpClientSubscriptions(options);
  if (_currentListener) {
    unsubscribe(HTTP_ON_CLIENT_REQUEST, _currentListener);
  }
  subscribe(HTTP_ON_CLIENT_REQUEST, onHttpClientRequestCreated);
  _currentListener = onHttpClientRequestCreated;
}
function instrumentHttpOutgoingRequestsViaMonkeyPatching(options) {
  patchHttpModuleClient(http, options);
}
class SentryHttpInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(INSTRUMENTATION_NAME, SDK_VERSION, config);
  }
  /** @inheritdoc */
  init() {
    instrumentHttpOutgoingRequests(this.getConfig());
  }
}

export { SentryHttpInstrumentation, instrumentHttpOutgoingRequests };
//# sourceMappingURL=SentryHttpInstrumentation.js.map
