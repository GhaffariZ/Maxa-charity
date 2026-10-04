import type { NormalizedRequest } from '../types';
import type { RequestMetadata, ServiceExtension } from './ServiceExtension';
export declare class KinesisServiceExtension implements ServiceExtension {
    requestPreSpanHook(request: NormalizedRequest): RequestMetadata;
}
//# sourceMappingURL=kinesis.d.ts.map