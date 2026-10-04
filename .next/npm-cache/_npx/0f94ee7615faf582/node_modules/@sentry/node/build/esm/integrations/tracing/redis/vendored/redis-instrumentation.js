import { InstrumentationBase, InstrumentationNodeModuleDefinition, isWrapped } from '@opentelemetry/instrumentation';
import { SDK_VERSION, startInactiveSpan, SPAN_KIND, getActiveSpan, withActiveSpan, debug, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN, SPAN_STATUS_ERROR } from '@sentry/core';
import { DB_STATEMENT, DB_SYSTEM, NET_PEER_NAME, NET_PEER_PORT } from '@sentry/conventions/attributes';
import { defaultDbStatementSerializer } from '@sentry/server-utils';
import { DEBUG_BUILD } from '../../../../debug-build.js';
import { InstrumentationNodeModuleFile } from '../../InstrumentationNodeModuleFile.js';
import { DB_SYSTEM_VALUE_REDIS, ATTR_DB_CONNECTION_STRING } from './semconv.js';

const PACKAGE_NAME = "@sentry/instrumentation-redis";
const ORIGIN = "auto.db.otel.redis";
const OTEL_OPEN_SPANS = /* @__PURE__ */ Symbol("opentelemetry.instrumentation.redis.open_spans");
const MULTI_COMMAND_OPTIONS = /* @__PURE__ */ Symbol("opentelemetry.instrumentation.redis.multi_command_options");
function endSpan(span, err) {
  if (err) {
    span.setStatus({ code: SPAN_STATUS_ERROR, message: err.message });
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
    DEBUG_BUILD && debug.error("failed to sanitize redis connection url", err);
  }
  return void 0;
}
function getClientAttributes(options) {
  return {
    // oxlint-disable-next-line typescript/no-deprecated
    [DB_SYSTEM]: DB_SYSTEM_VALUE_REDIS,
    // oxlint-disable-next-line typescript/no-deprecated
    [NET_PEER_NAME]: options?.socket?.host,
    // oxlint-disable-next-line typescript/no-deprecated
    [NET_PEER_PORT]: options?.socket?.port,
    [ATTR_DB_CONNECTION_STRING]: removeCredentialsFromDBConnectionStringAttribute(options?.url),
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
  };
}
const _RedisInstrumentationV2_V3 = class _RedisInstrumentationV2_V3 extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    return [
      new InstrumentationNodeModuleDefinition(
        "redis",
        [">=2.6.0 <4"],
        (moduleExports) => {
          if (isWrapped(moduleExports.RedisClient.prototype["internal_send_command"])) {
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
        const attributes = {
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_SYSTEM]: DB_SYSTEM_VALUE_REDIS,
          // oxlint-disable-next-line typescript/no-deprecated
          [DB_STATEMENT]: defaultDbStatementSerializer(cmd.command, cmd.args),
          [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: ORIGIN
        };
        if (this.connection_options) {
          attributes[NET_PEER_NAME] = this.connection_options.host;
          attributes[NET_PEER_PORT] = this.connection_options.port;
        }
        if (this.address) {
          attributes[ATTR_DB_CONNECTION_STRING] = `redis://${this.address}`;
        }
        const span = startInactiveSpan({
          name: `${_RedisInstrumentationV2_V3.COMPONENT}-${cmd.command}`,
          kind: SPAN_KIND.CLIENT,
          attributes
        });
        const originalCallback = arguments[0].callback;
        if (originalCallback) {
          const parentSpan = getActiveSpan();
          arguments[0].callback = function callback(err, reply) {
            runResponseHook(instrumentation.getConfig().responseHook, span, cmd.command, cmd.args, reply);
            endSpan(span, err);
            return withActiveSpan(parentSpan ?? null, () => originalCallback.apply(this, arguments));
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
const _RedisInstrumentationV4_V5 = class _RedisInstrumentationV4_V5 extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
  }
  init() {
    return [
      this._getInstrumentationNodeModuleDefinition("@redis/client"),
      this._getInstrumentationNodeModuleDefinition("@node-redis/client")
    ];
  }
  _getInstrumentationNodeModuleDefinition(basePackageName) {
    const commanderModuleFile = new InstrumentationNodeModuleFile(
      `${basePackageName}/dist/lib/commander.js`,
      ["^1.0.0"],
      (moduleExports, moduleVersion) => {
        const transformCommandArguments = moduleExports.transformCommandArguments;
        if (!transformCommandArguments) {
          DEBUG_BUILD && debug.error("internal instrumentation error, missing transformCommandArguments function");
          return moduleExports;
        }
        const functionToPatch = moduleVersion?.startsWith("1.0.") ? "extendWithCommands" : "attachCommands";
        if (isWrapped(moduleExports?.[functionToPatch])) {
          this._unwrap(moduleExports, functionToPatch);
        }
        this._wrap(moduleExports, functionToPatch, this._getPatchExtendWithCommands(transformCommandArguments));
        return moduleExports;
      },
      (moduleExports) => {
        if (isWrapped(moduleExports?.extendWithCommands)) {
          this._unwrap(moduleExports, "extendWithCommands");
        }
        if (isWrapped(moduleExports?.attachCommands)) {
          this._unwrap(moduleExports, "attachCommands");
        }
      }
    );
    const multiCommanderModule = new InstrumentationNodeModuleFile(
      `${basePackageName}/dist/lib/client/multi-command.js`,
      ["^1.0.0", ">=5.0.0 <5.12.0"],
      (moduleExports) => {
        const redisClientMultiCommandPrototype = moduleExports?.default?.prototype;
        if (isWrapped(redisClientMultiCommandPrototype?.exec)) {
          this._unwrap(redisClientMultiCommandPrototype, "exec");
        }
        this._wrap(redisClientMultiCommandPrototype, "exec", this._getPatchMultiCommandsExec());
        if (isWrapped(redisClientMultiCommandPrototype?.execAsPipeline)) {
          this._unwrap(redisClientMultiCommandPrototype, "execAsPipeline");
        }
        this._wrap(redisClientMultiCommandPrototype, "execAsPipeline", this._getPatchMultiCommandsExec());
        if (isWrapped(redisClientMultiCommandPrototype?.addCommand)) {
          this._unwrap(redisClientMultiCommandPrototype, "addCommand");
        }
        this._wrap(redisClientMultiCommandPrototype, "addCommand", this._getPatchMultiCommandsAddCommand());
        return moduleExports;
      },
      (moduleExports) => {
        const redisClientMultiCommandPrototype = moduleExports?.default?.prototype;
        if (isWrapped(redisClientMultiCommandPrototype?.exec)) {
          this._unwrap(redisClientMultiCommandPrototype, "exec");
        }
        if (isWrapped(redisClientMultiCommandPrototype?.execAsPipeline)) {
          this._unwrap(redisClientMultiCommandPrototype, "execAsPipeline");
        }
        if (isWrapped(redisClientMultiCommandPrototype?.addCommand)) {
          this._unwrap(redisClientMultiCommandPrototype, "addCommand");
        }
      }
    );
    const clientIndexModule = new InstrumentationNodeModuleFile(
      `${basePackageName}/dist/lib/client/index.js`,
      ["^1.0.0", ">=5.0.0 <5.12.0"],
      (moduleExports) => {
        const redisClientPrototype = moduleExports?.default?.prototype;
        if (redisClientPrototype?.multi) {
          if (isWrapped(redisClientPrototype?.multi)) {
            this._unwrap(redisClientPrototype, "multi");
          }
          this._wrap(redisClientPrototype, "multi", this._getPatchRedisClientMulti());
        }
        if (redisClientPrototype?.MULTI) {
          if (isWrapped(redisClientPrototype?.MULTI)) {
            this._unwrap(redisClientPrototype, "MULTI");
          }
          this._wrap(redisClientPrototype, "MULTI", this._getPatchRedisClientMulti());
        }
        if (isWrapped(redisClientPrototype?.sendCommand)) {
          this._unwrap(redisClientPrototype, "sendCommand");
        }
        this._wrap(redisClientPrototype, "sendCommand", this._getPatchRedisClientSendCommand());
        if (isWrapped(redisClientPrototype?.connect)) {
          this._unwrap(redisClientPrototype, "connect");
        }
        this._wrap(redisClientPrototype, "connect", this._getPatchedClientConnect());
        return moduleExports;
      },
      (moduleExports) => {
        const redisClientPrototype = moduleExports?.default?.prototype;
        if (isWrapped(redisClientPrototype?.multi)) {
          this._unwrap(redisClientPrototype, "multi");
        }
        if (isWrapped(redisClientPrototype?.MULTI)) {
          this._unwrap(redisClientPrototype, "MULTI");
        }
        if (isWrapped(redisClientPrototype?.sendCommand)) {
          this._unwrap(redisClientPrototype, "sendCommand");
        }
        if (isWrapped(redisClientPrototype?.connect)) {
          this._unwrap(redisClientPrototype, "connect");
        }
      }
    );
    return new InstrumentationNodeModuleDefinition(
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
          DEBUG_BUILD && debug.error("non-promise result when patching exec/execAsPipeline");
          return execRes;
        }
        return execRes.then((redisRes) => {
          const openSpans = this[OTEL_OPEN_SPANS];
          plugin._endSpansWithRedisReplies(openSpans, redisRes);
          return redisRes;
        }).catch((err) => {
          const openSpans = this[OTEL_OPEN_SPANS];
          if (!openSpans) {
            DEBUG_BUILD && debug.error("cannot find open spans to end for multi/pipeline");
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
        const span = startInactiveSpan({
          name: `${_RedisInstrumentationV4_V5.COMPONENT}-connect`,
          kind: SPAN_KIND.CLIENT,
          attributes
        });
        const res = withActiveSpan(span, () => original.apply(this));
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
    const attributes = getClientAttributes(clientOptions);
    const dbStatement = defaultDbStatementSerializer(commandName, commandArgs);
    if (dbStatement != null) {
      attributes[DB_STATEMENT] = dbStatement;
    }
    const span = startInactiveSpan({
      name: `${_RedisInstrumentationV4_V5.COMPONENT}-${commandName}`,
      kind: SPAN_KIND.CLIENT,
      attributes
    });
    const res = withActiveSpan(span, () => origFunction.apply(origThis, origArguments));
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
      DEBUG_BUILD && debug.error("cannot find open spans to end for redis multi/pipeline");
      return;
    }
    if (replies.length !== openSpans.length) {
      DEBUG_BUILD && debug.error("number of multi command spans does not match response from redis");
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
class RedisInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super(PACKAGE_NAME, SDK_VERSION, config);
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

export { RedisInstrumentation };
//# sourceMappingURL=redis-instrumentation.js.map
