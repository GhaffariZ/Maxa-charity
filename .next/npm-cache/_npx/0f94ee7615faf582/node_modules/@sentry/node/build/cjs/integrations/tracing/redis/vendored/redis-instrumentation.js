Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const serverUtils = require('@sentry/server-utils');
const debugBuild = require('../../../../debug-build.js');
const InstrumentationNodeModuleFile = require('../../InstrumentationNodeModuleFile.js');
const semconv = require('./semconv.js');

const PACKAGE_NAME = "@sentry/instrumentation-redis";
const ORIGIN = "auto.db.otel.redis";
const OTEL_OPEN_SPANS = /* @__PURE__ */ Symbol("opentelemetry.instrumentation.redis.open_spans");
const MULTI_COMMAND_OPTIONS = /* @__PURE__ */ Symbol("opentelemetry.instrumentation.redis.multi_command_options");
function endSpan(span, err) {
  if (err) {
    span.setStatus({ code: core.SPAN_STATUS_ERROR, message: err.message });
  }
  span.end();
}
function runResponseHook(responseHook, span, commandName, commandArgs, response) {
  if (!responseHook) {
    return;
  }
  try {
    responseHook(span, commandName, commandArgs, response);
  } catch {
  }
}
function removeCredentialsFromDBConnectionStringAttribute(url) {
  if (typeof url !== "string" || !url) {
    return void 0;
  }
  try {
    const u = new URL(url);
    u.searchParams.delete("user_pwd");
    u.username = "";
    u.password = "";
    return u.href;
  } catch (err) {
    debugBuild.DEBUG_BUILD && core.debug.error("failed to sanitize redis connection url", err);
  }
  return void 0;
}
function getClientAttributes(options) {
  return {
    // oxlint-disable-next-line typescript/no-deprecated
    [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_REDIS,
    // oxlint-disable-next-line typescript/no-deprecated
    [attributes.NET_PEER_NAME]: options?.socket?.host,
    // oxlint-disable-next-line typescript/no-deprecated
    [attributes.NET_PEER_PORT]: options?.socket?.port,
    [semconv.ATTR_DB_CONNECTION_STRING]: removeCredentialsFromDBConnectionStringAttribute(options?.url),
    [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
  };
}
const _RedisInstrumentationV2_V3 = class _RedisInstrumentationV2_V3 extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    return [
      new instrumentation.InstrumentationNodeModuleDefinition(
        "redis",
        [">=2.6.0 <4"],
        (moduleExports) => {
          if (instrumentation.isWrapped(moduleExports.RedisClient.prototype["internal_send_command"])) {
            this._unwrap(moduleExports.RedisClient.prototype, "internal_send_command");
          }
          this._wrap(moduleExports.RedisClient.prototype, "internal_send_command", this._getPatchInternalSendCommand());
          return moduleExports;
        },
        (moduleExports) => {
          if (moduleExports === void 0) return;
          this._unwrap(moduleExports.RedisClient.prototype, "internal_send_command");
        }
      )
    ];
  }
  _getPatchInternalSendCommand() {
    const instrumentation = this;
    return function internal_send_command(original) {
      return function internal_send_command_trace(cmd) {
        if (arguments.length !== 1 || typeof cmd !== "object") {
          return original.apply(this, arguments);
        }
        const attributes$1 = {
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_SYSTEM]: semconv.DB_SYSTEM_VALUE_REDIS,
          // oxlint-disable-next-line typescript/no-deprecated
          [attributes.DB_STATEMENT]: serverUtils.defaultDbStatementSerializer(cmd.command, cmd.args),
          [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        };
        if (this.connection_options) {
          attributes$1[attributes.NET_PEER_NAME] = this.connection_options.host;
          attributes$1[attributes.NET_PEER_PORT] = this.connection_options.port;
        }
        if (this.address) {
          attributes$1[semconv.ATTR_DB_CONNECTION_STRING] = `redis://${this.address}`;
        }
        const span = core.startInactiveSpan({
          name: `${_RedisInstrumentationV2_V3.COMPONENT}-${cmd.command}`,
          kind: core.SPAN_KIND.CLIENT,
          attributes: attributes$1
        });
        const originalCallback = arguments[0].callback;
        if (originalCallback) {
          const parentSpan = core.getActiveSpan();
          arguments[0].callback = function callback(err, reply) {
            runResponseHook(instrumentation.getConfig().responseHook, span, cmd.command, cmd.args, reply);
            endSpan(span, err);
            return core.withActiveSpan(parentSpan ?? null, () => originalCallback.apply(this, arguments));
          };
        }
        try {
          return original.apply(this, arguments);
        } catch (rethrow) {
          endSpan(span, rethrow);
          throw rethrow;
        }
      };
    };
  }
};
_RedisInstrumentationV2_V3.COMPONENT = "redis";
let RedisInstrumentationV2_V3 = _RedisInstrumentationV2_V3;
const _RedisInstrumentationV4_V5 = class _RedisInstrumentationV4_V5 extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
  }
  init() {
    return [
      this._getInstrumentationNodeModuleDefinition("@redis/client"),
      this._getInstrumentationNodeModuleDefinition("@node-redis/client")
    ];
  }
  _getInstrumentationNodeModuleDefinition(basePackageName) {
    const commanderModuleFile = new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      `${basePackageName}/dist/lib/commander.js`,
      ["^1.0.0"],
      (moduleExports, moduleVersion) => {
        const transformCommandArguments = moduleExports.transformCommandArguments;
        if (!transformCommandArguments) {
          debugBuild.DEBUG_BUILD && core.debug.error("internal instrumentation error, missing transformCommandArguments function");
          return moduleExports;
        }
        const functionToPatch = moduleVersion?.startsWith("1.0.") ? "extendWithCommands" : "attachCommands";
        if (instrumentation.isWrapped(moduleExports?.[functionToPatch])) {
          this._unwrap(moduleExports, functionToPatch);
        }
        this._wrap(moduleExports, functionToPatch, this._getPatchExtendWithCommands(transformCommandArguments));
        return moduleExports;
      },
      (moduleExports) => {
        if (instrumentation.isWrapped(moduleExports?.extendWithCommands)) {
          this._unwrap(moduleExports, "extendWithCommands");
        }
        if (instrumentation.isWrapped(moduleExports?.attachCommands)) {
          this._unwrap(moduleExports, "attachCommands");
        }
      }
    );
    const multiCommanderModule = new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      `${basePackageName}/dist/lib/client/multi-command.js`,
      ["^1.0.0", ">=5.0.0 <5.12.0"],
      (moduleExports) => {
        const redisClientMultiCommandPrototype = moduleExports?.default?.prototype;
        if (instrumentation.isWrapped(redisClientMultiCommandPrototype?.exec)) {
          this._unwrap(redisClientMultiCommandPrototype, "exec");
        }
        this._wrap(redisClientMultiCommandPrototype, "exec", this._getPatchMultiCommandsExec());
        if (instrumentation.isWrapped(redisClientMultiCommandPrototype?.execAsPipeline)) {
          this._unwrap(redisClientMultiCommandPrototype, "execAsPipeline");
        }
        this._wrap(redisClientMultiCommandPrototype, "execAsPipeline", this._getPatchMultiCommandsExec());
        if (instrumentation.isWrapped(redisClientMultiCommandPrototype?.addCommand)) {
          this._unwrap(redisClientMultiCommandPrototype, "addCommand");
        }
        this._wrap(redisClientMultiCommandPrototype, "addCommand", this._getPatchMultiCommandsAddCommand());
        return moduleExports;
      },
      (moduleExports) => {
        const redisClientMultiCommandPrototype = moduleExports?.default?.prototype;
        if (instrumentation.isWrapped(redisClientMultiCommandPrototype?.exec)) {
          this._unwrap(redisClientMultiCommandPrototype, "exec");
        }
        if (instrumentation.isWrapped(redisClientMultiCommandPrototype?.execAsPipeline)) {
          this._unwrap(redisClientMultiCommandPrototype, "execAsPipeline");
        }
        if (instrumentation.isWrapped(redisClientMultiCommandPrototype?.addCommand)) {
          this._unwrap(redisClientMultiCommandPrototype, "addCommand");
        }
      }
    );
    const clientIndexModule = new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
      `${basePackageName}/dist/lib/client/index.js`,
      ["^1.0.0", ">=5.0.0 <5.12.0"],
      (moduleExports) => {
        const redisClientPrototype = moduleExports?.default?.prototype;
        if (redisClientPrototype?.multi) {
          if (instrumentation.isWrapped(redisClientPrototype?.multi)) {
            this._unwrap(redisClientPrototype, "multi");
          }
          this._wrap(redisClientPrototype, "multi", this._getPatchRedisClientMulti());
        }
        if (redisClientPrototype?.MULTI) {
          if (instrumentation.isWrapped(redisClientPrototype?.MULTI)) {
            this._unwrap(redisClientPrototype, "MULTI");
          }
          this._wrap(redisClientPrototype, "MULTI", this._getPatchRedisClientMulti());
        }
        if (instrumentation.isWrapped(redisClientPrototype?.sendCommand)) {
          this._unwrap(redisClientPrototype, "sendCommand");
        }
        this._wrap(redisClientPrototype, "sendCommand", this._getPatchRedisClientSendCommand());
        if (instrumentation.isWrapped(redisClientPrototype?.connect)) {
          this._unwrap(redisClientPrototype, "connect");
        }
        this._wrap(redisClientPrototype, "connect", this._getPatchedClientConnect());
        return moduleExports;
      },
      (moduleExports) => {
        const redisClientPrototype = moduleExports?.default?.prototype;
        if (instrumentation.isWrapped(redisClientPrototype?.multi)) {
          this._unwrap(redisClientPrototype, "multi");
        }
        if (instrumentation.isWrapped(redisClientPrototype?.MULTI)) {
          this._unwrap(redisClientPrototype, "MULTI");
        }
        if (instrumentation.isWrapped(redisClientPrototype?.sendCommand)) {
          this._unwrap(redisClientPrototype, "sendCommand");
        }
        if (instrumentation.isWrapped(redisClientPrototype?.connect)) {
          this._unwrap(redisClientPrototype, "connect");
        }
      }
    );
    return new instrumentation.InstrumentationNodeModuleDefinition(
      basePackageName,
      ["^1.0.0", ">=5.0.0 <5.12.0"],
      (moduleExports) => moduleExports,
      () => {
      },
      [commanderModuleFile, multiCommanderModule, clientIndexModule]
    );
  }
  _getPatchExtendWithCommands(transformCommandArguments) {
    const plugin = this;
    return function extendWithCommandsPatchWrapper(original) {
      return function extendWithCommandsPatch(config) {
        if (config?.BaseClass?.name !== "RedisClient") {
          return original.apply(this, arguments);
        }
        const origExecutor = config.executor;
        config.executor = function(command, args) {
          const redisCommandArguments = transformCommandArguments(command, args).args;
          return plugin._traceClientCommand(origExecutor, this, arguments, redisCommandArguments);
        };
        return original.apply(this, arguments);
      };
    };
  }
  _getPatchMultiCommandsExec() {
    const plugin = this;
    return function execPatchWrapper(original) {
      return function execPatch() {
        const execRes = original.apply(this, arguments);
        if (typeof execRes?.then !== "function") {
          debugBuild.DEBUG_BUILD && core.debug.error("non-promise result when patching exec/execAsPipeline");
          return execRes;
        }
        return execRes.then((redisRes) => {
          const openSpans = this[OTEL_OPEN_SPANS];
          plugin._endSpansWithRedisReplies(openSpans, redisRes);
          return redisRes;
        }).catch((err) => {
          const openSpans = this[OTEL_OPEN_SPANS];
          if (!openSpans) {
            debugBuild.DEBUG_BUILD && core.debug.error("cannot find open spans to end for multi/pipeline");
          } else {
            const replies = err.constructor.name === "MultiErrorReply" ? err.replies : new Array(openSpans.length).fill(err);
            plugin._endSpansWithRedisReplies(openSpans, replies);
          }
          return Promise.reject(err);
        });
      };
    };
  }
  _getPatchMultiCommandsAddCommand() {
    const plugin = this;
    return function addCommandWrapper(original) {
      return function addCommandPatch(args) {
        return plugin._traceClientCommand(original, this, arguments, args);
      };
    };
  }
  _getPatchRedisClientMulti() {
    return function multiPatchWrapper(original) {
      return function multiPatch() {
        const multiRes = original.apply(this, arguments);
        multiRes[MULTI_COMMAND_OPTIONS] = this.options;
        return multiRes;
      };
    };
  }
  _getPatchRedisClientSendCommand() {
    const plugin = this;
    return function sendCommandWrapper(original) {
      return function sendCommandPatch(args) {
        return plugin._traceClientCommand(original, this, arguments, args);
      };
    };
  }
  _getPatchedClientConnect() {
    return function connectWrapper(original) {
      return function patchedConnect() {
        const attributes = getClientAttributes(this.options);
        const span = core.startInactiveSpan({
          name: `${_RedisInstrumentationV4_V5.COMPONENT}-connect`,
          kind: core.SPAN_KIND.CLIENT,
          attributes
        });
        const res = core.withActiveSpan(span, () => original.apply(this));
        return res.then(
          (result) => {
            span.end();
            return result;
          },
          (error) => {
            endSpan(span, error);
            return Promise.reject(error);
          }
        );
      };
    };
  }
  _traceClientCommand(origFunction, origThis, origArguments, redisCommandArguments) {
    const clientOptions = origThis.options || origThis[MULTI_COMMAND_OPTIONS];
    const commandName = redisCommandArguments[0];
    const commandArgs = redisCommandArguments.slice(1);
    const attributes$1 = getClientAttributes(clientOptions);
    const dbStatement = serverUtils.defaultDbStatementSerializer(commandName, commandArgs);
    if (dbStatement != null) {
      attributes$1[attributes.DB_STATEMENT] = dbStatement;
    }
    const span = core.startInactiveSpan({
      name: `${_RedisInstrumentationV4_V5.COMPONENT}-${commandName}`,
      kind: core.SPAN_KIND.CLIENT,
      attributes: attributes$1
    });
    const res = core.withActiveSpan(span, () => origFunction.apply(origThis, origArguments));
    if (res instanceof Promise) {
      res.then(
        (redisRes) => {
          this._endSpanWithResponse(span, commandName, commandArgs, redisRes, void 0);
        },
        (err) => {
          this._endSpanWithResponse(span, commandName, commandArgs, null, err);
        }
      );
    } else {
      const redisClientMultiCommand = res;
      redisClientMultiCommand[OTEL_OPEN_SPANS] = redisClientMultiCommand[OTEL_OPEN_SPANS] || [];
      redisClientMultiCommand[OTEL_OPEN_SPANS].push({
        span,
        commandName,
        commandArgs
      });
    }
    return res;
  }
  _endSpansWithRedisReplies(openSpans, replies) {
    if (!openSpans) {
      debugBuild.DEBUG_BUILD && core.debug.error("cannot find open spans to end for redis multi/pipeline");
      return;
    }
    if (replies.length !== openSpans.length) {
      debugBuild.DEBUG_BUILD && core.debug.error("number of multi command spans does not match response from redis");
      return;
    }
    for (let i = 0; i < openSpans.length; i++) {
      const { span, commandName, commandArgs } = openSpans[i];
      const currCommandRes = replies[i];
      const [res, err] = currCommandRes instanceof Error ? [null, currCommandRes] : [currCommandRes, void 0];
      this._endSpanWithResponse(span, commandName, commandArgs, res, err);
    }
  }
  _endSpanWithResponse(span, commandName, commandArgs, response, error) {
    if (!error) {
      runResponseHook(this.getConfig().responseHook, span, commandName, commandArgs, response);
    }
    endSpan(span, error);
  }
};
_RedisInstrumentationV4_V5.COMPONENT = "redis";
let RedisInstrumentationV4_V5 = _RedisInstrumentationV4_V5;
class RedisInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, core.SDK_VERSION, config);
    this.initialized = false;
    this.instrumentationV2_V3 = new RedisInstrumentationV2_V3(this.getConfig());
    this.instrumentationV4_V5 = new RedisInstrumentationV4_V5(this.getConfig());
    this.initialized = true;
  }
  setConfig(config = {}) {
    super.setConfig(config);
    if (!this.initialized) {
      return;
    }
    this.instrumentationV2_V3.setConfig(config);
    this.instrumentationV4_V5.setConfig(config);
  }
  init() {
  }
  getModuleDefinitions() {
    return [...this.instrumentationV2_V3.getModuleDefinitions(), ...this.instrumentationV4_V5.getModuleDefinitions()];
  }
  setTracerProvider(tracerProvider) {
    super.setTracerProvider(tracerProvider);
    if (!this.initialized) {
      return;
    }
    this.instrumentationV2_V3.setTracerProvider(tracerProvider);
    this.instrumentationV4_V5.setTracerProvider(tracerProvider);
  }
  enable() {
    super.enable();
    if (!this.initialized) {
      return;
    }
    this.instrumentationV2_V3.enable();
    this.instrumentationV4_V5.enable();
  }
  disable() {
    super.disable();
    if (!this.initialized) {
      return;
    }
    this.instrumentationV2_V3.disable();
    this.instrumentationV4_V5.disable();
  }
}

exports.RedisInstrumentation = RedisInstrumentation;
//# sourceMappingURL=redis-instrumentation.js.map
