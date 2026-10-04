import type { Span } from '@sentry/core';
import type { NormalizedRequest, NormalizedResponse } from '../types';
import type { RequestMetadata, ServiceExtension } from './ServiceExtension';
export declare class SecretsManagerServiceExtension implements ServiceExtension {
    requestPreSpanHook(request: NormalizedRequest): RequestMetadata;
    responseHook(response: NormalizedResponse, span: Span): void;
}
//# sourceMappingURL=secretsmanager.d.ts.map