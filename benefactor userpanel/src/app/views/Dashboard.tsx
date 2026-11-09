import React from "react";
import { useNavigate } from "react-router";
import {
  TrendingUp,
  ChevronLeft,
  Award,
} from "lucide-react";
import {
  AreaChart,
  Area,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from "recharts";
import { toast } from "sonner";
import { api, type DashboardDto } from "../../api/client";
import { faNumber, faToman, faDate } from "../lib/format";
import { DashboardSkeleton } from "../components/ui/dashboard-skeleton";

/** Persian (Jalali) long month label from a "YYYY-MM" key. */
function faMonthLabel(ym: string): string {
  const [y, m] = ym.split("-").map(Number);
  const d = new Date(Date.UTC(y, (m || 1) - 1, 1));
  try {
    return new Intl.DateTimeFormat("fa-IR-u-ca-persian", { month: "long" }).format(d);
  } catch {
    return ym;
  }
}

export function Dashboard() {
  const navigate = useNavigate();
  const [data, setData] = React.useState<DashboardDto | null>(null);
  const [loading, setLoading] = React.useState(true);

  React.useEffect(() => {
    api
      .dashboard()
      .then(setData)
      .catch(() => toast.error("دریافت اطلاعات داشبورد ناموفق بود."))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return <DashboardSkeleton />;
  }

  const stats = data?.stats;
  const chartData = (stats?.monthly_series ?? []).map((s) => ({
    name: faMonthLabel(s.month),
    amount: s.amount,
  }));

  const cards = [
    { label: "مجموع مشارکت‌ها", value: faToman(stats?.total_amount ?? 0) },
    { label: "تعداد کمک‌ها", value: `${faNumber(stats?.donation_count ?? 0)} کمک` },
    { label: "روزهای همراهی", value: `${faNumber(stats?.days_supporting ?? 0)} روز` },
    { label: "امتیاز مهربانی", value: faNumber(data?.kindness_points ?? 0) },
  ];

  return (
    <div className="space-y-6 pb-8 tabular-nums">
      <section className="flex flex-wrap items-end justify-between gap-4 border-b border-border pb-6">
        <div>
          <h2 className="mb-2 text-2xl font-semibold md:text-3xl">مشارکت‌های شما در مکسا</h2>
          <p className="text-sm text-muted-foreground">خلاصه کمک‌ها، روند مشارکت و فعالیت‌های اخیر</p>
        </div>
        <button
          onClick={() => navigate("/impact")}
          className="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
        >
          مشاهده گزارش اثرگذاری
          <ChevronLeft size={18} strokeWidth={1.5} aria-hidden="true" />
        </button>
      </section>

      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {cards.map((stat) => (
          <div key={stat.label} className="min-w-0 rounded-xl border border-border bg-surface p-5">
            <p className="text-muted-foreground text-sm font-medium mb-1">{stat.label}</p>
            <h3 className="text-xl font-semibold">{stat.value}</h3>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div
          className="min-w-0 xl:col-span-2 bg-surface p-5 md:p-6 rounded-xl border border-border border-s-4 border-s-primary"
        >
          <div className="flex items-center justify-between mb-6">
            <div>
              <h3 className="text-xl font-medium mb-1">روند مشارکت‌ها</h3>
              <p className="text-sm text-muted-foreground">گزارش شش ماههٔ کمک‌های شما</p>
            </div>
          </div>

          <div className="h-72 w-full">
            {chartData.length === 0 ? (
              <div className="h-full flex items-center justify-center text-muted-foreground text-sm">
                هنوز کمکی ثبت نشده است
              </div>
            ) : (
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={chartData}>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--border)" />
                  <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: "var(--muted-foreground)", fontSize: 12 }} dy={8} />
                  <YAxis hide />
                  <Tooltip
                    formatter={(v: number) => [faToman(v), "مبلغ"]}
                    contentStyle={{ borderRadius: "8px", border: "1px solid var(--border)", textAlign: "right" }}
                  />
                  <Area type="monotone" dataKey="amount" stroke="var(--primary)" strokeWidth={2} fillOpacity={0.08} fill="var(--primary)" isAnimationActive={false} />
                </AreaChart>
              </ResponsiveContainer>
            )}
          </div>
        </div>

        <div
          className="min-w-0 bg-surface p-5 md:p-6 rounded-xl border border-border"
        >
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-xl font-medium">فعالیت‌های اخیر</h3>
            <button onClick={() => navigate("/history")} className="text-primary text-sm font-medium hover:underline">
              مشاهده همه
            </button>
          </div>

          <div className="divide-y divide-border">
            {(data?.recent_activity ?? []).length === 0 && (
              <p className="text-sm text-muted-foreground">فعالیتی برای نمایش وجود ندارد</p>
            )}
            {(data?.recent_activity ?? []).map((activity) => (
              <div key={activity.reference} className="flex items-start gap-3 py-3">
                <div className="shrink-0 pt-1 text-muted-foreground">
                  <TrendingUp size={18} strokeWidth={1.5} aria-hidden="true" />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between gap-2 mb-1">
                    <h4 className="font-medium text-sm truncate">{activity.campaign_title ?? "کمک مالی"}</h4>
                    <span className="shrink-0 text-xs font-medium">{faToman(activity.amount)}</span>
                  </div>
                  <p className="text-xs text-muted-foreground">{faDate(activity.created_at)}</p>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-4 border-t border-border pt-4">
            <div className="mb-2 text-muted-foreground">
              <Award size={18} strokeWidth={1.5} aria-hidden="true" />
            </div>
            <p className="text-sm font-medium mb-1">
              {data?.tier?.name ? `سطح فعلی: ${data.tier.name}` : "سطح حمایت شما"}
            </p>
            <p className="text-xs text-muted-foreground">
              امتیاز مهربانی شما: {faNumber(data?.kindness_points ?? 0)}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
