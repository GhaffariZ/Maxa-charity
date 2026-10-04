import { toSubscribeInjections } from './subscribe-injection.js';

const FIRESTORE_VERSION_RANGE = ">=3.0.0 <5";
const FIRESTORE_FILE = /dist\/lite\/(index|common-[^/]+)\.node\.(cjs\.js|mjs)$/;
const FIRESTORE_OPERATIONS = [
  { functionName: "addDoc", channelName: "add-doc" },
  { functionName: "getDocs", channelName: "get-docs" },
  { functionName: "setDoc", channelName: "set-doc" },
  { functionName: "deleteDoc", channelName: "delete-doc" }
];
const FUNCTIONS_VERSION_RANGE = ">=6.0.0 <7";
const FUNCTIONS_TRIGGERS = [
  { file: "lib/v2/providers/https.js", functionName: "onRequest", channelName: "http-request" },
  { file: "lib/v2/providers/https.js", functionName: "onCall", channelName: "http-call" },
  { file: "lib/v2/providers/firestore.js", functionName: "onDocumentCreated", channelName: "firestore-created" },
  {
    file: "lib/v2/providers/firestore.js",
    functionName: "onDocumentCreatedWithAuthContext",
    channelName: "firestore-created"
  },
  { file: "lib/v2/providers/firestore.js", functionName: "onDocumentUpdated", channelName: "firestore-updated" },
  {
    file: "lib/v2/providers/firestore.js",
    functionName: "onDocumentUpdatedWithAuthContext",
    channelName: "firestore-updated"
  },
  { file: "lib/v2/providers/firestore.js", functionName: "onDocumentDeleted", channelName: "firestore-deleted" },
  {
    file: "lib/v2/providers/firestore.js",
    functionName: "onDocumentDeletedWithAuthContext",
    channelName: "firestore-deleted"
  },
  { file: "lib/v2/providers/firestore.js", functionName: "onDocumentWritten", channelName: "firestore-written" },
  {
    file: "lib/v2/providers/firestore.js",
    functionName: "onDocumentWrittenWithAuthContext",
    channelName: "firestore-written"
  },
  { file: "lib/v2/providers/scheduler.js", functionName: "onSchedule", channelName: "scheduler" },
  { file: "lib/v2/providers/storage.js", functionName: "onObjectFinalized", channelName: "storage-finalized" },
  { file: "lib/v2/providers/storage.js", functionName: "onObjectArchived", channelName: "storage-archived" },
  { file: "lib/v2/providers/storage.js", functionName: "onObjectDeleted", channelName: "storage-deleted" },
  {
    file: "lib/v2/providers/storage.js",
    functionName: "onObjectMetadataUpdated",
    channelName: "storage-metadata-updated"
  }
];
const firebaseConfig = [
  ...FIRESTORE_OPERATIONS.map(({ functionName, channelName }) => ({
    channelName,
    module: { name: "@firebase/firestore", versionRange: FIRESTORE_VERSION_RANGE, filePath: FIRESTORE_FILE },
    functionQuery: { functionName, kind: "Auto" }
  })),
  ...FUNCTIONS_TRIGGERS.map(({ file, functionName, channelName }) => ({
    channelName,
    module: { name: "firebase-functions", versionRange: FUNCTIONS_VERSION_RANGE, filePath: file },
    functionQuery: { functionName, kind: "Sync" }
  }))
];
const firebaseChannels = {
  FIREBASE_FIRESTORE_ADD_DOC: "orchestrion:@firebase/firestore:add-doc",
  FIREBASE_FIRESTORE_GET_DOCS: "orchestrion:@firebase/firestore:get-docs",
  FIREBASE_FIRESTORE_SET_DOC: "orchestrion:@firebase/firestore:set-doc",
  FIREBASE_FIRESTORE_DELETE_DOC: "orchestrion:@firebase/firestore:delete-doc",
  FIREBASE_FUNCTIONS_HTTP_REQUEST: "orchestrion:firebase-functions:http-request",
  FIREBASE_FUNCTIONS_HTTP_CALL: "orchestrion:firebase-functions:http-call",
  FIREBASE_FUNCTIONS_FIRESTORE_CREATED: "orchestrion:firebase-functions:firestore-created",
  FIREBASE_FUNCTIONS_FIRESTORE_UPDATED: "orchestrion:firebase-functions:firestore-updated",
  FIREBASE_FUNCTIONS_FIRESTORE_DELETED: "orchestrion:firebase-functions:firestore-deleted",
  FIREBASE_FUNCTIONS_FIRESTORE_WRITTEN: "orchestrion:firebase-functions:firestore-written",
  FIREBASE_FUNCTIONS_SCHEDULER: "orchestrion:firebase-functions:scheduler",
  FIREBASE_FUNCTIONS_STORAGE_FINALIZED: "orchestrion:firebase-functions:storage-finalized",
  FIREBASE_FUNCTIONS_STORAGE_ARCHIVED: "orchestrion:firebase-functions:storage-archived",
  FIREBASE_FUNCTIONS_STORAGE_DELETED: "orchestrion:firebase-functions:storage-deleted",
  FIREBASE_FUNCTIONS_STORAGE_METADATA_UPDATED: "orchestrion:firebase-functions:storage-metadata-updated"
};
const firebaseSubscribeInjection = toSubscribeInjections(firebaseConfig);

export { firebaseChannels, firebaseConfig, firebaseSubscribeInjection };
//# sourceMappingURL=firebase.js.map
