import type { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
export declare class MySQLInstrumentation extends InstrumentationBase<InstrumentationConfig> {
    constructor(config?: InstrumentationConfig);
    protected init(): InstrumentationNodeModuleDefinition[];
    private _patchCreateConnection;
    private _patchCreatePool;
    private _patchCreatePoolCluster;
    private _patchGetConnection;
    private _getConnectionCallbackPatchFn;
    private _patchQuery;
    private _patchCallbackQuery;
}
//# sourceMappingURL=instrumentation.d.ts.map