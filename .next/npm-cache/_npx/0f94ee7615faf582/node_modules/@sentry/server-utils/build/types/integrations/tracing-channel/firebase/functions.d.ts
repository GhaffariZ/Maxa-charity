interface FunctionsChannelContext {
    arguments: unknown[];
    self?: unknown;
}
/**
 * Rewrap the handler argument of a firebase-functions `onX(...)` registration so the returned cloud
 * function opens a `SERVER` span (and error boundary) each time it's invoked. Runs as the tracing
 * channel's `start` subscriber, before orchestrion forwards the (mutated) arguments to the real call.
 *
 * The registration call itself is trivial and synchronous, so — unlike the firestore path — this does
 * not bind a span to the channel; it only uses the channel as an injection point.
 */
export declare function wrapFunctionsRegistration(data: FunctionsChannelContext, triggerType: string): void;
export {};
//# sourceMappingURL=functions.d.ts.map