/** A string identifying the kind of message consumption. */
export declare const ATTR_MESSAGING_OPERATION: "messaging.operation";
/** The message destination name (the exchange for amqplib). */
export declare const ATTR_MESSAGING_DESTINATION: "messaging.destination";
/** The kind of message destination. */
export declare const ATTR_MESSAGING_DESTINATION_KIND: "messaging.destination_kind";
/** RabbitMQ message routing key. */
export declare const ATTR_MESSAGING_RABBITMQ_ROUTING_KEY: "messaging.rabbitmq.routing_key";
/** The name of the transport protocol. */
export declare const ATTR_MESSAGING_PROTOCOL: "messaging.protocol";
/** The version of the transport protocol. */
export declare const ATTR_MESSAGING_PROTOCOL_VERSION: "messaging.protocol_version";
/** Connection string. */
export declare const ATTR_MESSAGING_URL: "messaging.url";
/** A value used by the messaging system as an identifier for the message, represented as a string. */
export declare const OLD_ATTR_MESSAGING_MESSAGE_ID: "messaging.message_id";
/** The conversation ID (a.k.a. correlation ID) identifying the conversation the message belongs to. */
export declare const ATTR_MESSAGING_CONVERSATION_ID: "messaging.conversation_id";
/** Value for `messaging.destination_kind` when the destination is a topic. */
export declare const MESSAGING_DESTINATION_KIND_VALUE_TOPIC: "topic";
/** Value for `messaging.operation` when the message is being processed by a consumer. */
export declare const MESSAGING_OPERATION_VALUE_PROCESS: "process";
//# sourceMappingURL=semconv.d.ts.map