import { BedrockRuntimeServiceExtension } from './bedrock-runtime.js';
import { DynamodbServiceExtension } from './dynamodb.js';
import { KinesisServiceExtension } from './kinesis.js';
import { LambdaServiceExtension } from './lambda.js';
import { S3ServiceExtension } from './s3.js';
import { SecretsManagerServiceExtension } from './secretsmanager.js';
import { SnsServiceExtension } from './sns.js';
import { SqsServiceExtension } from './sqs.js';
import { StepFunctionsServiceExtension } from './stepfunctions.js';

class ServicesExtensions {
  constructor() {
    // Per-service extensions, keyed by the client's `serviceId` (e.g. `'S3'`). Services without a
    // registered extension still get the base rpc span from the subscriber.
    this._services = /* @__PURE__ */ new Map([
      ["SecretsManager", new SecretsManagerServiceExtension()],
      ["SFN", new StepFunctionsServiceExtension()],
      ["SQS", new SqsServiceExtension()],
      ["SNS", new SnsServiceExtension()],
      ["DynamoDB", new DynamodbServiceExtension()],
      ["Lambda", new LambdaServiceExtension()],
      ["S3", new S3ServiceExtension()],
      ["Kinesis", new KinesisServiceExtension()],
      ["BedrockRuntime", new BedrockRuntimeServiceExtension()]
    ]);
  }
  requestPreSpanHook(request) {
    const serviceExtension = this._services.get(request.serviceName);
    if (!serviceExtension) {
      return {};
    }
    return serviceExtension.requestPreSpanHook(request);
  }
  requestPostSpanHook(request, span) {
    const serviceExtension = this._services.get(request.serviceName);
    serviceExtension?.requestPostSpanHook?.(request, span);
  }
  responseHook(response, span) {
    const serviceExtension = this._services.get(response.request.serviceName);
    serviceExtension?.responseHook?.(response, span);
  }
}

export { ServicesExtensions };
//# sourceMappingURL=ServicesExtensions.js.map
