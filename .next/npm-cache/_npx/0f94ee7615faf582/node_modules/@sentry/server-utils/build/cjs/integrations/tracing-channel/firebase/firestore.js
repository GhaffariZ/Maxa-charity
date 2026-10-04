Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const net = require('node:net');
const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');

function _interopNamespaceDefault(e) {
  const n = Object.create(null, { [Symbol.toStringTag]: { value: 'Module' } });
  if (e) {
    for (const k in e) {
      n[k] = e[k];
    }
  }
  n.default = e;
  return n;
}

const net__namespace = /*#__PURE__*/_interopNamespaceDefault(net);

function startFirestoreSpan(spanName, reference) {
  return core.startInactiveSpan({
    name: `${spanName} ${reference.path}`,
    op: "db.query",
    kind: core.SPAN_KIND.CLIENT,
    attributes: {
      [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.firebase.orchestrion.firestore",
      [attributes.DB_OPERATION_NAME]: spanName,
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
      if (net__namespace.isIPv6(settings.host)) {
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
  const attributes$1 = {
    [attributes.DB_COLLECTION_NAME]: reference.path,
    [attributes.DB_NAMESPACE]: firestoreApp.name,
    [attributes.DB_SYSTEM_NAME]: "firebase.firestore",
    "firebase.firestore.type": reference.type,
    "firebase.firestore.options.projectId": firestoreOptions.projectId,
    "firebase.firestore.options.appId": firestoreOptions.appId,
    "firebase.firestore.options.messagingSenderId": firestoreOptions.messagingSenderId,
    "firebase.firestore.options.storageBucket": firestoreOptions.storageBucket
  };
  const { address, port } = getPortAndAddress(settings);
  if (address) {
    attributes$1[attributes.SERVER_ADDRESS] = address;
  }
  if (port) {
    attributes$1[attributes.SERVER_PORT] = port;
  }
  return attributes$1;
}

exports.getPortAndAddress = getPortAndAddress;
exports.startFirestoreSpan = startFirestoreSpan;
//# sourceMappingURL=firestore.js.map
