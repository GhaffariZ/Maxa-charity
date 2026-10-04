import { getIsolationScope, getCurrentScope, getClient } from './currentScopes.js';
import { DEBUG_BUILD } from './debug-build.js';
import { closeSession, makeSession, updateSession } from './session.js';
import { debug } from './utils/debug-logger.js';
import { parseEventHintOrCaptureContext } from './utils/prepareEvent.js';
import { getCombinedScopeData } from './utils/scopeData.js';
import { GLOBAL_OBJ } from './utils/worldwide.js';

function captureException(exception, hint) {
  return getCurrentScope().captureException(exception, parseEventHintOrCaptureContext(hint));
}
function captureMessage(message, captureContext) {
  const level = typeof captureContext === "string" ? captureContext : void 0;
  const hint = typeof captureContext !== "string" ? { captureContext } : void 0;
  return getCurrentScope().captureMessage(message, level, hint);
}
function captureEvent(event, hint) {
  return getCurrentScope().captureEvent(event, hint);
}
function setContext(name, context) {
  getIsolationScope().setContext(name, context);
}
function setExtras(extras) {
  getIsolationScope().setExtras(extras);
}
function setExtra(key, extra) {
  getIsolationScope().setExtra(key, extra);
}
function setTags(tags) {
  getIsolationScope().setTags(tags);
}
function setTag(key, value) {
  getIsolationScope().setTag(key, value);
}
function setAttributes(attributes) {
  getIsolationScope().setAttributes(attributes);
}
function setAttribute(key, value) {
  getIsolationScope().setAttribute(key, value);
}
function setUser(user) {
  getIsolationScope().setUser(user);
}
function setConversationId(conversationId) {
  getIsolationScope().setConversationId(conversationId);
}
function lastEventId() {
  return getIsolationScope().lastEventId();
}
async function flush(timeout) {
  const client = getClient();
  if (client) {
    return client.flush(timeout);
  }
  DEBUG_BUILD && debug.warn("Cannot flush events. No client defined.");
  return Promise.resolve(false);
}
async function close(timeout) {
  const client = getClient();
  if (client) {
    return client.close(timeout);
  }
  DEBUG_BUILD && debug.warn("Cannot flush events and disable SDK. No client defined.");
  return Promise.resolve(false);
}
function isInitialized() {
  return !!getClient();
}
function isEnabled() {
  const client = getClient();
  return client?.getOptions().enabled !== false && !!client?.getTransport();
}
function addEventProcessor(callback) {
  getIsolationScope().addEventProcessor(callback);
}
function startSession(context) {
  const isolationScope = getIsolationScope();
  const { user } = getCombinedScopeData(isolationScope, getCurrentScope());
  const { userAgent } = GLOBAL_OBJ.navigator || {};
  const session = makeSession({
    user,
    ...userAgent && { userAgent },
    ...context
  });
  const currentSession = isolationScope.getSession();
  if (currentSession?.status === "ok") {
    updateSession(currentSession, { status: "exited" });
  }
  endSession();
  isolationScope.setSession(session);
  return session;
}
function endSession() {
  const isolationScope = getIsolationScope();
  const currentScope = getCurrentScope();
  const session = currentScope.getSession() || isolationScope.getSession();
  if (session) {
    closeSession(session);
  }
  _sendSessionUpdate();
  isolationScope.setSession();
}
function _sendSessionUpdate() {
  const isolationScope = getIsolationScope();
  const client = getClient();
  const session = isolationScope.getSession();
  if (session && client) {
    client.captureSession(session);
  }
}
function captureSession(end = false) {
  if (end) {
    endSession();
    return;
  }
  _sendSessionUpdate();
}

export { addEventProcessor, captureEvent, captureException, captureMessage, captureSession, close, endSession, flush, isEnabled, isInitialized, lastEventId, setAttribute, setAttributes, setContext, setConversationId, setExtra, setExtras, setTag, setTags, setUser, startSession };
//# sourceMappingURL=exports.js.map
