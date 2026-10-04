import type { Span } from '@sentry/core';
import type { FirestoreReference, FirestoreSettings } from './firestore-types';
/**
 * Opens the inactive `db.query` span for a Firestore operation. `bindTracingChannelToSpan` makes it the
 * active span for the traced call and ends it when the call settles. Mirrors the OTel integration's span,
 * with a distinct `auto.firebase.orchestrion.firestore` origin.
 */
export declare function startFirestoreSpan(spanName: string, reference: FirestoreReference): Span;
/**
 * Gets the server address and port attributes from the Firestore settings.
 * It's best effort to extract the address and port from the settings, especially for IPv6.
 * @param settings - The Firestore settings containing host information.
 */
export declare function getPortAndAddress(settings: FirestoreSettings): {
    address?: string;
    port?: number;
};
//# sourceMappingURL=firestore.d.ts.map