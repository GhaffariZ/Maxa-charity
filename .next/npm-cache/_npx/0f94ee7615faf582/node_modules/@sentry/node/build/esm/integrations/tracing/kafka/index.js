import { KafkaJsInstrumentation } from './vendored/instrumentation.js';
import { defineIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';

const INTEGRATION_NAME = "Kafka";
const instrumentKafka = generateInstrumentOnce(INTEGRATION_NAME, () => new KafkaJsInstrumentation());
const _kafkaIntegration = (() => {
  return {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentKafka();
    }
  };
});
const kafkaIntegration = defineIntegration(_kafkaIntegration);

export { instrumentKafka, kafkaIntegration };
//# sourceMappingURL=index.js.map
