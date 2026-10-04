import { InstrumentationConfig } from '@opentelemetry/instrumentation';
export declare enum KoaLayerType {
    ROUTER = "router",
    MIDDLEWARE = "middleware"
}
/**
 * Options available for the Koa Instrumentation (see [documentation](https://github.com/open-telemetry/opentelemetry-js/tree/main/packages/opentelemetry-Instrumentation-koa#koa-Instrumentation-options))
 */
export interface KoaInstrumentationConfig extends InstrumentationConfig {
    /** Ignore specific layers based on their type */
    ignoreLayersType?: KoaLayerType[];
}
//# sourceMappingURL=types.d.ts.map
