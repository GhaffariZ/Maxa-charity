import * as diagnosticsChannel from 'node:diagnostics_channel';
import { defineIntegration, debug } from '@sentry/core';
import { DEBUG_BUILD } from '../../../debug-build.js';
import { CHANNELS } from '../../../orchestrion/channels.js';
import { isWrappedConsumerCallback, wrapEachMessage, wrapEachBatch } from './consumer.js';
import { applyErrorToSpans, startProducerSpan } from './spans.js';

const INTEGRATION_NAME = "Kafka";
function subscribeToProducer() {
  const channel = diagnosticsChannel.tracingChannel(CHANNELS.KAFKAJS_SEND_BATCH);
  const subscribers = {
    start(ctx) {
      const spans = [];
      (ctx.arguments[0]?.topicMessages ?? []).forEach((topicMessage) => {
        topicMessage.messages.forEach((message) => {
          spans.push(startProducerSpan(topicMessage.topic, message));
        });
      });
      ctx._sentrySpans = spans;
    },
    error(ctx) {
      if (ctx._sentrySpans) {
        applyErrorToSpans(ctx._sentrySpans, ctx.error);
      }
    },
    asyncEnd(ctx) {
      ctx._sentrySpans?.forEach((span) => span.end());
    }
  };
  channel.subscribe(subscribers);
}
function subscribeToConsumer() {
  const channel = diagnosticsChannel.tracingChannel(CHANNELS.KAFKAJS_CONSUMER_RUN);
  const subscribers = {
    start(ctx) {
      const config = ctx.arguments[0];
      if (!config || typeof config !== "object") {
        return;
      }
      if (typeof config.eachMessage === "function" && !isWrappedConsumerCallback(config.eachMessage)) {
        config.eachMessage = wrapEachMessage(config.eachMessage);
      }
      if (typeof config.eachBatch === "function" && !isWrappedConsumerCallback(config.eachBatch)) {
        config.eachBatch = wrapEachBatch(config.eachBatch);
      }
    }
  };
  channel.subscribe(subscribers);
}
const _kafkajsChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel.tracingChannel) {
        return;
      }
      DEBUG_BUILD && debug.log(
        `[orchestrion:kafkajs] subscribing to channels "${CHANNELS.KAFKAJS_SEND_BATCH}", "${CHANNELS.KAFKAJS_CONSUMER_RUN}"`
      );
      subscribeToProducer();
      subscribeToConsumer();
    }
  };
});
const kafkajsChannelIntegration = defineIntegration(_kafkajsChannelIntegration);

export { kafkajsChannelIntegration };
//# sourceMappingURL=index.js.map
