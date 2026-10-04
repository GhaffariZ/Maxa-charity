import type { Span } from '@sentry/core';
import type { NormalizedRequest, NormalizedResponse } from '../types';
import type { RequestMetadata, ServiceExtension } from './ServiceExtension';
export declare class SqsServiceExtension implements ServiceExtension {
    requestPreSpanHook(request: NormalizedRequest): RequestMetadata;
    requestPostSpanHook(request: NormalizedRequest, span: Span): void;
    responseHook(response: NormalizedResponse, span: Span): void;
}
//# sourceMappingURL=sqs.d.ts.map