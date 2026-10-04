import * as fs from 'fs';
import { FMember, FunctionPropertyNames, GenericFunction } from './types';
type FS = typeof fs;
export declare function splitTwoLevels<FSObject>(functionName: FMember): [
    FunctionPropertyNames<FSObject> & string
] | [
    FunctionPropertyNames<FSObject> & string,
    string
];
export declare function indexFs<FSObject extends FS | FS['promises']>(fs: FSObject, member: FMember): {
    objectToPatch: Record<string, GenericFunction>;
    functionNameToPatch: string;
};
export {};
//# sourceMappingURL=utils.d.ts.map
