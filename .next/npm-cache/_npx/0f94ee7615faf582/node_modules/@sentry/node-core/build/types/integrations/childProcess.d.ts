interface Options {
    /**
     * Whether to include child process arguments in breadcrumbs data.
     *
     * @default false
     */
    includeChildProcessArgs?: boolean;
    /**
     * Whether to capture errors from worker threads.
     *
     * @default true
     */
    captureWorkerErrors?: boolean;
}
/**
 * Capture breadcrumbs and events for child processes and worker threads.
 */
export declare const childProcessIntegration: (options?: Options | undefined) => import("@sentry/core").Integration & {
    name: "ChildProcess";
};
export {};
//# sourceMappingURL=childProcess.d.ts.map