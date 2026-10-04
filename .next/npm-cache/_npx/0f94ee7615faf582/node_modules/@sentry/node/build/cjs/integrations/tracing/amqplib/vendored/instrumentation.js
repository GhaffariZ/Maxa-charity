Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const InstrumentationNodeModuleFile = require('../../InstrumentationNodeModuleFile.js');
const patches = require('./patches.js');
const types = require('./types.js');

const PACKAGE_NAME = "@sentry/instrumentation-amqplib";
const supportedVersions = [">=0.5.5 <2"];
class AmqplibInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    const channelModelModuleFile = new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      "amqplib/lib/channel_model.js",
      supportedVersions,
      this.patchChannelModel.bind(this),
      this.unpatchChannelModel.bind(this)
    );
    const callbackModelModuleFile = new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      "amqplib/lib/callback_model.js",
      supportedVersions,
      this.patchChannelModel.bind(this),
      this.unpatchChannelModel.bind(this)
    );
    const connectModuleFile = new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      "amqplib/lib/connect.js",
      supportedVersions,
      this.patchConnect.bind(this),
      this.unpatchConnect.bind(this)
    );
    const module = new instrumentation.InstrumentationNodeModuleDefinition("amqplib", supportedVersions, void 0, void 0, [
      channelModelModuleFile,
      connectModuleFile,
      callbackModelModuleFile
    ]);
    return module;
  }
  patchConnect(moduleExports) {
    const unpatchedExports = this.unpatchConnect(moduleExports);
    if (!instrumentation.isWrapped(unpatchedExports.connect)) {
      this._wrap(unpatchedExports, "connect", patches.getConnectPatch);
    }
    return unpatchedExports;
  }
  unpatchConnect(moduleExports) {
    if (instrumentation.isWrapped(moduleExports.connect)) {
      this._unwrap(moduleExports, "connect");
    }
    return moduleExports;
  }
  patchChannelModel(moduleExports) {
    if (!instrumentation.isWrapped(moduleExports.Channel.prototype.publish)) {
      this._wrap(moduleExports.Channel.prototype, "publish", patches.getPublishPatch);
    }
    if (!instrumentation.isWrapped(moduleExports.Channel.prototype.consume)) {
      this._wrap(moduleExports.Channel.prototype, "consume", patches.getConsumePatch);
    }
    if (!instrumentation.isWrapped(moduleExports.Channel.prototype.ack)) {
      this._wrap(moduleExports.Channel.prototype, "ack", patches.getAckPatch(false, types.EndOperation.Ack));
    }
    if (!instrumentation.isWrapped(moduleExports.Channel.prototype.nack)) {
      this._wrap(moduleExports.Channel.prototype, "nack", patches.getAckPatch(true, types.EndOperation.Nack));
    }
    if (!instrumentation.isWrapped(moduleExports.Channel.prototype.reject)) {
      this._wrap(moduleExports.Channel.prototype, "reject", patches.getAckPatch(true, types.EndOperation.Reject));
    }
    if (!instrumentation.isWrapped(moduleExports.Channel.prototype.ackAll)) {
      this._wrap(moduleExports.Channel.prototype, "ackAll", patches.getAckAllPatch(false, types.EndOperation.AckAll));
    }
    if (!instrumentation.isWrapped(moduleExports.Channel.prototype.nackAll)) {
      this._wrap(moduleExports.Channel.prototype, "nackAll", patches.getAckAllPatch(true, types.EndOperation.NackAll));
    }
    if (!instrumentation.isWrapped(moduleExports.Channel.prototype.emit)) {
      this._wrap(moduleExports.Channel.prototype, "emit", patches.getChannelEmitPatch);
    }
    if (!instrumentation.isWrapped(moduleExports.ConfirmChannel.prototype.publish)) {
      this._wrap(moduleExports.ConfirmChannel.prototype, "publish", patches.getConfirmedPublishPatch);
    }
    return moduleExports;
  }
  unpatchChannelModel(moduleExports) {
    if (instrumentation.isWrapped(moduleExports.Channel.prototype.publish)) {
      this._unwrap(moduleExports.Channel.prototype, "publish");
    }
    if (instrumentation.isWrapped(moduleExports.Channel.prototype.consume)) {
      this._unwrap(moduleExports.Channel.prototype, "consume");
    }
    if (instrumentation.isWrapped(moduleExports.Channel.prototype.ack)) {
      this._unwrap(moduleExports.Channel.prototype, "ack");
    }
    if (instrumentation.isWrapped(moduleExports.Channel.prototype.nack)) {
      this._unwrap(moduleExports.Channel.prototype, "nack");
    }
    if (instrumentation.isWrapped(moduleExports.Channel.prototype.reject)) {
      this._unwrap(moduleExports.Channel.prototype, "reject");
    }
    if (instrumentation.isWrapped(moduleExports.Channel.prototype.ackAll)) {
      this._unwrap(moduleExports.Channel.prototype, "ackAll");
    }
    if (instrumentation.isWrapped(moduleExports.Channel.prototype.nackAll)) {
      this._unwrap(moduleExports.Channel.prototype, "nackAll");
    }
    if (instrumentation.isWrapped(moduleExports.Channel.prototype.emit)) {
      this._unwrap(moduleExports.Channel.prototype, "emit");
    }
    if (instrumentation.isWrapped(moduleExports.ConfirmChannel.prototype.publish)) {
      this._unwrap(moduleExports.ConfirmChannel.prototype, "publish");
    }
    return moduleExports;
  }
}

exports.AmqplibInstrumentation = AmqplibInstrumentation;
//# sourceMappingURL=instrumentation.js.map
