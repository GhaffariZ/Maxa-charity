import type { InstrumentationConfig } from '@opentelemetry/instrumentation';
export interface PgInstrumentationConfig extends InstrumentationConfig {
    /**
     * If true, `pg.connect` and `pg-pool.connect` spans will not be created.
     * Query spans are still recorded.
     *
     * @default false
     */
    ignoreConnectSpans?: boolean;
}
//# sourceMappingURL=types.d.ts.map