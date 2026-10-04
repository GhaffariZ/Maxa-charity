import type { InstrumentationConfig } from '../apmTypes';
export declare const koaConfig: {
    channelName: string;
    module: {
        name: string;
        versionRange: string;
        filePath: string;
    };
    functionQuery: {
        className: string;
        methodName: string;
        kind: "Sync";
    };
}[];
export declare const koaChannels: {
    readonly KOA_USE: "orchestrion:koa:use";
};
export declare const koaSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=koa.d.ts.map