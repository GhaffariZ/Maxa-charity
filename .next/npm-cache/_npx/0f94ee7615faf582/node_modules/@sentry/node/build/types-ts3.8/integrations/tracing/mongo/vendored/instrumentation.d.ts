import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
import { InstrumentationConfig } from '@opentelemetry/instrumentation';
/** mongodb instrumentation plugin */
export declare class MongoDBInstrumentation extends InstrumentationBase<InstrumentationConfig> {
    constructor(config?: InstrumentationConfig);
    init(): InstrumentationNodeModuleDefinition[];
    private _getV3ConnectionPatches;
    private _getV4ConnectionPoolPatches;
    private _getV4ConnectionPatches;
}
//# sourceMappingURL=instrumentation.d.ts.map
