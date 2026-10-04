import type { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
export declare class KnexInstrumentation extends InstrumentationBase<InstrumentationConfig> {
    constructor(config?: InstrumentationConfig);
    init(): InstrumentationNodeModuleDefinition;
    private _getRunnerNodeModuleFileInstrumentation;
    private _getClientNodeModuleFileInstrumentation;
    private _createQueryWrapper;
    private _storeContext;
    private _ensureWrapped;
}
//# sourceMappingURL=instrumentation.d.ts.map