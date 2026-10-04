import { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
export declare class GenericPoolInstrumentation extends InstrumentationBase {
    private _isDisabled;
    constructor(config?: InstrumentationConfig);
    init(): InstrumentationNodeModuleDefinition[];
    private _acquirePatcher;
    private _poolWrapper;
    private _acquireWithCallbacksPatcher;
}
//# sourceMappingURL=instrumentation.d.ts.map
