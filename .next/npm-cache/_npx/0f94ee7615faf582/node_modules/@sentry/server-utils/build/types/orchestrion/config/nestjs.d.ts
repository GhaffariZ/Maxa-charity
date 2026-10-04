import type { InstrumentationConfig } from '..';
/**
 * NestJS is different from the other instrumentations here. It's a standalone
 * metaframework SDK (`@sentry/nestjs`), so its channel LISTENER lives in that
 * package rather than in `server-utils`. Only the code-transform config (and
 * the channel names below) live here, so `@nestjs/*` is transformed whenever
 * orchestrion is enabled, same as every other library.
 *
 * `@sentry/nestjs` imports {@link nestjsChannels} to subscribe, and picks the
 * channel-vs-OTel path based on the global injection flag.
 */
export declare const nestjsConfig: InstrumentationConfig[];
export declare const nestjsChannels: {
    readonly NESTJS_APP_CREATION: "orchestrion:@nestjs/core:nestFactoryCreate";
    readonly NESTJS_ROUTER_CONTEXT: "orchestrion:@nestjs/core:routerExecutionContextCreate";
    readonly NESTJS_INJECTABLE: "orchestrion:@nestjs/common:injectableDecorator";
    readonly NESTJS_CATCH: "orchestrion:@nestjs/common:catchDecorator";
    readonly NESTJS_SCHEDULE_CRON: "orchestrion:@nestjs/schedule:cronDecorator";
    readonly NESTJS_SCHEDULE_INTERVAL: "orchestrion:@nestjs/schedule:intervalDecorator";
    readonly NESTJS_SCHEDULE_TIMEOUT: "orchestrion:@nestjs/schedule:timeoutDecorator";
    readonly NESTJS_ONEVENT: "orchestrion:@nestjs/event-emitter:onEventDecorator";
    readonly NESTJS_PROCESSOR: "orchestrion:@nestjs/bullmq:processorDecorator";
};
export declare const nestjsSubscribeInjection: InstrumentationConfig[];
//# sourceMappingURL=nestjs.d.ts.map