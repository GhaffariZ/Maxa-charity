const SUBSCRIBE_TRANSFORM_NAME = "sentrySubscribeOrchestrionChannel";
function toSubscribeInjections(configs) {
  const seen = /* @__PURE__ */ new Set();
  const injections = [];
  for (const { module } of configs) {
    const key = `${module.name}\0${module.versionRange}\0${String(module.filePath)}`;
    if (seen.has(key)) {
      continue;
    }
    seen.add(key);
    injections.push({
      channelName: module.name,
      module,
      astQuery: "Program",
      transform: SUBSCRIBE_TRANSFORM_NAME
    });
  }
  return injections;
}

export { SUBSCRIBE_TRANSFORM_NAME, toSubscribeInjections };
//# sourceMappingURL=subscribe-injection.js.map
