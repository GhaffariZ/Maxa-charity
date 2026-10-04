import { MySQL2Instrumentation } from './vendored/instrumentation.js';
import { defineIntegration, extendIntegration } from '@sentry/core';
import { generateInstrumentOnce } from '@sentry/node-core';
import { mysql2Integration as mysql2Integration$1 } from '@sentry/server-utils';

const INTEGRATION_NAME = "Mysql2";
const instrumentMysql2 = generateInstrumentOnce(INTEGRATION_NAME, () => new MySQL2Instrumentation());
const _mysql2Integration = (() => {
  return extendIntegration(mysql2Integration$1(), {
    name: INTEGRATION_NAME,
    setupOnce() {
      instrumentMysql2();
    }
  });
});
const mysql2Integration = defineIntegration(_mysql2Integration);

export { instrumentMysql2, mysql2Integration };
//# sourceMappingURL=index.js.map
