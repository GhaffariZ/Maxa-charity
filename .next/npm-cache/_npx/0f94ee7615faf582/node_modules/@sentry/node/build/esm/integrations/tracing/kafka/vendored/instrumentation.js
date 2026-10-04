import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { MESSAGING_BATCH_MESSAGE_COUNT } from '@sentry/conventions/attributes';
import { SDK_VERSION, continueTrace, withActiveSpan, startNewTrace, startInactiveSpan, SPAN_STATUS_ERROR, SPAN_STATUS_OK } from '@sentry/core';
import { ATTR_MESSAGING_DESTINATION_PARTITION_ID, MESSAGING_OPERATION_TYPE_VALUE_PROCESS, MESSAGING_OPERATION_TYPE_VALUE_RECEIVE } from './semconv.js';
import { getHeaderAsString, startConsumerSpan, endSpansOnPromise, getLinksFromHeaders, startProducerSpan } from './utils.js';

const PACKAGE_NAME = "@sentry/instrumentation-kafkajs";
class KafkaJsInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    const unpatch = (moduleExports) => {
      if (isWrapped(moduleExports?.Kafka?.prototype.producer)) {
        this._unwrap(moduleExports.Kafka.prototype, "producer");
      }
      if (isWrapped(moduleExports?.Kafka?.prototype.consumer)) {
        this._unwrap(moduleExports.Kafka.prototype, "consumer");
      }
    };
    const module = new InstrumentationNodeModuleDefinition(
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
    const instrumentation = this;
    return (original) => {
      return function consumer(...args) {
        const newConsumer = original.apply(this, args);
        if (isWrapped(newConsumer.run)) {
          instrumentation._unwrap(newConsumer, "run");
        }
        instrumentation._wrap(newConsumer, "run", instrumentation._getConsumerRunPatch());
        return newConsumer;
      };
    };
  }
  _getProducerPatch() {
    const instrumentation = this;
    return (original) => {
      return function consumer(...args) {
        const newProducer = original.apply(this, args);
        if (isWrapped(newProducer.sendBatch)) {
          instrumentation._unwrap(newProducer, "sendBatch");
        }
        instrumentation._wrap(newProducer, "sendBatch", instrumentation._getSendBatchPatch());
        if (isWrapped(newProducer.send)) {
          instrumentation._unwrap(newProducer, "send");
        }
        instrumentation._wrap(newProducer, "send", instrumentation._getSendPatch());
        if (isWrapped(newProducer.transaction)) {
          instrumentation._unwrap(newProducer, "transaction");
        }
        instrumentation._wrap(newProducer, "transaction", instrumentation._getProducerTransactionPatch());
        return newProducer;
      };
    };
  }
  _getConsumerRunPatch() {
    const instrumentation = this;
    return (original) => {
      return function run(...args) {
        const config = args[0];
        if (config?.eachMessage) {
          if (isWrapped(config.eachMessage)) {
            instrumentation._unwrap(config, "eachMessage");
          }
          instrumentation._wrap(config, "eachMessage", instrumentation._getConsumerEachMessagePatch());
        }
        if (config?.eachBatch) {
          if (isWrapped(config.eachBatch)) {
            instrumentation._unwrap(config, "eachBatch");
          }
          instrumentation._wrap(config, "eachBatch", instrumentation._getConsumerEachBatchPatch());
        }
        return original.call(this, config);
      };
    };
  }
  _getConsumerEachMessagePatch() {
    return (original) => {
      return function eachMessage(...args) {
        const payload = args[0];
        const sentryTrace = getHeaderAsString(payload.message.headers, "sentry-trace");
        const baggage = getHeaderAsString(payload.message.headers, "baggage");
        return continueTrace({ sentryTrace, baggage }, () => {
          const span = startConsumerSpan({
            topic: payload.topic,
            message: payload.message,
            operationType: MESSAGING_OPERATION_TYPE_VALUE_PROCESS,
            attributes: {
              [ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.partition)
            }
          });
          const eachMessagePromise = withActiveSpan(span, () => {
            return original.apply(this, args);
          });
          return endSpansOnPromise([span], eachMessagePromise);
        });
      };
    };
  }
  _getConsumerEachBatchPatch() {
    return (original) => {
      return function eachBatch(...args) {
        const payload = args[0];
        const receivingSpan = startNewTrace(
          () => startConsumerSpan({
            topic: payload.batch.topic,
            message: void 0,
            operationType: MESSAGING_OPERATION_TYPE_VALUE_RECEIVE,
            attributes: {
              [MESSAGING_BATCH_MESSAGE_COUNT]: payload.batch.messages.length,
              [ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.batch.partition)
            }
          })
        );
        return withActiveSpan(receivingSpan, () => {
          const spans = [receivingSpan];
          payload.batch.messages.forEach((message) => {
            spans.push(
              startConsumerSpan({
                topic: payload.batch.topic,
                message,
                operationType: MESSAGING_OPERATION_TYPE_VALUE_PROCESS,
                links: getLinksFromHeaders(message.headers),
                attributes: {
                  [ATTR_MESSAGING_DESTINATION_PARTITION_ID]: String(payload.batch.partition)
                }
              })
            );
          });
          const batchMessagePromise = original.apply(this, args);
          return endSpansOnPromise(spans, batchMessagePromise);
        });
      };
    };
  }
  _getProducerTransactionPatch() {
    const instrumentation = this;
    return (original) => {
      return function transaction(...args) {
        const transactionSpan = startInactiveSpan({ name: "transaction" });
        const transactionPromise = original.apply(this, args);
        transactionPromise.then((transaction2) => {
          const originalSend = transaction2.send;
          transaction2.send = function send(...args2) {
            return withActiveSpan(transactionSpan, () => {
              const patched = instrumentation._getSendPatch()(originalSend);
              return patched.apply(this, args2).catch((err) => {
                transactionSpan.setStatus({
                  code: SPAN_STATUS_ERROR,
                  message: err?.message
                });
                throw err;
              });
            });
          };
          const originalSendBatch = transaction2.sendBatch;
          transaction2.sendBatch = function sendBatch(...args2) {
            return withActiveSpan(transactionSpan, () => {
              const patched = instrumentation._getSendBatchPatch()(originalSendBatch);
              return patched.apply(this, args2).catch((err) => {
                transactionSpan.setStatus({
                  code: SPAN_STATUS_ERROR,
                  message: err?.message
                });
                throw err;
              });
            });
          };
          const originalCommit = transaction2.commit;
          transaction2.commit = function commit(...args2) {
            const originCommitPromise = originalCommit.apply(this, args2).then(() => {
              transactionSpan.setStatus({ code: SPAN_STATUS_OK });
            });
            return endSpansOnPromise([transactionSpan], originCommitPromise);
          };
          const originalAbort = transaction2.abort;
          transaction2.abort = function abort(...args2) {
            const originAbortPromise = originalAbort.apply(this, args2);
            return endSpansOnPromise([transactionSpan], originAbortPromise);
          };
        }).catch((err) => {
          transactionSpan.setStatus({
            code: SPAN_STATUS_ERROR,
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
            spans.push(startProducerSpan(topicMessage.topic, message));
          });
        });
        const origSendResult = original.apply(this, args);
        return endSpansOnPromise(spans, origSendResult);
      };
    };
  }
  _getSendPatch() {
    return (original) => {
      return function send(...args) {
        const record = args[0];
        const spans = record.messages.map((message) => {
          return startProducerSpan(record.topic, message);
        });
        const origSendResult = original.apply(this, args);
        return endSpansOnPromise(spans, origSendResult);
      };
    };
  }
}

export { KafkaJsInstrumentation };
//# sourceMappingURL=instrumentation.js.map
