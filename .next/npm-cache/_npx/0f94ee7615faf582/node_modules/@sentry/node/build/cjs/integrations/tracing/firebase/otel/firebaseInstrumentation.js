Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const instrumentation = require('@opentelemetry/instrumentation');
const core = require('@sentry/core');
const firestore = require('./patches/firestore.js');
const functions = require('./patches/functions.js');

const firestoreSupportedVersions = [">=3.0.0 <5"];
const functionsSupportedVersions = [">=6.0.0 <7"];
class FirebaseInstrumentation extends instrumentation.InstrumentationBase {
  constructor(config = {}) {
    super("@sentry/instrumentation-firebase", core.SDK_VERSION, config);
  }
  /**
   *
   * @protected
   */
  // eslint-disable-next-line @typescript-eslint/naming-convention
  init() {
    const modules = [];
    modules.push(firestore.patchFirestore(firestoreSupportedVersions, this._wrap, this._unwrap));
    modules.push(functions.patchFunctions(functionsSupportedVersions, this._wrap, this._unwrap));
    return modules;
  }
}

exports.FirebaseInstrumentation = FirebaseInstrumentation;
//# sourceMappingURL=firebaseInstrumentation.js.map
