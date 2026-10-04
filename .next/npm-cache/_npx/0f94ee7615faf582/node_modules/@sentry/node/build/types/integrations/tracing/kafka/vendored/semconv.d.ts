/**
 * The identifier of the partition messages are sent to or received from, unique within the `messaging.destination.name`.
 *
 * @example "1"
 *
 * @experimental This attribute is experimental and is subject to breaking changes in minor releases of `@opentelemetry/semantic-conventions`.
 */
export declare const ATTR_MESSAGING_DESTINATION_PARTITION_ID: "messaging.destination.partition.id";
/**
 * Message keys in Kafka are used for grouping alike messages to ensure they're processed on the same partition. They differ from `messaging.message.id` in that they're not unique. If the key is `null`, the attribute **MUST NOT** be set.
 *
 * @example "myKey"
 *
 * @note If the key type is not string, it's string representation has to be supplied for the attribute. If the key has no unambiguous, canonical string form, don't include its value.
 *
 * @experimental This attribute is experimental and is subject to breaking changes in minor releases of `@opentelemetry/semantic-conventions`.
 */
export declare const ATTR_MESSAGING_KAFKA_MESSAGE_KEY: "messaging.kafka.message.key";
/**
 * A boolean that is true if the message is a tombstone.
 *
 * @experimental This attribute is experimental and is subject to breaking changes in minor releases of `@opentelemetry/semantic-conventions`.
 */
export declare const ATTR_MESSAGING_KAFKA_MESSAGE_TOMBSTONE: "messaging.kafka.message.tombstone";
/**
 * The offset of a record in the corresponding Kafka partition.
 *
 * @example 42
 *
 * @experimental This attribute is experimental and is subject to breaking changes in minor releases of `@opentelemetry/semantic-conventions`.
 */
export declare const ATTR_MESSAGING_KAFKA_OFFSET: "messaging.kafka.offset";
/**
 * Enum value "process" for attribute `messaging.operation.type`.
 */
export declare const MESSAGING_OPERATION_TYPE_VALUE_PROCESS: "process";
/**
 * Enum value "receive" for attribute `messaging.operation.type`.
 */
export declare const MESSAGING_OPERATION_TYPE_VALUE_RECEIVE: "receive";
/**
 * Enum value "send" for attribute `messaging.operation.type`.
 */
export declare const MESSAGING_OPERATION_TYPE_VALUE_SEND: "send";
/**
 * Enum value "kafka" for attribute `messaging.system`.
 */
export declare const MESSAGING_SYSTEM_VALUE_KAFKA: "kafka";
/**
 * Enum value "_OTHER" for attribute `error.type`. A fallback error value to be used when
 * the instrumentation doesn't define a custom value.
 */
export declare const ERROR_TYPE_VALUE_OTHER: "_OTHER";
//# sourceMappingURL=semconv.d.ts.map