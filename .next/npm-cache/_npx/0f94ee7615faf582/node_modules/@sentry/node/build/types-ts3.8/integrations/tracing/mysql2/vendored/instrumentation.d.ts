import { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
export declare class MySQL2Instrumentation extends InstrumentationBase<InstrumentationConfig> {
    constructor(config?: InstrumentationConfig);
    protected init(): InstrumentationNodeModuleDefinition[];
    private _patchQuery;
    private _patchCallbackQuery;
}
//# sourceMappingURL=instrumentation.d.ts.map
