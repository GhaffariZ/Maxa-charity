import * as net from 'node:net';
import { DB_OPERATION_NAME, DB_NAMESPACE, DB_COLLECTION_NAME, DB_SYSTEM_NAME, SERVER_ADDRESS, SERVER_PORT } from '@sentry/conventions/attributes';
import { startInactiveSpan, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';

function startFirestoreSpan(spanName, reference) {
  return startInactiveSpan({
    name: `${spanName} ${reference.path}`,
    op: "db.query",
    kind: SPAN_KIND.CLIENT,
    attributes: {
      [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.firebase.orchestrion.firestore",
      [DB_OPERATION_NAME]: spanName,
      ...buildAttributes(reference)
    }
  });
}
function getPortAndAddress(settings) {
  let address;
  let port;
  if (typeof settings.host === "string") {
    if (settings.host.startsWith("[")) {
      if (settings.host.endsWith("]")) {
        address = settings.host.replace(/^\[|\]$/g, "");
      } else if (settings.host.includes("]:")) {
        const lastColonIndex = settings.host.lastIndexOf(":");
        if (lastColonIndex !== -1) {
          address = settings.host.slice(1, lastColonIndex).replace(/^\[|\]$/g, "");
          port = settings.host.slice(lastColonIndex + 1);
        }
      }
    } else {
      if (net.isIPv6(settings.host)) {
        address = settings.host;
      } else {
        const lastColonIndex = settings.host.lastIndexOf(":");
        if (lastColonIndex !== -1) {
          address = settings.host.slice(0, lastColonIndex);
          port = settings.host.slice(lastColonIndex + 1);
        } else {
          address = settings.host;
        }
      }
    }
  }
  return {
    address,
    port: port ? parseInt(port, 10) : void 0
  };
}
function buildAttributes(reference) {
  const firestoreApp = reference.firestore.app;
  const firestoreOptions = firestoreApp.options;
  const settings = reference.firestore.toJSON()?.settings || {};
  const attributes = {
    [DB_COLLECTION_NAME]: reference.path,
    [DB_NAMESPACE]: firestoreApp.name,
    [DB_SYSTEM_NAME]: "firebase.firestore",
    "firebase.firestore.type": reference.type,
    "firebase.firestore.options.projectId": firestoreOptions.projectId,
    "firebase.firestore.options.appId": firestoreOptions.appId,
    "firebase.firestore.options.messagingSenderId": firestoreOptions.messagingSenderId,
    "firebase.firestore.options.storageBucket": firestoreOptions.storageBucket
  };
  const { address, port } = getPortAndAddress(settings);
  if (address) {
    attributes[SERVER_ADDRESS] = address;
  }
  if (port) {
    attributes[SERVER_PORT] = port;
  }
  return attributes;
}

export { getPortAndAddress, startFirestoreSpan };
//# sourceMappingURL=firestore.js.map
