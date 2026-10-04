import { SPAN_KIND } from '@sentry/core';
import { AWS_S3_BUCKET } from '@sentry/conventions/attributes';

class S3ServiceExtension {
  requestPreSpanHook(request) {
    const bucketName = request.commandInput?.Bucket;
    const spanAttributes = {};
    if (bucketName) {
      spanAttributes[AWS_S3_BUCKET] = bucketName;
    }
    return {
      spanAttributes,
      spanKind: SPAN_KIND.CLIENT
    };
  }
}

export { S3ServiceExtension };
//# sourceMappingURL=s3.js.map
