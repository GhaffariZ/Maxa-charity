Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');

class SecretsManagerServiceExtension {
  requestPreSpanHook(request) {
    const secretId = request.commandInput?.SecretId;
    const spanAttributes = {};
    if (typeof secretId === "string" && secretId.startsWith("arn:aws:secretsmanager:")) {
      spanAttributes[attributes.AWS_SECRETSMANAGER_SECRET_ARN] = secretId;
    }
    return {
      spanAttributes,
      spanKind: core.SPAN_KIND.CLIENT
    };
  }
  responseHook(response, span) {
    const secretArn = response.data?.ARN;
    if (secretArn) {
      span.setAttribute(attributes.AWS_SECRETSMANAGER_SECRET_ARN, secretArn);
    }
  }
}

exports.SecretsManagerServiceExtension = SecretsManagerServiceExtension;
//# sourceMappingURL=secretsmanager.js.map
