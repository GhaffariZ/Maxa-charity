import type { NodeFetchOptions } from './types';
/**
 * Instrument outgoing HTTP requests made through `undici` or the global `fetch` API: emit `http.client`
 * spans, record breadcrumbs, and propagate traces into the outgoing request headers.
 *
 * Span creation is gated by the `spans` option (defaults to `true`). When spans are disabled, breadcrumbs
 * are still recorded and trace propagation headers are still injected (gated by `tracePropagation`).
 *
 * undici reports its request lifecycle via `diagnostics_channel`, so rather than patching any module we
 * subscribe to those channels directly. This is idempotent — subsequent calls are no-ops once the
 * channels have been subscribed to, and the config of the first call wins.
 *
 * A combination of https://github.com/elastic/apm-agent-nodejs and
 * https://github.com/gadget-inc/opentelemetry-instrumentations/blob/main/packages/opentelemetry-instrumentation-undici/src/index.ts
 */
export declare function instrumentUndici(config?: NodeFetchOptions): void;
//# sourceMappingURL=undici-instrumentation.d.ts.map