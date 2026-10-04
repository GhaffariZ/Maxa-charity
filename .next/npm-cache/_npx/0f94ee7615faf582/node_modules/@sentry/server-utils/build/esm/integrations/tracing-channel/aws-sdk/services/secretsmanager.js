import { SPAN_KIND } from '@sentry/core';
import { AWS_SECRETSMANAGER_SECRET_ARN } from '@sentry/conventions/attributes';

class SecretsManagerServiceExtension {
  requestPreSpanHook(request) {
    const secretId = request.commandInput?.SecretId;
    const spanAttributes = {};
    if (typeof secretId === "string" && secretId.startsWith("arn:aws:secretsmanager:")) {
      spanAttributes[AWS_SECRETSMANAGER_SECRET_ARN] = secretId;
    }
    return {
      spanAttributes,
      spanKind: SPAN_KIND.CLIENT
    };
  }
  responseHook(response, span) {
    const secretArn = response.data?.ARN;
    if (secretArn) {
      span.setAttribute(AWS_SECRETSMANAGER_SECRET_ARN, secretArn);
    }
  }
}

export { SecretsManagerServiceExtension };
//# sourceMappingURL=secretsmanager.js.map
