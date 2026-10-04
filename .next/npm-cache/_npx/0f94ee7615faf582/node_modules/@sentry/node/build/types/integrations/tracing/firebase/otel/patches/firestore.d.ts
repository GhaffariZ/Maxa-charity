import { InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
import type { FirestoreSettings } from '../types';
type ShimmerWrap = (target: any, name: string, wrapper: (...args: any[]) => any) => void;
type ShimmerUnwrap = (target: any, name: string) => void;
/**
 *
 * @param firestoreSupportedVersions - supported version of firebase/firestore
 * @param wrap - reference to native instrumentation wrap function
 * @param unwrap - reference to native instrumentation wrap function
 */
export declare function patchFirestore(firestoreSupportedVersions: string[], wrap: ShimmerWrap, unwrap: ShimmerUnwrap): InstrumentationNodeModuleDefinition;
/**
 * Gets the server address and port attributes from the Firestore settings.
 * It's best effort to extract the address and port from the settings, especially for IPv6.
 * @param settings - The Firestore settings containing host information.
 */
export declare function getPortAndAddress(settings: FirestoreSettings): {
    address?: string;
    port?: number;
};
export {};
//# sourceMappingURL=firestore.d.ts.map