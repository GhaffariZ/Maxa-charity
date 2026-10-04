import { MongoDBInstrumentation } from './vendored/instrumentation';
export declare const instrumentMongo: ((options?: unknown) => MongoDBInstrumentation) & {
    id: string;
};
/**
 * Adds Sentry tracing instrumentation for the [mongodb](https://www.npmjs.com/package/mongodb) library.
 *
 * For more information, see the [`mongoIntegration` documentation](https://docs.sentry.io/platforms/javascript/guides/node/configuration/integrations/mongo/).
 *
 * @example
 * ```javascript
 * const Sentry = require('@sentry/node');
 *
 * Sentry.init({
 *  integrations: [Sentry.mongoIntegration()],
 * });
 * ```
 */
export declare const mongoIntegration: () => import("@sentry/core").Integration & {
    name: "Mongo";
};
//# sourceMappingURL=index.d.ts.map