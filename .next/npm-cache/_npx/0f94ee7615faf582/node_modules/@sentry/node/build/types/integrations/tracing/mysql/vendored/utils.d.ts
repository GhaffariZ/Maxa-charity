import type { ConnectionConfig, PoolConfig, Query, QueryOptions } from './mysql-types';
export declare function getConfig(config: ConnectionConfig | PoolConfig | undefined): {
    host: string | undefined;
    port: number | undefined;
    database: string | undefined;
    user: string | undefined;
};
export declare function getJDBCString(host: string | undefined, port: number | undefined, database: string | undefined): string;
/**
 * @returns the database query being executed.
 */
export declare function getDbQueryText(query: string | Query | QueryOptions): string;
/**
 * The span name SHOULD be set to a low cardinality value
 * representing the statement executed on the database.
 *
 * TODO: revisit span name based on https://github.com/open-telemetry/semantic-conventions/blob/v1.33.0/docs/database/database-spans.md#name
 *
 * @returns SQL statement without variable arguments or SQL verb
 */
export declare function getSpanName(query: string | Query | QueryOptions): string;
//# sourceMappingURL=utils.d.ts.map