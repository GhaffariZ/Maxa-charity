import type { CursorState, MongodbNamespace, MongoInternalCommand, MongoInternalTopology, V4Connection, V4ConnectionPool, WireProtocolInternal } from './internal-types';
/** Creates spans for v3 common operations (insert/update/remove). */
export declare function getV3PatchOperation(operationName: 'insert' | 'update' | 'remove'): (original: WireProtocolInternal[typeof operationName]) => (this: unknown, server: MongoInternalTopology, ns: string, ops: unknown[], options: unknown | Function, callback?: Function) => any;
/** Creates spans for the v3 command operation. */
export declare function getV3PatchCommand(): (original: WireProtocolInternal["command"]) => (this: unknown, server: MongoInternalTopology, ns: string, cmd: MongoInternalCommand, options: unknown | Function, callback?: Function) => any;
/** Creates spans for the v4 (<6.4) callback-style command operation. */
export declare function getV4PatchCommandCallback(): (original: V4Connection["commandCallback"]) => (this: any, ns: MongodbNamespace, cmd: any, options: undefined | unknown, callback: any) => any;
/** Creates spans for the v4 (>=6.4) promise-style command operation. */
export declare function getV4PatchCommandPromise(): (original: V4Connection["commandPromise"]) => (this: any, ...args: Parameters<V4Connection["commandPromise"]>) => any;
/** Creates spans for the v3 find operation. */
export declare function getV3PatchFind(): (original: WireProtocolInternal["query"]) => (this: unknown, server: MongoInternalTopology, ns: string, cmd: MongoInternalCommand, cursorState: CursorState, options: unknown | Function, callback?: Function) => any;
/** Creates spans for the v3 getMore (cursor) operation. */
export declare function getV3PatchCursor(): (original: WireProtocolInternal["getMore"]) => (this: unknown, server: MongoInternalTopology, ns: string, cursorState: CursorState, batchSize: number, options: unknown | Function, callback?: Function) => any;
export declare function getV4ConnectionPoolCheckOut(): (original: V4ConnectionPool["checkOut"]) => (this: unknown, callback: (error: any, connection: any) => void) => any;
//# sourceMappingURL=patches.d.ts.map