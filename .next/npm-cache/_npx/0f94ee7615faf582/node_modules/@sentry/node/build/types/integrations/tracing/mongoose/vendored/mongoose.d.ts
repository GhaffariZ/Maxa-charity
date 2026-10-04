import { type InstrumentationConfig, InstrumentationBase, type InstrumentationModuleDefinition } from '@opentelemetry/instrumentation';
export declare const _STORED_PARENT_SPAN: unique symbol;
export declare const _ALREADY_INSTRUMENTED: unique symbol;
export declare class MongooseInstrumentation extends InstrumentationBase<InstrumentationConfig> {
    constructor(config?: InstrumentationConfig);
    protected init(): InstrumentationModuleDefinition;
    private patch;
    private unpatch;
    private patchAggregateExec;
    private patchQueryExec;
    private patchOnModelMethods;
    private _patchDocumentUpdateMethods;
    private patchModelStatic;
    private patchModelAggregate;
    private patchAndCaptureSpanContext;
    private _handleResponse;
}
//# sourceMappingURL=mongoose.d.ts.map