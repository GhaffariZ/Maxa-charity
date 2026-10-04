import type { NormalizedRequest } from '../types';
import type { RequestMetadata, ServiceExtension } from './ServiceExtension';
export declare class S3ServiceExtension implements ServiceExtension {
    requestPreSpanHook(request: NormalizedRequest): RequestMetadata;
}
//# sourceMappingURL=s3.d.ts.map