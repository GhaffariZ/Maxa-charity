/**
 * AWS-specific span constants used by the aws-sdk channel integration that are NOT covered by
 * `@sentry/conventions/attributes` (attribute names that exist there are imported from there
 * directly). These are either Sentry-specific (the span origin), attribute *values* (not keys), or
 * obsolete OTel conventions with no `@sentry/conventions` export.
 */
/** The span origin every aws-sdk channel span carries, mirroring the uniform OTel `auto.otel.aws`. */
export declare const AWS_SDK_ORIGIN = "auto.aws.orchestrion.aws_sdk";
/** DynamoDB `db.system` value (an attribute value, not a key, so not covered by conventions). */
export declare const DB_SYSTEM_VALUE_DYNAMODB = "dynamodb";
export declare const ATTR_MESSAGING_DESTINATION_KIND = "messaging.destination_kind";
export declare const MESSAGING_DESTINATION_KIND_VALUE_TOPIC = "topic";
export declare const GEN_AI_OPERATION_NAME_VALUE_CHAT = "chat";
export declare const GEN_AI_SYSTEM_VALUE_AWS_BEDROCK = "aws.bedrock";
//# sourceMappingURL=constants.d.ts.map