Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const net = require('node:net');
const instrumentation = require('@opentelemetry/instrumentation');
const InstrumentationNodeModuleFile = require('../../../InstrumentationNodeModuleFile.js');
const attributes = require('@sentry/conventions/attributes');
const core = require('@sentry/core');

function patchFirestore(firestoreSupportedVersions, wrap, unwrap) {
  const moduleFirestoreCJS = new instrumentation.InstrumentationNodeModuleDefinition(
    "@firebase/firestore",
    firestoreSupportedVersions,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (moduleExports) => wrapMethods(moduleExports, wrap, unwrap)
  );
  const files = [
    "@firebase/firestore/dist/lite/index.node.cjs.js",
    "@firebase/firestore/dist/lite/index.node.mjs.js",
    "@firebase/firestore/dist/lite/index.rn.esm2017.js",
    "@firebase/firestore/dist/lite/index.cjs.js"
  ];
  for (const file of files) {
    moduleFirestoreCJS.files.push(
      new InstrumentationNodeModuleFile.InstrumentationNodeModuleFile(
        file,
        firestoreSupportedVersions,
        (moduleExports) => wrapMethods(moduleExports, wrap, unwrap),
        (moduleExports) => unwrapMethods(moduleExports, unwrap)
      )
    );
  }
  return moduleFirestoreCJS;
}
function wrapMethods(moduleExports, wrap, unwrap) {
  unwrapMethods(moduleExports, unwrap);
  wrap(moduleExports, "addDoc", patchAddDoc());
  wrap(moduleExports, "getDocs", patchGetDocs());
  wrap(moduleExports, "setDoc", patchSetDoc());
  wrap(moduleExports, "deleteDoc", patchDeleteDoc());
  return moduleExports;
}
function unwrapMethods(moduleExports, unwrap) {
  for (const method of ["addDoc", "getDocs", "setDoc", "deleteDoc"]) {
    if (instrumentation.isWrapped(moduleExports[method])) {
      unwrap(moduleExports, method);
    }
  }
  return moduleExports;
}
function patchAddDoc() {
  return function addDoc(original) {
    return function(reference, data) {
      return startFirestoreSpan("addDoc", reference, () => original(reference, data));
    };
  };
}
function patchDeleteDoc() {
  return function deleteDoc(original) {
    return function(reference) {
      return startFirestoreSpan("deleteDoc", reference.parent || reference, () => original(reference));
    };
  };
}
function patchGetDocs() {
  return function getDocs(original) {
    return function(reference) {
      return startFirestoreSpan("getDocs", reference, () => original(reference));
    };
  };
}
function patchSetDoc() {
  return function setDoc(original) {
    return function(reference, data, options) {
      return startFirestoreSpan("setDoc", reference.parent || reference, () => {
        return typeof options !== "undefined" ? original(reference, data, options) : original(reference, data);
      });
    };
  };
}
function startFirestoreSpan(spanName, reference, callback) {
  return core.startSpan(
    {
      name: `${spanName} ${reference.path}`,
      op: "db.query",
      kind: core.SPAN_KIND.CLIENT,
      attributes: {
        [core.SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: "auto.firebase.otel.firestore",
        [attributes.DB_OPERATION_NAME]: spanName,
        ...buildAttributes(reference)
      }
    },
    callback
  );
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
  const json = reference.firestore.toJSON() || {};
  const settings = json.settings || {};
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
exports.patchFirestore = patchFirestore;
//# sourceMappingURL=firestore.js.map
