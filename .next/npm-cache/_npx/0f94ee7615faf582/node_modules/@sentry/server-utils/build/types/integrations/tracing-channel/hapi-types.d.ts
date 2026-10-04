declare const LIFECYCLE_EXT_POINTS: readonly ["onPreAuth", "onCredentials", "onPostAuth", "onPreHandler", "onPostHandler", "onPreResponse", "onRequest"];
export type ServerRequestExtType = (typeof LIFECYCLE_EXT_POINTS)[number];
export type LifecycleMethod = (request: unknown, h: unknown, err?: Error) => unknown;
export interface ServerRouteOptions {
    handler?: LifecycleMethod | unknown;
    [key: string]: unknown;
}
export interface ServerRoute {
    path: string;
    method: string;
    handler?: LifecycleMethod | unknown;
    options?: ((server: unknown) => ServerRouteOptions) | ServerRouteOptions;
    [key: string]: unknown;
}
export interface ServerExtEventsObject {
    type: string;
    [key: string]: unknown;
}
export interface ServerExtEventsRequestObject {
    type: ServerRequestExtType;
    method: LifecycleMethod;
    [key: string]: unknown;
}
export interface ServerExtOptions {
    [key: string]: unknown;
}
/**
 * This symbol is used to mark a Hapi route handler or server extension handler as
 * already patched, since it's possible to use these handlers multiple times
 * i.e. when allowing multiple versions of one plugin, or when registering a plugin
 * multiple times on different servers.
 */
export declare const handlerPatched: unique symbol;
export type PatchableServerRoute = ServerRoute & {
    [handlerPatched]?: boolean;
};
export type PatchableExtMethod = LifecycleMethod & {
    [handlerPatched]?: boolean;
};
export type ServerExtDirectInput = [ServerRequestExtType, LifecycleMethod, (ServerExtOptions | undefined)?];
export declare const HapiLayerType: {
    readonly ROUTER: "router";
    readonly PLUGIN: "plugin";
    readonly EXT: "server.ext";
};
export declare const HapiLifecycleMethodNames: Set<string>;
export declare enum AttributeNames {
    HAPI_TYPE = "hapi.type",
    PLUGIN_NAME = "hapi.plugin.name",
    EXT_TYPE = "server.ext.type"
}
export {};
//# sourceMappingURL=hapi-types.d.ts.map