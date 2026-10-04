Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const debugBuild = require('../../../debug-build.js');
const channels = require('../../../orchestrion/channels.js');
const tracingChannel = require('../../../tracing-channel.js');
const constants = require('./constants.js');
const ServicesExtensions = require('./services/ServicesExtensions.js');
const utils = require('./utils.js');

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

const INTEGRATION_NAME = "Aws";
function safe(fn) {
  try {
    return fn();
  } catch (error) {
    debugBuild.DEBUG_BUILD && core.debug.warn("[orchestrion:aws-sdk] error building span", error);
    return void 0;
  }
}
function setMetadataAttributes(span, metadata) {
  if (!metadata) {
    return;
  }
  if (metadata.requestId) {
    span.setAttribute(attributes._AWS_REQUEST_ID, metadata.requestId);
  }
  if (metadata.httpStatusCode) {
    span.setAttribute(attributes.HTTP_STATUS_CODE, metadata.httpStatusCode);
  }
  if (metadata.extendedRequestId) {
    span.setAttribute(attributes.AWS_REQUEST_EXTENDED_ID, metadata.extendedRequestId);
  }
}
const _awsChannelIntegration = (() => {
  const servicesExtensions = new ServicesExtensions.ServicesExtensions();
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      const getSpan = (data) => safe(() => {
        const command = data.arguments[0];
        const commandName = command?.constructor?.name;
        if (!command || !commandName) {
          return void 0;
        }
        const clientConfig = data.self?.config;
        const serviceName = clientConfig?.serviceId ?? // `clientName` isn't available at the `send` boundary; fall back to the client's
        // constructor name (e.g. `S3Client` -> `S3`). `serviceId` is set for all AWS clients.
        utils.removeSuffixFromStringIfExists(data.self?.constructor?.name || "AWS", "Client");
        if (!command.input) {
          command.input = {};
        }
        const normalizedRequest = utils.normalizeV3Request(serviceName, commandName, command.input, void 0);
        const requestMetadata = servicesExtensions.requestPreSpanHook(normalizedRequest);
        const span = core.startInactiveSpan({
          name: requestMetadata.spanName ?? `${normalizedRequest.serviceName}.${normalizedRequest.commandName}`,
          kind: requestMetadata.spanKind ?? core.SPAN_KIND.CLIENT,
          // `rpc` matches what the exporter infers from `rpc.service` for the OTel aws-sdk spans;
          // service extensions override it where inference yields a different op (DynamoDB: `db`).
          op: requestMetadata.spanOp || "rpc",
          attributes: {
            [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: constants.AWS_SDK_ORIGIN,
            ...utils.extractAttributesFromNormalizedRequest(normalizedRequest),
            ...requestMetadata.spanAttributes
          }
        });
        data._sentryNormalizedRequest = normalizedRequest;
        data._sentryRequestMetadata = requestMetadata;
        let regionResult;
        try {
          regionResult = clientConfig?.region?.();
        } catch {
        }
        const regionHolder = {
          settled: false,
          promise: Promise.resolve(regionResult).then((region) => {
            if (region) {
              normalizedRequest.region = region;
              span.setAttribute(attributes.CLOUD_REGION, region);
            }
          }).catch(() => {
          }).finally(() => {
            regionHolder.settled = true;
          })
        };
        data._sentryRegion = regionHolder;
        safe(() => servicesExtensions.requestPostSpanHook(normalizedRequest, span));
        return span;
      });
      const opts = {
        deferSpanEnd({ span, data, end }) {
          const normalizedRequest = data._sentryNormalizedRequest;
          const requestMetadata = data._sentryRequestMetadata;
          if (!normalizedRequest) {
            return false;
          }
          const failed = "error" in data;
          safe(() => {
            if (failed) {
              const err = data.error;
              const errMetadata = err?.$metadata;
              setMetadataAttributes(span, {
                requestId: err?.RequestId ?? errMetadata?.requestId,
                httpStatusCode: errMetadata?.httpStatusCode,
                extendedRequestId: err?.extendedRequestId ?? errMetadata?.extendedRequestId
              });
              return;
            }
            const output = data.result;
            setMetadataAttributes(span, output?.$metadata);
            const normalizedResponse = {
              data: output,
              request: normalizedRequest,
              requestId: output?.$metadata?.requestId
            };
            servicesExtensions.responseHook(normalizedResponse, span);
          });
          if (requestMetadata?.isStream && !failed) {
            return true;
          }
          const region = data._sentryRegion;
          if (region && !region.settled) {
            void region.promise.then(() => end());
            return true;
          }
          return false;
        }
      };
      const awsSendChannels = [
        channels.CHANNELS.AWS_SMITHY_CORE_SEND,
        channels.CHANNELS.AWS_SMITHY_CLIENT_SEND,
        channels.CHANNELS.AWS_SDK_SMITHY_CLIENT_SEND
      ];
      debugBuild.DEBUG_BUILD && core.debug.log(`[orchestrion:aws-sdk] subscribing to channels "${awsSendChannels.join('", "')}"`);
      core.waitForTracingChannelBinding(() => {
        for (const channelName of awsSendChannels) {
          tracingChannel.bindTracingChannelToSpan(
            diagnosticsChannel__namespace.tracingChannel(channelName),
            getSpan,
            opts
          );
        }
      });
    }
  };
});
const awsChannelIntegration = core.defineIntegration(_awsChannelIntegration);

exports.awsChannelIntegration = awsChannelIntegration;
//# sourceMappingURL=index.js.map
