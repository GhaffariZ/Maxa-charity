import React from "react";
import { useNavigate } from "react-router";
import {
  TrendingUp,
  ChevronLeft,
  Award,
  Gift,
  CalendarDays,
  Heart,
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
    { label: "مجموع مشارکت‌ها", value: faToman(stats?.total_amount ?? 0), icon: TrendingUp, tone: "teal" },
    { label: "تعداد کمک‌ها", value: `${faNumber(stats?.donation_count ?? 0)} کمک`, icon: Gift, tone: "gold" },
    { label: "روزهای همراهی", value: `${faNumber(stats?.days_supporting ?? 0)} روز`, icon: CalendarDays, tone: "aqua" },
    { label: "امتیاز مهربانی", value: faNumber(data?.kindness_points ?? 0), icon: Heart, tone: "violet" },
  ];

  return (
    <div className="space-y-6 pb-8 tabular-nums">
      <section className="donor-welcome">
        <svg className="donor-welcome-flower" viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
          <ellipse cx="50" cy="25" rx="15" ry="22" /><ellipse cx="50" cy="75" rx="15" ry="22" />
          <ellipse cx="25" cy="50" rx="22" ry="15" /><ellipse cx="75" cy="50" rx="22" ry="15" /><circle cx="50" cy="50" r="14" />
        </svg>
        <div className="relative">
          <h2 className="mb-2 text-2xl font-semibold md:text-3xl">مشارکت‌های شما در مکسا</h2>
          <p className="donor-welcome-description">خلاصه کمک‌ها، روند مشارکت و فعالیت‌های اخیر</p>
        </div>
        <button
          onClick={() => navigate("/impact")}
          className="donor-impact-button"
        >
          مشاهده گزارش اثرگذاری
          <ChevronLeft size={18} strokeWidth={1.5} aria-hidden="true" />
        </button>
      </section>

      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {cards.map((stat) => (
          <div key={stat.label} className={`donor-stat donor-stat--${stat.tone}`}>
            <div className="donor-stat-icon"><stat.icon size={24} strokeWidth={1.75} aria-hidden="true" /></div>
            <p className="text-muted-foreground text-sm font-medium mb-2">{stat.label}</p>
            <h3 className="donor-stat-value">{stat.value}</h3>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div
          className="donor-panel donor-chart min-w-0 xl:col-span-2 p-5 md:p-6"
        >
          <div className="flex items-center justify-between mb-6">
            <div>
              <h3 className="text-xl font-medium mb-1">روند مشارکت‌ها</h3>
              <p className="text-sm text-muted-foreground">گزارش شش ماههٔ کمک‌های شما</p>
            </div>
            <span className="donor-chart-icon" aria-hidden="true"><TrendingUp size={22} strokeWidth={1.75} /></span>
          </div>

          <div className="h-72 w-full">
            {chartData.length === 0 ? (
              <div className="h-full flex items-center justify-center text-muted-foreground text-sm">
                هنوز کمکی ثبت نشده است
              </div>
            ) : (
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={chartData}>
                  <defs>
                    <linearGradient id="donorAmountFill" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor="var(--primary-ink)" stopOpacity={0.28} />
                      <stop offset="100%" stopColor="var(--primary-ink)" stopOpacity={0.02} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--border)" />
                  <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: "var(--muted-foreground)", fontSize: 12 }} dy={8} />
                  <YAxis hide />
                  <Tooltip
                    formatter={(v: number) => [faToman(v), "مبلغ"]}
                    contentStyle={{ borderRadius: "12px", border: "1px solid var(--border)", backgroundColor: "var(--card)", color: "var(--foreground)", textAlign: "right" }}
                    itemStyle={{ color: "var(--primary-ink)" }}
                  />
                  <Area type="monotone" dataKey="amount" stroke="var(--primary-ink)" strokeWidth={3} fillOpacity={1} fill="url(#donorAmountFill)" activeDot={{ r: 5, fill: "#faa61a", stroke: "var(--card)", strokeWidth: 3 }} isAnimationActive={false} />
                </AreaChart>
              </ResponsiveContainer>
            )}
          </div>
        </div>

        <div
          className="donor-panel donor-activity min-w-0 p-5 md:p-6"
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
                <div className="donor-activity-icon">
                  <TrendingUp size={18} strokeWidth={1.5} aria-hidden="true" />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex flex-wrap items-center justify-between gap-x-2 gap-y-1 mb-1">
                    <h4 className="font-medium text-sm truncate">{activity.campaign_title ?? "کمک مالی"}</h4>
                    <span className="shrink-0 text-xs font-semibold text-primary">{faToman(activity.amount)}</span>
                  </div>
                  <p className="text-xs text-muted-foreground">{faDate(activity.created_at)}</p>
                </div>
              </div>
            ))}
          </div>

          <div className="donor-tier mt-4">
            <div className="donor-tier-icon">
              <Award size={24} strokeWidth={1.75} aria-hidden="true" />
            </div>
            <div>
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
    </div>
  );
}
