import type { Span } from '@sentry/core';
import type { NormalizedRequest, NormalizedResponse, RequestMetadata } from '../types';
export type { RequestMetadata };
export interface ServiceExtension {
    requestPreSpanHook: (request: NormalizedRequest) => RequestMetadata;
    requestPostSpanHook?: (request: NormalizedRequest, span: Span) => void;
    responseHook?: (response: NormalizedResponse, span: Span) => void;
}
//# sourceMappingURL=ServiceExtension.d.ts.map