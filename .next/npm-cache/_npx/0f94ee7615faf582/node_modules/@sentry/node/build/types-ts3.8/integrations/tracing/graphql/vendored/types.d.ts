import { InstrumentationConfig } from '@opentelemetry/instrumentation';
export interface GraphQLInstrumentationConfig extends InstrumentationConfig {
    /**
     * Do not create spans for resolvers.
     *
     * @default false
     */
    ignoreResolveSpans?: boolean;
    /**
     * Don't create spans for the execution of the default resolver on object properties.
     *
     * When a resolver function is not defined on the schema for a field, graphql will
     * use the default resolver which just looks for a property with that name on the object.
     * If the property is not a function, it's not very interesting to trace.
     * This option can reduce noise and number of spans created.
     *
     * @default false
     */
    ignoreTrivialResolveSpans?: boolean;
    /**
     * If this is enabled, a `http.server` root span containing the execute span will automatically be renamed
     * to include the operation name.
     *
     * @default false
     */
    useOperationNameForRootSpan?: boolean;
}
type RequireSpecificKeys<T, K extends keyof T> = T & {
    [P in K]-?: T[P];
};
export type GraphQLInstrumentationParsedConfig = RequireSpecificKeys<GraphQLInstrumentationConfig, 'ignoreResolveSpans'>;
export {};
//# sourceMappingURL=types.d.ts.map
