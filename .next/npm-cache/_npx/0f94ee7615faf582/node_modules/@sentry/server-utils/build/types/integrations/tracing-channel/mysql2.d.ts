/**
 * Orchestrion-driven mysql2 integration.
 *
 * Adds Sentry tracing instrumentation for the [mysql2](https://www.npmjs.com/package/mysql2) library
 * via diagnostics-channel injection. See {@link instrumentMysql2} for how the two mysql2 version
 * ranges are covered.
 *
 * Known limitation vs. the OTel integration it replaces: the callback-less streaming form
 * (`connection.query(sql).on('result', ...)`) is not traced — see the `mysql2` orchestrion config for
 * why. The callback and promise forms (the common case) are fully instrumented.
 */
export declare const mysql2ChannelIntegration: () => import("@sentry/core").Integration & {
    name: "Mysql2";
};
//# sourceMappingURL=mysql2.d.ts.map