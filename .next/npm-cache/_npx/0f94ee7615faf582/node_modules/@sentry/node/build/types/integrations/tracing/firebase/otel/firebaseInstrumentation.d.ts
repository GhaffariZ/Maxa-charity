import type { InstrumentationConfig, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
import { InstrumentationBase } from '@opentelemetry/instrumentation';
/**
 * Instrumentation for Firebase services, specifically Firestore.
 */
export declare class FirebaseInstrumentation extends InstrumentationBase<InstrumentationConfig> {
    constructor(config?: InstrumentationConfig);
    /**
     *
     * @protected
     */
    protected init(): InstrumentationNodeModuleDefinition | InstrumentationNodeModuleDefinition[] | void;
}
//# sourceMappingURL=firebaseInstrumentation.d.ts.map