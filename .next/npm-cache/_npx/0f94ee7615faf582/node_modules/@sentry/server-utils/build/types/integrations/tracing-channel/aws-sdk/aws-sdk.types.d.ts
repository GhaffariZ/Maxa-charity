interface Blob {
}
type Binary = Buffer | Uint8Array | Blob | string;
export declare namespace SNS {
    interface MessageAttributeValue {
        DataType: string;
        StringValue?: string;
        BinaryValue?: Binary;
    }
    export type MessageAttributeMap = {
        [key: string]: MessageAttributeValue;
    };
    export {};
}
export declare namespace SQS {
    type StringList = string[];
    type BinaryList = Binary[];
    interface MessageAttributeValue {
        StringValue?: string;
        BinaryValue?: Binary;
        StringListValues?: StringList;
        BinaryListValues?: BinaryList;
        DataType: string;
    }
    export type MessageBodyAttributeMap = {
        [key: string]: MessageAttributeValue;
    };
    type MessageSystemAttributeMap = {
        [key: string]: string;
    };
    export interface Message {
        MessageId?: string;
        ReceiptHandle?: string;
        MD5OfBody?: string;
        Body?: string;
        Attributes?: MessageSystemAttributeMap;
        MD5OfMessageAttributes?: string;
        MessageAttributes?: MessageBodyAttributeMap;
    }
    export {};
}
export {};
//# sourceMappingURL=aws-sdk.types.d.ts.map