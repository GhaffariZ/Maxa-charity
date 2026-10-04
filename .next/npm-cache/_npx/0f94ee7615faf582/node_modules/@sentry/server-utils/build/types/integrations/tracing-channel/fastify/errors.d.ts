import type { FastifyReply, FastifyRequest } from './types';
/**
 * Subscribe to the Fastify v5 error diagnostics channel.
 */
export declare function subscribeToFastifyErrorChannel(): void;
/**
 * Handle a Fastify error, and possibly send it to Sentry.
 */
export declare function handleFastifyError(this: {
    diagnosticsChannelExists?: boolean;
}, error: Error, request: FastifyRequest, reply: FastifyReply, handlerOrigin: 'diagnostics-channel' | 'onError-hook'): void;
//# sourceMappingURL=errors.d.ts.map