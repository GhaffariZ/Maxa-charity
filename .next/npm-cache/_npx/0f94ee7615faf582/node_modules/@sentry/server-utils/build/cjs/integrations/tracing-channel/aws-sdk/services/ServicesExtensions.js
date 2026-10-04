Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const bedrockRuntime = require('./bedrock-runtime.js');
const dynamodb = require('./dynamodb.js');
const kinesis = require('./kinesis.js');
const lambda = require('./lambda.js');
const s3 = require('./s3.js');
const secretsmanager = require('./secretsmanager.js');
const sns = require('./sns.js');
const sqs = require('./sqs.js');
const stepfunctions = require('./stepfunctions.js');

class ServicesExtensions {
  constructor() {
    // Per-service extensions, keyed by the client's `serviceId` (e.g. `'S3'`). Services without a
    // registered extension still get the base rpc span from the subscriber.
    this._services = /* @__PURE__ */ new Map([
      ["SecretsManager", new secretsmanager.SecretsManagerServiceExtension()],
      ["SFN", new stepfunctions.StepFunctionsServiceExtension()],
      ["SQS", new sqs.SqsServiceExtension()],
      ["SNS", new sns.SnsServiceExtension()],
      ["DynamoDB", new dynamodb.DynamodbServiceExtension()],
      ["Lambda", new lambda.LambdaServiceExtension()],
      ["S3", new s3.S3ServiceExtension()],
      ["Kinesis", new kinesis.KinesisServiceExtension()],
      ["BedrockRuntime", new bedrockRuntime.BedrockRuntimeServiceExtension()]
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

exports.ServicesExtensions = ServicesExtensions;
//# sourceMappingURL=ServicesExtensions.js.map
