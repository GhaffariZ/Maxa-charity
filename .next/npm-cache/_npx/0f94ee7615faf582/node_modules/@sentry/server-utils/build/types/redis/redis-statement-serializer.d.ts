/**
 * Given the redis command name and arguments, return a combination of the
 * command name + the allowed arguments according to `serializationSubsets`.
 */
export declare const defaultDbStatementSerializer: (cmdName: string, cmdArgs: Array<string | Buffer | number | unknown[]>) => string;
//# sourceMappingURL=redis-statement-serializer.d.ts.map