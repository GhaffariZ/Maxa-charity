/**
 * EXPERIMENTAL — orchestrion-driven firebase integration.
 *
 * Subscribes to the `orchestrion:@firebase/firestore:*` and `orchestrion:firebase-functions:*`
 * diagnostics_channels the orchestrion code transform injects into firestore's `addDoc`/`getDocs`/
 * `setDoc`/`deleteDoc` and firebase-functions' `onX` registration functions, emitting spans identical
 * to the OTel integration — with a distinct `auto.firebase.orchestrion.*` origin. Requires the
 * orchestrion runtime hook or bundler plugin — wire it up via `experimentalUseDiagnosticsChannelInjection()`.
 *
 * @experimental
 */
export declare const firebaseChannelIntegration: () => import("@sentry/core").Integration & {
    name: "Firebase";
};
//# sourceMappingURL=index.d.ts.map