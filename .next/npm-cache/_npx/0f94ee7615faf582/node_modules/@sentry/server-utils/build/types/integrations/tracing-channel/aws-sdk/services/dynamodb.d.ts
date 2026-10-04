import type { Span } from '@sentry/core';
import type { NormalizedRequest, NormalizedResponse } from '../types';
import type { RequestMetadata, ServiceExtension } from './ServiceExtension';
export declare class DynamodbServiceExtension implements ServiceExtension {
    requestPreSpanHook(normalizedRequest: NormalizedRequest): RequestMetadata;
    responseHook(response: NormalizedResponse, span: Span): void;
}
//# sourceMappingURL=dynamodb.d.ts.map