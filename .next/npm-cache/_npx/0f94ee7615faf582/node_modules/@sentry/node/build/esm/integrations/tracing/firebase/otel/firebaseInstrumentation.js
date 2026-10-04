import { InstrumentationBase } from '@opentelemetry/instrumentation';
import { SDK_VERSION } from '@sentry/core';
import { patchFirestore } from './patches/firestore.js';
import { patchFunctions } from './patches/functions.js';

const firestoreSupportedVersions = [">=3.0.0 <5"];
const functionsSupportedVersions = [">=6.0.0 <7"];
class FirebaseInstrumentation extends InstrumentationBase {
  constructor(config = {}) {
    super("@sentry/instrumentation-firebase", SDK_VERSION, config);
  }
  /**
   *
   * @protected
   */
  // eslint-disable-next-line @typescript-eslint/naming-convention
  init() {
    const modules = [];
    modules.push(patchFirestore(firestoreSupportedVersions, this._wrap, this._unwrap));
    modules.push(patchFunctions(functionsSupportedVersions, this._wrap, this._unwrap));
    return modules;
  }
}

export { FirebaseInstrumentation };
//# sourceMappingURL=firebaseInstrumentation.js.map
