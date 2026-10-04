export type MongoInternalCommand = {
    findandmodify: boolean;
    createIndexes: boolean;
    count: boolean;
    aggregate: boolean;
    ismaster: boolean;
    indexes?: unknown[];
    query?: Record<string, unknown>;
    limit?: number;
    q?: Record<string, unknown>;
    u?: Record<string, unknown>;
};
export type CursorState = {
    cmd: MongoInternalCommand;
} & Record<string, unknown>;
export type WireProtocolInternal = {
    insert: (server: MongoInternalTopology, ns: string, ops: unknown[], options: unknown | Function, callback?: Function) => unknown;
    update: (server: MongoInternalTopology, ns: string, ops: unknown[], options: unknown | Function, callback?: Function) => unknown;
    remove: (server: MongoInternalTopology, ns: string, ops: unknown[], options: unknown | Function, callback?: Function) => unknown;
    killCursors: (server: MongoInternalTopology, ns: string, cursorState: CursorState, callback: Function) => unknown;
    getMore: (server: MongoInternalTopology, ns: string, cursorState: CursorState, batchSize: number, options: unknown | Function, callback?: Function) => unknown;
    query: (server: MongoInternalTopology, ns: string, cmd: MongoInternalCommand, cursorState: CursorState, options: unknown | Function, callback?: Function) => unknown;
    command: (server: MongoInternalTopology, ns: string, cmd: MongoInternalCommand, options: unknown | Function, callback?: Function) => unknown;
};
export type MongoInternalTopology = {
    s?: {
        options?: {
            host?: string;
            port?: number;
            servername?: string;
        };
        host?: string;
        port?: number;
    };
    description?: {
        address?: string;
    };
};
export declare enum MongodbCommandType {
    CREATE_INDEXES = "createIndexes",
    FIND_AND_MODIFY = "findAndModify",
    IS_MASTER = "isMaster",
    COUNT = "count",
    AGGREGATE = "aggregate",
    UNKNOWN = "unknown"
}
export type Document = {
    [key: string]: any;
};
export interface MongodbNamespace {
    db: string;
    collection?: string;
}
export type V4Connection = {
    command: Function;
    commandPromise(ns: MongodbNamespace, cmd: Document, options: undefined | unknown, responseType: undefined | unknown): Promise<any>;
    commandCallback(ns: MongodbNamespace, cmd: Document, options: undefined | unknown, callback: any): void;
};
export type V4ConnectionPool = {
    checkOut: (callback: (error: any, connection: any) => void) => void;
};
//# sourceMappingURL=internal-types.d.ts.map