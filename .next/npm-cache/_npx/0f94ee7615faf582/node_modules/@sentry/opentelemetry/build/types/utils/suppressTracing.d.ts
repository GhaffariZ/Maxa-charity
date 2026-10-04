import type { Scope } from '@sentry/core';
/** Suppress tracing in the given callback, ensuring no spans are generated inside of it. */
export declare function suppressTracing<T>(callback: () => T): T;
export declare function isTracingSuppressed(scope?: Scope): boolean;
//# sourceMappingURL=suppressTracing.d.ts.map