import type { Span, SpanAttributes } from '@sentry/core';
import type { Channel, ConfirmChannel, Connection, Options } from './amqplib-types';
import type { ConsumeMessage, Message } from './types';
export declare const MESSAGE_STORED_SPAN: unique symbol;
export declare const CHANNEL_SPANS_NOT_ENDED: unique symbol;
export declare const CHANNEL_CONSUME_TIMEOUT_TIMER: unique symbol;
export declare const CONNECTION_ATTRIBUTES: unique symbol;
export declare const CHANNEL_IS_CONFIRM_PUBLISHING: unique symbol;
export type InstrumentationConnection = Connection & {
    [CONNECTION_ATTRIBUTES]?: SpanAttributes;
};
export type InstrumentationPublishChannel = (Channel | ConfirmChannel) & {
    connection: InstrumentationConnection;
    [CHANNEL_IS_CONFIRM_PUBLISHING]?: boolean;
};
export type InstrumentationConsumeChannel = Channel & {
    connection: InstrumentationConnection;
    [CHANNEL_SPANS_NOT_ENDED]?: {
        msg: ConsumeMessage;
        timeOfConsume: number;
    }[];
    [CHANNEL_CONSUME_TIMEOUT_TIMER]?: NodeJS.Timeout;
};
export type InstrumentationMessage = Message & {
    [MESSAGE_STORED_SPAN]?: Span;
};
export type InstrumentationConsumeMessage = ConsumeMessage & {
    [MESSAGE_STORED_SPAN]?: Span;
};
export declare const normalizeExchange: (exchangeName: string) => string;
export declare const getConnectionAttributesFromServer: (conn: Connection) => SpanAttributes;
export declare const getConnectionAttributesFromUrl: (url: string | Options.Connect) => SpanAttributes;
/** Reads a propagation header value off an amqplib message as a string. */
export declare function getHeaderAsString(headers: Record<string, unknown> | undefined, key: string): string | undefined;
/** Starts an inactive producer span and propagates its trace into the publish `options.headers`. */
export declare function startPublishSpan(exchange: string, routingKey: string, channel: InstrumentationPublishChannel, options?: Options.Publish): {
    span: Span;
    modifiedOptions: Options.Publish;
};
/** Starts an inactive consumer (process) span carrying the amqplib messaging attributes. */
export declare function startConsumeSpan(queue: string, msg: InstrumentationConsumeMessage, channel: InstrumentationConsumeChannel): Span;
//# sourceMappingURL=utils.d.ts.map