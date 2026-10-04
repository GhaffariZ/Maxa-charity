import type { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
export declare class KafkaJsInstrumentation extends InstrumentationBase<InstrumentationConfig> {
    constructor(config?: InstrumentationConfig);
    protected init(): InstrumentationNodeModuleDefinition;
    private _getConsumerPatch;
    private _getProducerPatch;
    private _getConsumerRunPatch;
    private _getConsumerEachMessagePatch;
    private _getConsumerEachBatchPatch;
    private _getProducerTransactionPatch;
    private _getSendBatchPatch;
    private _getSendPatch;
}
//# sourceMappingURL=instrumentation.d.ts.map