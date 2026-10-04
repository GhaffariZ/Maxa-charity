Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const diagnosticsChannel = require('node:diagnostics_channel');
const mongooseDcSubscriber = require('./mongoose-dc-subscriber.js');

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

const _mongooseIntegration = (() => {
  return {
    name: "Mongoose",
    setupOnce() {
      if (!diagnosticsChannel__namespace.tracingChannel) {
        return;
      }
      core.waitForTracingChannelBinding(() => {
        mongooseDcSubscriber.subscribeMongooseDiagnosticChannels(diagnosticsChannel__namespace.tracingChannel);
      });
    }
  };
});
const mongooseIntegration = core.defineIntegration(_mongooseIntegration);

exports.mongooseIntegration = mongooseIntegration;
//# sourceMappingURL=index.js.map
