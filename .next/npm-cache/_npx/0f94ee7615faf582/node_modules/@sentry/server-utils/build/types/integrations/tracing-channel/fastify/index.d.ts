import type { FastifyReply, FastifyRequest } from './types';
import { handleFastifyError as _handleFastifyError } from './errors';
/**
 * Options for the Fastify integration.
 *
 * `shouldHandleError` - Callback method deciding whether error should be captured and sent to Sentry
 * This is used on Fastify v5 where Sentry handles errors in the diagnostics channel.
 * Fastify v3 and v4 use `setupFastifyErrorHandler` instead.
 *
 * @example
 *
 * ```javascript
 * Sentry.init({
 *   integrations: [
 *     Sentry.fastifyIntegration({
 *       shouldHandleError(_error, _request, reply) {
 *         return reply.statusCode >= 500;
 *       },
 *     });
 *   },
 * });
 * ```
 *
 */
interface FastifyIntegrationOptions {
    /**
     * Callback method deciding whether error should be captured and sent to Sentry
     * This is used on Fastify v5 where Sentry handles errors in the diagnostics channel.
     * Fastify v3 and v4 use `setupFastifyErrorHandler` instead.
     *
     * @param error Captured Fastify error
     * @param request Fastify request (or any object containing at least method, routeOptions.url, and routerPath)
     * @param reply Fastify reply (or any object containing at least statusCode)
     */
    shouldHandleError: (error: Error, request: FastifyRequest, reply: FastifyReply) => boolean;
}
/**
 * Adds Sentry tracing instrumentation for [Fastify](https://fastify.dev/).
 * This integration supports Fastify v5 only.
 *
 * For more information, see the [fastify documentation](https://docs.sentry.io/platforms/javascript/guides/fastify/).
 *
 * @example
 * ```javascript
 * const Sentry = require('@sentry/node');
 *
 * Sentry.init({
 *   integrations: [Sentry.fastifyIntegration()],
 * })
 * ```
 */
export declare const fastifyIntegration: (args_0?: Partial<FastifyIntegrationOptions> | undefined) => import("@sentry/core").Integration & {
    name: "Fastify";
};
/**
 * @deprecated This export is deprecated and will not longer be exposed in the next major version.
 */
export declare const instrumentFastify: (() => void) & {
    id: string;
};
/**
 * @deprecated This export is deprecated and will not longer be exposed in the next major version.
 */
export declare const handleFastifyError: typeof _handleFastifyError;
export {};
//# sourceMappingURL=index.d.ts.map