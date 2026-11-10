import { Loader } from "./loader";
import { Skeleton } from "./skeleton";

export function DashboardSkeleton() {
  return (
    <div className="macsa-loading-layout" dir="rtl">
      <div className="macsa-loading-heading">
        <div className="macsa-loading-status">
          <Loader label="در حال دریافت اطلاعات داشبورد" />
          <p className="macsa-loading-label" aria-hidden="true">در حال دریافت اطلاعات داشبورد</p>
        </div>
        <div className="macsa-loading-lines" aria-hidden="true"><Skeleton className="macsa-loading-line" /></div>
      </div>
      <div className="macsa-loading-stats" aria-hidden="true">
        {[0, 1, 2, 3].map(i => (
          <div className="macsa-loading-card" key={i}>
            <Skeleton className="macsa-loading-line" /><Skeleton className="macsa-loading-number" /><Skeleton className="macsa-loading-line macsa-loading-line--short" />
          </div>
        ))}
      </div>
      <div className="macsa-loading-columns" aria-hidden="true">
        <div className="macsa-loading-card">
          <Skeleton className="macsa-loading-line macsa-loading-line--short" />
          <div className="macsa-loading-chart"><Loader variant="pulse" size={64} /></div>
        </div>
        <div className="macsa-loading-card">
          <Skeleton className="macsa-loading-line" />
          {[0, 1, 2].map(i => (
            <div className="macsa-loading-row" key={i}>
              <Skeleton className="macsa-loading-avatar" />
              <div className="macsa-loading-lines"><Skeleton className="macsa-loading-line" /><Skeleton className="macsa-loading-line macsa-loading-line--short" /></div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
