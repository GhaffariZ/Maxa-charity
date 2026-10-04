export declare enum EndOperation {
    AutoAck = "auto ack",
    Ack = "ack",
    AckAll = "ackAll",
    Reject = "reject",
    Nack = "nack",
    NackAll = "nackAll",
    ChannelClosed = "channel closed",
    ChannelError = "channel error",
    InstrumentationTimeout = "instrumentation timeout"
}
export interface Message {
    content: Buffer;
    fields: MessageFields;
    properties: MessageProperties;
}
export interface ConsumeMessage extends Message {
    fields: ConsumeMessageFields;
}
export interface CommonMessageFields {
    deliveryTag: number;
    redelivered: boolean;
    exchange: string;
    routingKey: string;
}
export interface MessageFields extends CommonMessageFields {
    messageCount?: number;
    consumerTag?: string;
}
export interface ConsumeMessageFields extends CommonMessageFields {
    deliveryTag: number;
}
export interface MessageProperties {
    contentType: any | undefined;
    contentEncoding: any | undefined;
    headers: any;
    deliveryMode: any | undefined;
    priority: any | undefined;
    correlationId: any | undefined;
    replyTo: any | undefined;
    expiration: any | undefined;
    messageId: any | undefined;
    timestamp: any | undefined;
    type: any | undefined;
    userId: any | undefined;
    appId: any | undefined;
    clusterId: any | undefined;
}
//# sourceMappingURL=types.d.ts.map
