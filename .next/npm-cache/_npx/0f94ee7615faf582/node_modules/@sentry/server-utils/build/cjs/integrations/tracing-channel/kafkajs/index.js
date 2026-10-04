Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const diagnosticsChannel = require('node:diagnostics_channel');
const core = require('@sentry/core');
const debugBuild = require('../../../debug-build.js');
const channels = require('../../../orchestrion/channels.js');
const consumer = require('./consumer.js');
const spans = require('./spans.js');

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

const INTEGRATION_NAME = "Kafka";
function subscribeToProducer() {
  const channel = diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.KAFKAJS_SEND_BATCH);
  const subscribers = {
    start(ctx) {
      const spans$1 = [];
      (ctx.arguments[0]?.topicMessages ?? []).forEach((topicMessage) => {
        topicMessage.messages.forEach((message) => {
          spans$1.push(spans.startProducerSpan(topicMessage.topic, message));
        });
      });
      ctx._sentrySpans = spans$1;
    },
    error(ctx) {
      if (ctx._sentrySpans) {
        spans.applyErrorToSpans(ctx._sentrySpans, ctx.error);
      }
    },
    asyncEnd(ctx) {
      ctx._sentrySpans?.forEach((span) => span.end());
    }
  };
  channel.subscribe(subscribers);
}
function subscribeToConsumer() {
  const channel = diagnosticsChannel__namespace.tracingChannel(channels.CHANNELS.KAFKAJS_CONSUMER_RUN);
  const subscribers = {
    start(ctx) {
      const config = ctx.arguments[0];
      if (!config || typeof config !== "object") {
        return;
      }
      if (typeof config.eachMessage === "function" && !consumer.isWrappedConsumerCallback(config.eachMessage)) {
        config.eachMessage = consumer.wrapEachMessage(config.eachMessage);
      }
      if (typeof config.eachBatch === "function" && !consumer.isWrappedConsumerCallback(config.eachBatch)) {
        config.eachBatch = consumer.wrapEachBatch(config.eachBatch);
      }
    }
  };
  channel.subscribe(subscribers);
}
const _kafkajsChannelIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      debugBuild.DEBUG_BUILD && core.debug.log(
        `[orchestrion:kafkajs] subscribing to channels "${channels.CHANNELS.KAFKAJS_SEND_BATCH}", "${channels.CHANNELS.KAFKAJS_CONSUMER_RUN}"`
      );
      subscribeToProducer();
      subscribeToConsumer();
    }
  };
});
const kafkajsChannelIntegration = core.defineIntegration(_kafkajsChannelIntegration);

exports.kafkajsChannelIntegration = kafkajsChannelIntegration;
//# sourceMappingURL=index.js.map
