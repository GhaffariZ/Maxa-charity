import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
import { PgInstrumentationConfig } from './types';
export declare class PgInstrumentation extends InstrumentationBase<PgInstrumentationConfig> {
    constructor(config?: PgInstrumentationConfig);
    protected init(): InstrumentationNodeModuleDefinition[];
    private _patchPgClient;
    private _unpatchPgClient;
    private _getClientConnectPatch;
    private _getClientQueryPatch;
    private _getPoolConnectPatch;
}
//# sourceMappingURL=instrumentation.d.ts.map
