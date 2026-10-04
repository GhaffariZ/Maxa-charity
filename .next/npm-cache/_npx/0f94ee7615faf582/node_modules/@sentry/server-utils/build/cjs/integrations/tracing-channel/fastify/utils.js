Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const INTEGRATION_NAME = "Fastify";
function defaultShouldHandleError(_error, _request, reply) {
  const statusCode = reply.statusCode;
  return statusCode >= 500 || statusCode <= 299;
}

exports.INTEGRATION_NAME = INTEGRATION_NAME;
exports.defaultShouldHandleError = defaultShouldHandleError;
//# sourceMappingURL=utils.js.map
