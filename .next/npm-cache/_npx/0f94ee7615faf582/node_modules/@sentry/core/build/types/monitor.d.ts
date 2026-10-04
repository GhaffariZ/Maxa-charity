import type { CheckIn, MonitorConfig } from './types/checkin';
/**
 * Wraps a callback with a cron monitor check in. The check in will be sent to Sentry when the callback finishes.
 *
 * @param monitorSlug The distinct slug of the monitor.
 * @param callback Callback to be monitored
 * @param upsertMonitorConfig An optional object that describes a monitor config. Use this if you want
 * to create a monitor automatically when sending a check in.
 */
export declare function withMonitor<T>(monitorSlug: CheckIn['monitorSlug'], callback: () => T, upsertMonitorConfig?: MonitorConfig): T;
/**
 * Create a cron monitor check in and send it to Sentry.
 *
 * @param checkIn An object that describes a check in.
 * @param upsertMonitorConfig An optional object that describes a monitor config. Use this if you want
 * to create a monitor automatically when sending a check in.
 */
export declare function captureCheckIn(checkIn: CheckIn, upsertMonitorConfig?: MonitorConfig): string;
//# sourceMappingURL=monitor.d.ts.map