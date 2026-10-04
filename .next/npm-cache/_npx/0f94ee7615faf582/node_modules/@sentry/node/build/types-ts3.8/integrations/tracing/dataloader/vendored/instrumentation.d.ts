import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
export declare class DataloaderInstrumentation extends InstrumentationBase {
    constructor(config?: {});
    protected init(): InstrumentationNodeModuleDefinition[];
    private _wrapBatchLoadFn;
    private _getPatchedConstructor;
    private _patchLoad;
    private _getPatchedLoad;
    private _patchLoadMany;
    private _getPatchedLoadMany;
    private _patchPrime;
    private _getPatchedPrime;
    private _patchClear;
    private _getPatchedClear;
    private _patchClearAll;
    private _getPatchedClearAll;
}
//# sourceMappingURL=instrumentation.d.ts.map
