/**
 * Auto-instrument the [mysql2](https://www.npmjs.com/package/mysql2) library via its native
 * `node:diagnostics_channel` tracing channels (mysql2 >= 3.20.0).
 *
 * On older mysql2 versions the channels are never published to, so this integration is inert and
 * the vendored OTel instrumentation (gated to `< 3.20.0`) handles instrumentation instead.
 */
export declare const mysql2Integration: () => import("@sentry/core").Integration & {
    name: string;
};
//# sourceMappingURL=index.d.ts.map