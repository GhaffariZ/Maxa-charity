import type { InstrumentationBase } from '@opentelemetry/instrumentation';
import { InstrumentationNodeModuleDefinition } from '@opentelemetry/instrumentation';
import type { FirebaseFunctions, OverloadedParameters } from '../types';
/**
 * Patches Firebase Functions v2 to add OpenTelemetry instrumentation
 * @param functionsSupportedVersions - supported versions of firebase-functions
 * @param wrap - reference to native instrumentation wrap function
 * @param unwrap - reference to native instrumentation unwrap function
 */
export declare function patchFunctions(functionsSupportedVersions: string[], wrap: InstrumentationBase['_wrap'], unwrap: InstrumentationBase['_unwrap']): InstrumentationNodeModuleDefinition;
/**
 * Patches Cloud Functions for Firebase (v2) to add OpenTelemetry instrumentation
 *
 * @param triggerType - Type of trigger
 * @returns A function that patches the function
 */
export declare function patchV2Functions<T extends FirebaseFunctions = FirebaseFunctions>(triggerType: string): (original: T) => (...args: OverloadedParameters<T>) => ReturnType<T>;
//# sourceMappingURL=functions.d.ts.map