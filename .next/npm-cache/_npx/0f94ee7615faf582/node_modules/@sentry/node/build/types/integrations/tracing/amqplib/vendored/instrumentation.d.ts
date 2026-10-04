import type { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
export declare class AmqplibInstrumentation extends InstrumentationBase<InstrumentationConfig> {
    constructor(config?: InstrumentationConfig);
    protected init(): InstrumentationNodeModuleDefinition;
    private patchConnect;
    private unpatchConnect;
    private patchChannelModel;
    private unpatchChannelModel;
}
//# sourceMappingURL=instrumentation.d.ts.map