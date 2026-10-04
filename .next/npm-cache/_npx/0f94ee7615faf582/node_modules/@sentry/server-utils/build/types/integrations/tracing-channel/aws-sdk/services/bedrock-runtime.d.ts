import type { Span } from '@sentry/core';
import type { NormalizedRequest, NormalizedResponse } from '../types';
import type { RequestMetadata, ServiceExtension } from './ServiceExtension';
export declare class BedrockRuntimeServiceExtension implements ServiceExtension {
    requestPreSpanHook(request: NormalizedRequest): RequestMetadata;
    responseHook(response: NormalizedResponse, span: Span): void;
    private _requestPreSpanHookConverse;
    private _requestPreSpanHookInvokeModel;
    private _responseHookConverse;
    private _responseHookConverseStream;
    private _responseHookInvokeModel;
    private _responseHookInvokeModelWithResponseStream;
}
//# sourceMappingURL=bedrock-runtime.d.ts.map