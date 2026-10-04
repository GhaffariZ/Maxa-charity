import { SPAN_KIND } from '@sentry/core';
import { AWS_STEP_FUNCTIONS_STATE_MACHINE_ARN, AWS_STEP_FUNCTIONS_ACTIVITY_ARN } from '@sentry/conventions/attributes';

class StepFunctionsServiceExtension {
  requestPreSpanHook(request) {
    const stateMachineArn = request.commandInput?.stateMachineArn;
    const activityArn = request.commandInput?.activityArn;
    const spanAttributes = {};
    if (stateMachineArn) {
      spanAttributes[AWS_STEP_FUNCTIONS_STATE_MACHINE_ARN] = stateMachineArn;
    }
    if (activityArn) {
      spanAttributes[AWS_STEP_FUNCTIONS_ACTIVITY_ARN] = activityArn;
    }
    return {
      spanAttributes,
      spanKind: SPAN_KIND.CLIENT
    };
  }
}

export { StepFunctionsServiceExtension };
//# sourceMappingURL=stepfunctions.js.map
