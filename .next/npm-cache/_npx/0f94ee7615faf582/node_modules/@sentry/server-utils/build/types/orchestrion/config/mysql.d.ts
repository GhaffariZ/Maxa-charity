import type { InstrumentationConfig } from '..';
export declare const mysqlConfig: {
    channelName: string;
    module: {
        name: string;
        versionRange: string;
        filePath: string;
    };
    functionQuery: {
        expressionName: string;
        kind: "Auto";
    };
}[];
export declare const mysqlChannels: {
    readonly MYSQL_QUERY: "orchestrion:mysql:query";
};
export declare const mysqlSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=mysql.d.ts.map