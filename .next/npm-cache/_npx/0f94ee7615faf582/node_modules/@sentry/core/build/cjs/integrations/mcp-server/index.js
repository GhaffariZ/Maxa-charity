Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const object = require('../../utils/object.js');
const handlers = require('./handlers.js');
const transport = require('./transport.js');
const validation = require('./validation.js');

const wrappedMcpServerInstances = /* @__PURE__ */ new WeakSet();
function wrapMcpServerWithSentry(mcpServerInstance, options) {
  if (wrappedMcpServerInstances.has(mcpServerInstance)) {
    return mcpServerInstance;
  }
  if (!validation.validateMcpServerInstance(mcpServerInstance)) {
    return mcpServerInstance;
  }
  const serverInstance = mcpServerInstance;
  const captureOptions = { ...options };
  object.fill(serverInstance, "connect", (originalConnect) => {
    return async function(transport$1, ...restArgs) {
      const result = await originalConnect.call(
        this,
        transport$1,
        ...restArgs
      );
      transport.wrapTransportOnMessage(transport$1, captureOptions);
      transport.wrapTransportSend(transport$1, captureOptions);
      transport.wrapTransportOnClose(transport$1);
      transport.wrapTransportError(transport$1);
      return result;
    };
  });
  handlers.wrapAllMCPHandlers(serverInstance);
  handlers.wrapExistingHandlers(serverInstance);
  wrappedMcpServerInstances.add(mcpServerInstance);
  return mcpServerInstance;
}

exports.wrapMcpServerWithSentry = wrapMcpServerWithSentry;
//# sourceMappingURL=index.js.map
