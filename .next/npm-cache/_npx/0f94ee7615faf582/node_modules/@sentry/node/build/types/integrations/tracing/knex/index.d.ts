import { KnexInstrumentation } from './vendored/instrumentation';
export declare const instrumentKnex: ((options?: unknown) => KnexInstrumentation) & {
    id: string;
};
/**
 * Knex integration
 *
 * Capture tracing data for [Knex](https://knexjs.org/).
 *
 * @example
 * ```javascript
 * import * as Sentry from '@sentry/node';
 *
 * Sentry.init({
 *  integrations: [Sentry.knexIntegration()],
 * });
 * ```
 */
export declare const knexIntegration: () => import("@sentry/core").Integration & {
    name: "Knex";
};
//# sourceMappingURL=index.d.ts.map