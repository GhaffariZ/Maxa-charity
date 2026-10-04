Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');

class StepFunctionsServiceExtension {
  requestPreSpanHook(request) {
    const stateMachineArn = request.commandInput?.stateMachineArn;
    const activityArn = request.commandInput?.activityArn;
    const spanAttributes = {};
    if (stateMachineArn) {
      spanAttributes[attributes.AWS_STEP_FUNCTIONS_STATE_MACHINE_ARN] = stateMachineArn;
    }
    if (activityArn) {
      spanAttributes[attributes.AWS_STEP_FUNCTIONS_ACTIVITY_ARN] = activityArn;
    }
    return {
      spanAttributes,
      spanKind: core.SPAN_KIND.CLIENT
    };
  }
}

exports.StepFunctionsServiceExtension = StepFunctionsServiceExtension;
//# sourceMappingURL=stepfunctions.js.map
