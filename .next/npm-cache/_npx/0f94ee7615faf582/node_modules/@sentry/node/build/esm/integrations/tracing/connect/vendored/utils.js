import { debug } from '@sentry/core';
import { _LAYERS_STORE_PROPERTY } from './internal-types.js';
import { DEBUG_BUILD } from '../../../../debug-build.js';

const addNewStackLayer = (request) => {
  if (Array.isArray(request[_LAYERS_STORE_PROPERTY]) === false) {
    Object.defineProperty(request, _LAYERS_STORE_PROPERTY, {
      enumerable: false,
      value: []
    });
  }
  request[_LAYERS_STORE_PROPERTY].push("/");
  const stackLength = request[_LAYERS_STORE_PROPERTY].length;
  return () => {
    if (stackLength === request[_LAYERS_STORE_PROPERTY].length) {
      request[_LAYERS_STORE_PROPERTY].pop();
    } else {
      DEBUG_BUILD && debug.warn("Connect: Trying to pop the stack multiple time");
    }
  };
};
const replaceCurrentStackRoute = (request, newRoute) => {
  if (newRoute) {
    request[_LAYERS_STORE_PROPERTY].splice(-1, 1, newRoute);
  }
};
const generateRoute = (request) => {
  return request[_LAYERS_STORE_PROPERTY].reduce((acc, sub) => acc.replace(/\/+$/, "") + sub);
};

export { addNewStackLayer, generateRoute, replaceCurrentStackRoute };
//# sourceMappingURL=utils.js.map
