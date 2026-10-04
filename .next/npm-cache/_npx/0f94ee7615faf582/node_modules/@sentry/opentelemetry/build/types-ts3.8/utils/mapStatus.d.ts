import { SpanAttributes, SpanStatus } from '@sentry/core';
import { AbstractSpan } from '../types';
export declare const isStatusErrorMessageValid: (message: string) => boolean;
/**
 * Get a Sentry span status from an otel span.
 */
export declare function mapStatus(span: AbstractSpan): SpanStatus;
export declare function inferStatusFromAttributes(attributes: SpanAttributes): SpanStatus | undefined;
//# sourceMappingURL=mapStatus.d.ts.map
