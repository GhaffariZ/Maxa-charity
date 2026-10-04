import { LRUMap, getActiveSpan, debug, startInactiveSpan, startSpanManual, SPAN_KIND, SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN } from '@sentry/core';
import { DEBUG_BUILD } from '../debug-build.js';
import { DB_SYSTEM } from '@sentry/conventions/attributes';

const showAllTraces = (() => {
  try {
    return process.env.PRISMA_SHOW_ALL_TRACES === "true";
  } catch {
    return false;
  }
})();
const nonSampledTraceParent = `00-10-10-00`;
const PRISMA_ORIGIN = "auto.db.otel.prisma";
const MAX_TRACKED_PRISMA_SPANS = 1e3;
const prismaSpanRegistry = new LRUMap(MAX_TRACKED_PRISMA_SPANS);
const pendingEngineSpans = [];
function registerPrismaSpan(id, span) {
  prismaSpanRegistry.set(id, span);
}
function buildSpanAttributes(name, attributes) {
  const merged = {
    ...attributes,
    [SEMANTIC_ATTRIBUTE_SENTRY_ORIGIN]: PRISMA_ORIGIN
  };
  if (name === "prisma:engine:db_query" && merged[DB_SYSTEM] == null) {
    merged[DB_SYSTEM] = "prisma";
  }
  return merged;
}
function buildSpanName(name, attributes) {
  const queryText = attributes["db.query.text"];
  if ((name === "prisma:engine:db_query" || name === "prisma:client:db_query") && typeof queryText === "string") {
    return queryText;
  }
  return name;
}
function createResolvedEngineSpans() {
  let createdSpan = true;
  while (createdSpan) {
    createdSpan = false;
    for (let i = pendingEngineSpans.length - 1; i >= 0; i--) {
      const engineSpan = pendingEngineSpans[i];
      const parentSpan = prismaSpanRegistry.get(engineSpan.parent_span_id);
      if (!parentSpan) {
        continue;
      }
      const attributes = buildSpanAttributes(engineSpan.name, engineSpan.attributes);
      const span = startInactiveSpan({
        name: buildSpanName(engineSpan.name, attributes),
        attributes,
        kind: engineSpan.kind === "client" ? SPAN_KIND.CLIENT : SPAN_KIND.INTERNAL,
        startTime: engineSpan.start_time,
        parentSpan
      });
      registerPrismaSpan(engineSpan.span_id, span);
      if (engineSpan.links) {
        span.addLinks(
          engineSpan.links.flatMap((link) => {
            const linkedSpan = prismaSpanRegistry.get(link.span_id);
            return linkedSpan ? [{ context: linkedSpan.spanContext() }] : [];
          })
        );
      }
      span.end(engineSpan.end_time);
      pendingEngineSpans.splice(i, 1);
      createdSpan = true;
    }
  }
}
class ActiveTracingHelper {
  constructor({ ignoreSpanTypes }) {
    this.ignoreSpanTypes = ignoreSpanTypes;
  }
  isEnabled() {
    return true;
  }
  getTraceParent(span) {
    const spanContext = (span ?? getActiveSpan())?.spanContext();
    if (spanContext) {
      return `00-${spanContext.traceId}-${spanContext.spanId}-0${spanContext.traceFlags}`;
    }
    return nonSampledTraceParent;
  }
  dispatchEngineSpans(spans) {
    const linkIds = /* @__PURE__ */ new Map();
    const roots = spans.filter((span) => span.parentId === null);
    for (const root of roots) {
      dispatchEngineSpan(root, spans, linkIds, this.ignoreSpanTypes);
    }
  }
  /**
   * Prisma v5 broke the tracing helper interface with the v6 major, replacing `createEngineSpan` with
   * `dispatchEngineSpans`. We implement the v6/v7 interface (`dispatchEngineSpans`) but also keep this
   * v5-only method so the same helper doesn't blow up in Prisma 5 users' faces, minting v5 engine spans
   * through Sentry's span APIs instead of crashing.
   */
  createEngineSpan(engineSpanEvent) {
    pendingEngineSpans.push(...engineSpanEvent.spans);
    createResolvedEngineSpans();
    const overflow = pendingEngineSpans.length - MAX_TRACKED_PRISMA_SPANS;
    if (overflow > 0) {
      DEBUG_BUILD && debug.log(`[Prisma] Dropping ${overflow} unresolved v5 engine span(s) whose parent was never registered.`);
      pendingEngineSpans.splice(0, overflow);
    }
  }
  getActiveContext() {
    return getActiveSpan();
  }
  runInChildSpan(nameOrOptions, callback) {
    const options = typeof nameOrOptions === "string" ? { name: nameOrOptions } : nameOrOptions;
    if (options.internal && !showAllTraces) {
      return callback();
    }
    const name = `prisma:client:${options.name}`;
    if (shouldIgnoreSpan(name, this.ignoreSpanTypes)) {
      return callback();
    }
    const parentSpan = getActiveSpan();
    const attributes = buildSpanAttributes(name, options.attributes);
    const spanOptions = {
      name: buildSpanName(name, attributes),
      attributes,
      kind: options.kind,
      links: options.links,
      startTime: options.startTime,
      parentSpan
    };
    if (options.active === false) {
      const span = startInactiveSpan(spanOptions);
      registerPrismaSpan(span.spanContext().spanId, span);
      return endSpan(span, () => callback(span, parentSpan));
    }
    return startSpanManual(spanOptions, (span) => {
      registerPrismaSpan(span.spanContext().spanId, span);
      return endSpan(span, () => callback(span, parentSpan));
    });
  }
}
function dispatchEngineSpan(engineSpan, allSpans, linkIds, ignoreSpanTypes) {
  if (shouldIgnoreSpan(engineSpan.name, ignoreSpanTypes)) {
    return;
  }
  const attributes = buildSpanAttributes(engineSpan.name, engineSpan.attributes);
  startSpanManual(
    {
      name: buildSpanName(engineSpan.name, attributes),
      attributes,
      kind: engineSpan.kind === "client" ? SPAN_KIND.CLIENT : SPAN_KIND.INTERNAL,
      startTime: engineSpan.startTime
    },
    (span) => {
      linkIds.set(engineSpan.id, span.spanContext().spanId);
      if (engineSpan.links) {
        span.addLinks(
          engineSpan.links.flatMap((link) => {
            const linkedId = linkIds.get(link);
            if (!linkedId) {
              return [];
            }
            return {
              context: {
                spanId: linkedId,
                traceId: span.spanContext().traceId,
                traceFlags: span.spanContext().traceFlags
              }
            };
          })
        );
      }
      const children = allSpans.filter((s) => s.parentId === engineSpan.id);
      for (const child of children) {
        dispatchEngineSpan(child, allSpans, linkIds, ignoreSpanTypes);
      }
      span.end(engineSpan.endTime);
    }
  );
}
function endSpan(span, run) {
  let result;
  try {
    result = run();
  } catch (reason) {
    span.end();
    throw reason;
  }
  if (isPromiseLike(result)) {
    return result.then(
      (value) => {
        span.end();
        return value;
      },
      (reason) => {
        span.end();
        throw reason;
      }
    );
  }
  span.end();
  return result;
}
function isPromiseLike(value) {
  return value != null && typeof value["then"] === "function";
}
function shouldIgnoreSpan(spanName, ignoreSpanTypes) {
  return ignoreSpanTypes.some((pattern) => typeof pattern === "string" ? pattern === spanName : pattern.test(spanName));
}

export { ActiveTracingHelper };
//# sourceMappingURL=tracing-helper.js.map
