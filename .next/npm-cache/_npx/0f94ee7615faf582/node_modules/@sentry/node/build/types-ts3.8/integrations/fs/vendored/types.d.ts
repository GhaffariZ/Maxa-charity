import * as fs from 'fs';
export type GenericFunction = (...args: any[]) => unknown;
export type FunctionPropertyNames<T> = Exclude<{
    [K in keyof T]: T[K] extends Function ? K : never;
}[keyof T], undefined>;
export type FunctionPropertyNamesTwoLevels<T> = Exclude<{
    [K in keyof T]: {
        [L in keyof T[K]]: L extends string ? T[K][L] extends Function ? K extends string ? L extends string ? string : never : never : never : never;
    }[keyof T[K]];
}[keyof T], undefined>;
export type FMember = FunctionPropertyNames<typeof fs> | FunctionPropertyNamesTwoLevels<typeof fs>;
export type FPMember = FunctionPropertyNames<(typeof fs)['promises']> | FunctionPropertyNamesTwoLevels<(typeof fs)['promises']>;
export interface FsInstrumentationConfig {
    /**
     * Setting this option to `true` will include any filepath arguments from your `fs` API calls as span attributes.
     */
    recordFilePaths?: boolean;
    /**
     * Setting this option to `true` will include the error messages of failed `fs` API calls as a span attribute.
     */
    recordErrorMessagesAsSpanAttributes?: boolean;
}
//# sourceMappingURL=types.d.ts.map
