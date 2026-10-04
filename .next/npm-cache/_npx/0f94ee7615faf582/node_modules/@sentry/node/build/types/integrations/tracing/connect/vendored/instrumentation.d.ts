import type { HandleFunction, NextFunction, Server, Use } from './internal-types';
import type { Span } from '@sentry/core';
import type { InstrumentationConfig } from '@opentelemetry/instrumentation';
import { InstrumentationBase, InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
/** Connect instrumentation for OpenTelemetry */
export declare class ConnectInstrumentation extends InstrumentationBase {
    constructor(config?: InstrumentationConfig);
    init(): InstrumentationNodeModuleDefinition[];
    private _patchApp;
    private _patchConstructor;
    _patchNext(next: NextFunction, span: Span, finishSpan: () => void): NextFunction;
    _startSpan(routeName: string, middleWare: HandleFunction): Span;
    _patchMiddleware(routeName: string, middleWare: HandleFunction): HandleFunction;
    _patchUse(original: Server['use']): Use;
    _patchHandle(original: Server['handle']): Server['handle'];
    _patchOut(out: NextFunction, completeStack: () => void): NextFunction;
}
//# sourceMappingURL=instrumentation.d.ts.map