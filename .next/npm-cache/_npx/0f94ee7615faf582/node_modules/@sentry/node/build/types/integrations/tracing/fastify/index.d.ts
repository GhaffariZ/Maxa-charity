import type { Integration } from '@sentry/core';
import type { FastifyMinimal, FastifyReply, FastifyRequest } from './types';
import { FastifyInstrumentationV3 } from './v3/instrumentation';
import { instrumentFastify } from '@sentry/server-utils';
export { instrumentFastify };
/**
 * Options for the Fastify integration.
 *
 * `shouldHandleError` - Callback method deciding whether error should be captured and sent to Sentry
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
     *
     * @example
     *
     * If using TypeScript, you can cast the request and reply to get full type safety.
     *
     * ```typescript
     * import type { FastifyRequest, FastifyReply } from 'fastify';
     *
     * Sentry.fastifyIntegration({
     *   shouldHandleError(error, minimalRequest, minimalReply) {
     *     const request = minimalRequest as FastifyRequest;
     *     const reply = minimalReply as FastifyReply;
     *     return reply.statusCode >= 500;
     *   },
     * });
     * ```
     */
    shouldHandleError: (error: Error, request: FastifyRequest, reply: FastifyReply) => boolean;
}
interface FastifyHandlerOptions {
    /**
     * Callback method deciding whether error should be captured and sent to Sentry
     *
     * @param error Captured Fastify error
     * @param request Fastify request (or any object containing at least method, routeOptions.url, and routerPath)
     * @param reply Fastify reply (or any object containing at least statusCode)
     *
     * @deprecated Configure `shouldHandleError` on `fastifyIntegration()` rather than here, where it
     * applies to every supported Fastify version. This option will be removed in v11.
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
     *     }),
     *   ],
     * });
     * ```
     */
    shouldHandleError: (error: Error, request: FastifyRequest, reply: FastifyReply) => boolean;
}
export declare const instrumentFastifyV3: ((options?: unknown) => FastifyInstrumentationV3) & {
    id: string;
};
/**
 * Adds Sentry tracing instrumentation for [Fastify](https://fastify.dev/).
 *
 * If you also want to capture errors, you need to call `setupFastifyErrorHandler(app)` after you set up your Fastify server.
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
export declare const fastifyIntegration: (options?: Partial<FastifyIntegrationOptions> | undefined) => Integration & {
    name: string;
};
/**
 * Add an Fastify error handler to capture errors to Sentry.
 *
 * @param fastify The Fastify instance to which to add the error handler
 * @param options Configuration options for the handler
 *
 * @example
 * ```javascript
 * const Sentry = require('@sentry/node');
 * const Fastify = require("fastify");
 *
 * const app = Fastify();
 *
 * Sentry.setupFastifyErrorHandler(app);
 *
 * // Add your routes, etc.
 *
 * app.listen({ port: 3000 });
 * ```
 */
export declare function setupFastifyErrorHandler(fastify: FastifyMinimal, options?: Partial<FastifyHandlerOptions>): void;
//# sourceMappingURL=index.d.ts.map