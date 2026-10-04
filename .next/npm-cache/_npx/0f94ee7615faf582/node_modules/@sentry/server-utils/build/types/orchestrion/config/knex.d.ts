import type { InstrumentationConfig } from '..';
export declare const knexConfig: InstrumentationConfig[];
export declare const knexChannels: {
    readonly KNEX_QUERY: "orchestrion:knex:query";
    readonly KNEX_QUERY_BUILDER: "orchestrion:knex:queryBuilder";
    readonly KNEX_SCHEMA_BUILDER: "orchestrion:knex:schemaBuilder";
    readonly KNEX_RAW: "orchestrion:knex:raw";
};
export declare const knexSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=knex.d.ts.map