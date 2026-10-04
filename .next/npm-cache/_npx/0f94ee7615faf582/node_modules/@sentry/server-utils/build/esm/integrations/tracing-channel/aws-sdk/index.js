import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, debug, waitForTracingChannelBinding, startInactiveSpan, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SPAN_KIND } from '@sentry/core';
import { CLOUD_REGION, _AWS_REQUEST_ID, HTTP_STATUS_CODE, AWS_REQUEST_EXTENDED_ID } from '@sentry/conventions/attributes';
import { DEBUG_BUILD } from '../../../debug-build.js';
import { CHANNELS } from '../../../orchestrion/channels.js';
import { bindTracingChannelToSpan } from '../../../tracing-channel.js';
import { AWS_SDK_ORIGIN } from './constants.js';
import { ServicesExtensions } from './services/ServicesExtensions.js';
import { removeSuffixFromStringIfExists, normalizeV3Request, extractAttributesFromNormalizedRequest } from './utils.js';

const INTEGRATION_NAME = "Aws";
function safe(fn) {
  try {
    return fn();
  } catch (error) {
    DEBUG_BUILD && debug.warn("[orchestrion:aws-sdk] error building span", error);
    return void 0;
  }
}
function setMetadataAttributes(span, metadata) {
  if (!metadata) {
    return;
  }
  if (metadata.requestId) {
    span.setAttribute(_AWS_REQUEST_ID, metadata.requestId);
  }
  if (metadata.httpStatusCode) {
    span.setAttribute(HTTP_STATUS_CODE, metadata.httpStatusCode);
  }
  if (metadata.extendedRequestId) {
    span.setAttribute(AWS_REQUEST_EXTENDED_ID, metadata.extendedRequestId);
  }
}
const _awsChannelIntegration = (() => {
  const servicesExtensions = new ServicesExtensions();
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
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
        removeSuffixFromStringIfExists(data.self?.constructor?.name || "AWS", "Client");
        if (!command.input) {
          command.input = {};
        }
        const normalizedRequest = normalizeV3Request(serviceName, commandName, command.input, void 0);
        const requestMetadata = servicesExtensions.requestPreSpanHook(normalizedRequest);
        const span = startInactiveSpan({
          name: requestMetadata.spanName ?? `${normalizedRequest.serviceName}.${normalizedRequest.commandName}`,
          kind: requestMetadata.spanKind ?? SPAN_KIND.CLIENT,
          // `rpc` matches what the exporter infers from `rpc.service` for the OTel aws-sdk spans;
          // service extensions override it where inference yields a different op (DynamoDB: `db`).
          op: requestMetadata.spanOp || "rpc",
          attributes: {
            [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: AWS_SDK_ORIGIN,
            ...extractAttributesFromNormalizedRequest(normalizedRequest),
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
              span.setAttribute(CLOUD_REGION, region);
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
        CHANNELS.AWS_SMITHY_CORE_SEND,
        CHANNELS.AWS_SMITHY_CLIENT_SEND,
        CHANNELS.AWS_SDK_SMITHY_CLIENT_SEND
      ];
      DEBUG_BUILD && debug.log(`[orchestrion:aws-sdk] subscribing to channels "${awsSendChannels.join('", "')}"`);
      waitForTracingChannelBinding(() => {
        for (const channelName of awsSendChannels) {
          bindTracingChannelToSpan(
            diagnosticsChannel.tracingChannel(channelName),
            getSpan,
            opts
          );
        }
      });
    }
  };
});
const awsChannelIntegration = defineIntegration(_awsChannelIntegration);

export { awsChannelIntegration };
//# sourceMappingURL=index.js.map
