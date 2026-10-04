Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');

function setErrorStatus(span, error) {
  span.setStatus({
    code: core.SPAN_STATUS_ERROR,
    message: `${error.message} ${error.code ? `
Mongoose Error Code: ${error.code}` : ""}`
  });
}
function handlePromiseResponse(execResponse, span) {
  if (!(execResponse instanceof Promise)) {
    span.end();
    return execResponse;
  }
  return execResponse.catch((err) => {
    setErrorStatus(span, err);
    throw err;
  }).finally(() => span.end());
}
function handleCallbackResponse(callback, exec, originalThis, span, args) {
  let callbackArgumentIndex = 0;
  if (args.length === 2) {
    callbackArgumentIndex = 1;
  } else if (args.length === 3) {
    callbackArgumentIndex = 2;
  }
  args[callbackArgumentIndex] = (err, response) => {
    if (err) {
      setErrorStatus(span, err);
    }
    span.end();
    return callback(err, response);
  };
  return exec.apply(originalThis, args);
}

exports.handleCallbackResponse = handleCallbackResponse;
exports.handlePromiseResponse = handlePromiseResponse;
//# sourceMappingURL=utils.js.map
