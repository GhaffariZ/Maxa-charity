Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');
const semconv = require('./semconv.js');
const utils = require('./utils.js');

const PACKAGE_NAME = "@sentry/instrumentation-kafkajs";
class KafkaJsInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    const unpatch = (moduleExports) => {
      if (instrumentation.isWrapped(moduleExports?.Kafka?.prototype.producer)) {
        this._unwrap(moduleExports.Kafka.prototype, "producer");
      }
      if (instrumentation.isWrapped(moduleExports?.Kafka?.prototype.consumer)) {
        this._unwrap(moduleExports.Kafka.prototype, "consumer");
      }
    };
    const module = new instrumentation.InstrumentationNodeModuleDefinition(
      "kafkajs",
      [">=0.3.0 <3"],
      (moduleExports) => {
        unpatch(moduleExports);
        this._wrap(moduleExports?.Kafka?.prototype, "producer", this._getProducerPatch());
        this._wrap(moduleExports?.Kafka?.prototype, "consumer", this._getConsumerPatch());
        return moduleExports;
      },
      unpatch
    );
    return module;
  }
  _getConsumerPatch() {
    const instrumentation$1 = this;
    return (original) => {
      return function consumer(...args) {
        const newConsumer = original.apply(this, args);
        if (instrumentation.isWrapped(newConsumer.run)) {
          instrumentation$1._unwrap(newConsumer, "run");
        }
        instrumentation$1._wrap(newConsumer, "run", instrumentation$1._getConsumerRunPatch());
        return newConsumer;
      };
    };
  }
  _getProducerPatch() {
    const instrumentation$1 = this;
    return (original) => {
      return function consumer(...args) {
        const newProducer = original.apply(this, args);
        if (instrumentation.isWrapped(newProducer.sendBatch)) {
          instrumentation$1._unwrap(newProducer, "sendBatch");
        }
        instrumentation$1._wrap(newProducer, "sendBatch", instrumentation$1._getSendBatchPatch());
        if (instrumentation.isWrapped(newProducer.send)) {
          instrumentation$1._unwrap(newProducer, "send");
        }
        instrumentation$1._wrap(newProducer, "send", instrumentation$1._getSendPatch());
        if (instrumentation.isWrapped(newProducer.transaction)) {
          instrumentation$1._unwrap(newProducer, "transaction");
        }
        instrumentation$1._wrap(newProducer, "transaction", instrumentation$1._getProducerTransactionPatch());
        return newProducer;
      };
    };
  }
  _getConsumerRunPatch() {
    const instrumentation$1 = this;
    return (original) => {
      return function run(...args) {
        const config = args[0];
        if (config?.eachMessage) {
          if (instrumentation.isWrapped(config.eachMessage)) {
            instrumentation$1._unwrap(config, "eachMessage");
          }
          instrumentation$1._wrap(config, "eachMessage", instrumentation$1._getConsumerEachMessagePatch());
        }
        if (config?.eachBatch) {
          if (instrumentation.isWrapped(config.eachBatch)) {
            instrumentation$1._unwrap(config, "eachBatch");
          }
          instrumentation$1._wrap(config, "eachBatch", instrumentation$1._getConsumerEachBatchPatch());
        }
        return original.call(this, config);
      };
    };
  }
  _getConsumerEachMessagePatch() {
    return (original) => {
      return function eachMessage(...args) {
        const payload = args[0];
        const sentryTrace = utils.getHeaderAsString(payload.message.headers, "sentry-trace");
        const baggage = utils.getHeaderAsString(payload.message.headers, "baggage");
        return core.continueTrace({ sentryTrace, baggage }, () => {
          const span = utils.startConsumerSpan({
            topic: payload.topic,
            message: payload.message,
            operationType: semconv.MESSAGING_OPERATION_TYPE_VALUE_PROCESS,
            attributes: {
              [semconv.ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.partition)
            }
          });
          const eachMessagePromise = core.withActiveSpan(span, () => {
            return original.apply(this, args);
          });
          return utils.endSpansOnPromise([span], eachMessagePromise);
        });
      };
    };
  }
  _getConsumerEachBatchPatch() {
    return (original) => {
      return function eachBatch(...args) {
        const payload = args[0];
        const receivingSpan = core.startNewTrace(
          () => utils.startConsumerSpan({
            topic: payload.batch.topic,
            message: void 0,
            operationType: semconv.MESSAGING_OPERATION_TYPE_VALUE_RECEIVE,
            attributes: {
              [attributes.MESSAGING_BATCH_MESSAGE_COUNT]: payload.batch.messages.length,
              [semconv.ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.batch.partition)
            }
          })
        );
        return core.withActiveSpan(receivingSpan, () => {
          const spans = [receivingSpan];
          payload.batch.messages.forEach((message) => {
            spans.push(
              utils.startConsumerSpan({
                topic: payload.batch.topic,
                message,
                operationType: semconv.MESSAGING_OPERATION_TYPE_VALUE_PROCESS,
                links: utils.getLinksFromHeaders(message.headers),
                attributes: {
                  [semconv.ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.batch.partition)
                }
              })
            );
          });
          const batchMessagePromise = original.apply(this, args);
          return utils.endSpansOnPromise(spans, batchMessagePromise);
        });
      };
    };
  }
  _getProducerTransactionPatch() {
    const instrumentation = this;
    return (original) => {
      return function transaction(...args) {
        const transactionSpan = core.startInactiveSpan({ name: "transaction" });
        const transactionPromise = original.apply(this, args);
        transactionPromise.then((transaction2) => {
          const originalSend = transaction2.send;
          transaction2.send = function send(...args2) {
            return core.withActiveSpan(transactionSpan, () => {
              const patched = instrumentation._getSendPatch()(originalSend);
              return patched.apply(this, args2).catch((err) => {
                transactionSpan.setStatus({
                  code: core.SPAN_STATUS_ERROR,
                  message: err?.message
                });
                throw err;
              });
            });
          };
          const originalSendBatch = transaction2.sendBatch;
          transaction2.sendBatch = function sendBatch(...args2) {
            return core.withActiveSpan(transactionSpan, () => {
              const patched = instrumentation._getSendBatchPatch()(originalSendBatch);
              return patched.apply(this, args2).catch((err) => {
                transactionSpan.setStatus({
                  code: core.SPAN_STATUS_ERROR,
                  message: err?.message
                });
                throw err;
              });
            });
          };
          const originalCommit = transaction2.commit;
          transaction2.commit = function commit(...args2) {
            const originCommitPromise = originalCommit.apply(this, args2).then(() => {
              transactionSpan.setStatus({ code: core.SPAN_STATUS_OK });
            });
            return utils.endSpansOnPromise([transactionSpan], originCommitPromise);
          };
          const originalAbort = transaction2.abort;
          transaction2.abort = function abort(...args2) {
            const originAbortPromise = originalAbort.apply(this, args2);
            return utils.endSpansOnPromise([transactionSpan], originAbortPromise);
          };
        }).catch((err) => {
          transactionSpan.setStatus({
            code: core.SPAN_STATUS_ERROR,
            message: err?.message
          });
          transactionSpan.end();
        });
        return transactionPromise;
      };
    };
  }
  _getSendBatchPatch() {
    return (original) => {
      return function sendBatch(...args) {
        const batch = args[0];
        const messages = batch.topicMessages || [];
        const spans = [];
        messages.forEach((topicMessage) => {
          topicMessage.messages.forEach((message) => {
            spans.push(utils.startProducerSpan(topicMessage.topic, message));
          });
        });
        const origSendResult = original.apply(this, args);
        return utils.endSpansOnPromise(spans, origSendResult);
      };
    };
  }
  _getSendPatch() {
    return (original) => {
      return function send(...args) {
        const record = args[0];
        const spans = record.messages.map((message) => {
          return utils.startProducerSpan(record.topic, message);
        });
        const origSendResult = original.apply(this, args);
        return utils.endSpansOnPromise(spans, origSendResult);
      };
    };
  }
}

exports.KafkaJsInstrumentation = KafkaJsInstrumentation;
//# sourceMappingURL=instrumentation.js.map
