Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');

class S3ServiceExtension {
  requestPreSpanHook(request) {
    const bucketName = request.commandInput?.Bucket;
    const spanAttributes = {};
    if (bucketName) {
      spanAttributes[attributes.AWS_S3_BUCKET] = bucketName;
    }
    return {
      spanAttributes,
      spanKind: core.SPAN_KIND.CLIENT
    };
  }
}

exports.S3ServiceExtension = S3ServiceExtension;
//# sourceMappingURL=s3.js.map
