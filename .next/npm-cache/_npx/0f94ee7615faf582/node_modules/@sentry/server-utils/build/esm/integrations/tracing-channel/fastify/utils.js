const INTEGRATION_NAME = "Fastify";
function defaultShouldHandleError(_error, _request, reply) {
  const statusCode = reply.statusCode;
  return statusCode >= 500 || statusCode <= 299;
}

export { INTEGRATION_NAME, defaultShouldHandleError };
//# sourceMappingURL=utils.js.map
