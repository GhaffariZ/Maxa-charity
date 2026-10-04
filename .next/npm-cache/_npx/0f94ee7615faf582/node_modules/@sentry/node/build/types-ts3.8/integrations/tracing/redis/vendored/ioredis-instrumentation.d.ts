import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
import { IORedisInstrumentationConfig } from './types';
export declare class IORedisInstrumentation extends InstrumentationBase<IORedisInstrumentationConfig> {
    constructor(config?: IORedisInstrumentationConfig);
    protected init(): InstrumentationNodeModuleDefinition[];
    private _patchSendCommand;
    private _patchConnection;
    private _callResponseHook;
}
//# sourceMappingURL=ioredis-instrumentation.d.ts.map
