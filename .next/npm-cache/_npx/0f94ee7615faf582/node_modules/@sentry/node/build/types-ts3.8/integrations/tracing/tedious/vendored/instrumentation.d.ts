import { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
export declare class TediousInstrumentation extends InstrumentationBase<InstrumentationConfig> {
    static readonly COMPONENT = "tedious";
    constructor(config?: InstrumentationConfig);
    protected init(): InstrumentationNodeModuleDefinition[];
    private _patchConnect;
    private _patchQuery;
    private _patchCallbackQuery;
}
//# sourceMappingURL=instrumentation.d.ts.map
