import { SpanAttributes } from '@sentry/core';
import { FormatFunction } from './mysql2-types';
interface QueryOptions {
    sql: string;
    values?: any | any[] | {
        [param: string]: any;
    };
}
interface Query {
    sql: string;
}
interface Config {
    host?: string;
    port?: number;
    database?: string;
    user?: string;
    connectionConfig?: Config;
}
export declare function getConnectionAttributes(config: Config): SpanAttributes;
export declare function getQueryText(query: string | Query | QueryOptions, format?: FormatFunction, values?: any[]): string;
export declare function getSpanName(query: string | Query | QueryOptions): string;
export declare const once: (fn: Function) => (...args: unknown[]) => any;
export declare function getConnectionPrototypeToInstrument(connection: any): any;
export {};
//# sourceMappingURL=utils.d.ts.map
