import { Span } from '@sentry/core';
import { InstrumentationConfig } from '@opentelemetry/instrumentation';
/**
 * Function that can be used to add custom attributes to span on response from redis server
 */
export interface RedisResponseCustomAttributeFunction {
    (span: Span, cmdName: string, cmdArgs: Array<string | Buffer>, response: unknown): void;
}
export interface RedisInstrumentationConfig extends InstrumentationConfig {
    /** Function for adding custom attributes on db response */
    responseHook?: RedisResponseCustomAttributeFunction;
}
export type CommandArgs = Array<string | Buffer | number | any[]>;
/**
 * Function that can be used to add custom attributes to span on response from redis server (ioredis)
 */
export interface IORedisResponseCustomAttributeFunction {
    (span: Span, cmdName: string, cmdArgs: CommandArgs, response: unknown): void;
}
export interface IORedisInstrumentationConfig extends InstrumentationConfig {
    /** Function for adding custom attributes on db response */
    responseHook?: IORedisResponseCustomAttributeFunction;
}
//# sourceMappingURL=types.d.ts.map
