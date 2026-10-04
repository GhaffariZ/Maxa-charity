import { SPAN_KIND } from '@sentry/core';
import { _AWS_KINESIS_STREAM_NAME } from '@sentry/conventions/attributes';

class KinesisServiceExtension {
  requestPreSpanHook(request) {
    const streamName = request.commandInput?.StreamName;
    const spanAttributes = {};
    if (streamName) {
      spanAttributes[_AWS_KINESIS_STREAM_NAME] = streamName;
    }
    return {
      spanAttributes,
      spanKind: SPAN_KIND.CLIENT
    };
  }
}

export { KinesisServiceExtension };
//# sourceMappingURL=kinesis.js.map
