import { Connection, Options, Replies } from './amqplib-types';
import { EndOperation, ConsumeMessage, Message } from './types';
import { InstrumentationConsumeChannel, InstrumentationPublishChannel } from './utils';
export declare function getConnectPatch(original: (url: string | Options.Connect, socketOptions: any, openCallback: (err: any, connection: Connection) => void) => Connection): (this: unknown, url: string | Options.Connect, socketOptions: any, openCallback: Function) => Connection;
export declare function getChannelEmitPatch(original: Function): (this: InstrumentationConsumeChannel, eventName: string) => void;
export declare function getAckAllPatch(isRejected: boolean, endOperation: EndOperation): (original: Function) => (this: InstrumentationConsumeChannel, requeueOrEmpty?: boolean) => void;
export declare function getAckPatch(isRejected: boolean, endOperation: EndOperation): (original: Function) => (this: InstrumentationConsumeChannel, message: Message, allUpToOrRequeue?: boolean, requeue?: boolean) => void;
export declare function getConsumePatch(original: Function): (this: InstrumentationConsumeChannel, queue: string, onMessage: (msg: ConsumeMessage | null) => void, options?: Options.Consume) => Promise<Replies.Consume>;
export declare function getConfirmedPublishPatch(original: Function): (this: InstrumentationPublishChannel, exchange: string, routingKey: string, content: Buffer, options?: Options.Publish, callback?: (err: any, ok: Replies.Empty) => void) => boolean;
export declare function getPublishPatch(original: Function): (this: InstrumentationPublishChannel, exchange: string, routingKey: string, content: Buffer, options?: Options.Publish) => boolean;
//# sourceMappingURL=patches.d.ts.map
