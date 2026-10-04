Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const breadcrumbs = require('../../breadcrumbs.js');
const breadcrumbLogLevel = require('../../utils/breadcrumb-log-level.js');
const filterCollectedUrl = require('../../utils/data-collection/filterCollectedUrl.js');
const url = require('../../utils/url.js');
const getRequestUrl = require('./get-request-url.js');

function addOutgoingRequestBreadcrumb(request, response) {
  const url$1 = getRequestUrl.getRequestUrlFromClientRequest(request);
  const parsedUrl = url.parseUrl(url$1);
  const statusCode = response?.statusCode;
  const level = breadcrumbLogLevel.getBreadcrumbLogLevelFromHttpStatusCode(statusCode);
  breadcrumbs.addBreadcrumb(
    {
      category: "http",
      data: {
        status_code: statusCode,
        url: url.getSanitizedUrlString(parsedUrl),
        "http.method": request.method || "GET",
        ...parsedUrl.search ? { "http.query": filterCollectedUrl.filterCollectedUrlQuery(parsedUrl.search) } : {},
        ...parsedUrl.hash ? { "http.fragment": parsedUrl.hash } : {}
      },
      type: "http",
      level
    },
    {
      event: "response",
      request,
      response
    }
  );
}

exports.addOutgoingRequestBreadcrumb = addOutgoingRequestBreadcrumb;
//# sourceMappingURL=add-outgoing-request-breadcrumb.js.map
