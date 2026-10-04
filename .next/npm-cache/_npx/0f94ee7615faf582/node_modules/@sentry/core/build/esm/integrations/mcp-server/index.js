import { fill } from '../../utils/object.js';
import { wrapAllMCPHandlers, wrapExistingHandlers } from './handlers.js';
import { wrapTransportOnMessage, wrapTransportSend, wrapTransportOnClose, wrapTransportError } from './transport.js';
import { validateMcpServerInstance } from './validation.js';

const wrappedMcpServerInstances = /* @__PURE__ */ new WeakSet();
function wrapMcpServerWithSentry(mcpServerInstance, options) {
  if (wrappedMcpServerInstances.has(mcpServerInstance)) {
    return mcpServerInstance;
  }
  if (!validateMcpServerInstance(mcpServerInstance)) {
    return mcpServerInstance;
  }
  const serverInstance = mcpServerInstance;
  const captureOptions = { ...options };
  fill(serverInstance, "connect", (originalConnect) => {
    return async function(transport, ...restArgs) {
      const result = await originalConnect.call(
        this,
        transport,
        ...restArgs
      );
      wrapTransportOnMessage(transport, captureOptions);
      wrapTransportSend(transport, captureOptions);
      wrapTransportOnClose(transport);
      wrapTransportError(transport);
      return result;
    };
  });
  wrapAllMCPHandlers(serverInstance);
  wrapExistingHandlers(serverInstance);
  wrappedMcpServerInstances.add(mcpServerInstance);
  return mcpServerInstance;
}

export { wrapMcpServerWithSentry };
//# sourceMappingURL=index.js.map
