import type { FastifyReply, FastifyRequest } from './types';
export declare const INTEGRATION_NAME: "Fastify";
/**
 * Default function to determine if an error should be sent to Sentry
 *
 * 3xx and 4xx errors are not sent by default.
 */
export declare function defaultShouldHandleError(_error: Error, _request: FastifyRequest, reply: FastifyReply): boolean;
//# sourceMappingURL=utils.d.ts.map