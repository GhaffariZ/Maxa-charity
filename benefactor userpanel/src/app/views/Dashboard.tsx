import React from "react";
import { useNavigate } from "react-router";
import {
  TrendingUp,
  Heart,
  ChevronLeft,
  Calendar,
  Award,
  Gift,
  Loader2,
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
import { motion } from "motion/react";
import { toast } from "sonner";
import { api, type DashboardDto } from "../../api/client";
import { faNumber, faToman, faDate } from "../lib/format";

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
    return (
      <div className="flex items-center justify-center py-32">
        <Loader2 className="animate-spin text-primary" size={32} />
      </div>
    );
  }

  const stats = data?.stats;
  const chartData = (stats?.monthly_series ?? []).map((s) => ({
    name: faMonthLabel(s.month),
    amount: s.amount,
  }));

  const cards = [
    { label: "مجموع مشارکت‌ها", value: faToman(stats?.total_amount ?? 0), icon: TrendingUp, color: "bg-primary" },
    { label: "تعداد کمک‌ها", value: `${faNumber(stats?.donation_count ?? 0)} کمک`, icon: Gift, color: "bg-secondary" },
    { label: "روزهای همراهی", value: `${faNumber(stats?.days_supporting ?? 0)} روز`, icon: Calendar, color: "bg-accent" },
    { label: "امتیاز مهربانی", value: faNumber(data?.kindness_points ?? 0), icon: Heart, color: "bg-destructive" },
  ];

  return (
    <div className="space-y-8 pb-12">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="relative overflow-hidden rounded-[2.5rem] bg-primary p-8 md:p-12 text-white shadow-xl shadow-primary/20"
      >
        <div className="relative z-10 max-w-2xl">
          <h2 className="text-3xl md:text-4xl font-extrabold mb-4 leading-tight">
            ممنون که دنیای دیگران را <br /> زیباتر می‌کنید
          </h2>
          <p className="text-primary-light text-lg mb-8 leading-relaxed opacity-90">
            هر کمک شما، گامی به سوی دنیایی مهربان‌تر است. گزارش اثرگذاری مشارکت‌هایتان را ببینید.
          </p>
          <button
            onClick={() => navigate("/impact")}
            className="bg-secondary hover:bg-secondary/90 text-white px-8 py-4 rounded-2xl font-bold transition-all flex items-center gap-2 group shadow-lg shadow-secondary/30"
          >
            مشاهده گزارش اثرگذاری
            <ChevronLeft size={20} className="group-hover:-translate-x-1 transition-transform" />
          </button>
        </div>
        <div className="absolute top-0 left-0 w-full h-full pointer-events-none overflow-hidden">
          <div className="absolute -top-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl" />
          <div className="absolute -bottom-24 left-1/2 w-64 h-64 bg-secondary/20 rounded-full blur-3xl" />
        </div>
      </motion.div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {cards.map((stat, idx) => (
          <motion.div
            key={stat.label}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: idx * 0.1 }}
            className="bg-surface p-6 rounded-[2rem] border border-border hover:border-primary/30 transition-all group"
          >
            <div className={`w-14 h-14 ${stat.color} rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-current/10`}>
              <stat.icon size={28} />
            </div>
            <p className="text-muted-foreground text-sm font-medium mb-1">{stat.label}</p>
            <h3 className="text-xl font-extrabold">{stat.value}</h3>
          </motion.div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <motion.div
          initial={{ opacity: 0, x: 20 }}
          animate={{ opacity: 1, x: 0 }}
          className="lg:col-span-2 bg-surface p-8 rounded-[2.5rem] border border-border"
        >
          <div className="flex items-center justify-between mb-10">
            <div>
              <h3 className="text-xl font-bold mb-1">روند مشارکت‌ها</h3>
              <p className="text-sm text-muted-foreground">گزارش شش ماههٔ کمک‌های شما</p>
            </div>
          </div>

          <div className="h-[300px] w-full">
            {chartData.length === 0 ? (
              <div className="h-full flex items-center justify-center text-muted-foreground text-sm">
                هنوز کمکی ثبت نشده است.
              </div>
            ) : (
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={chartData}>
                  <defs>
                    <linearGradient id="colorAmount" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#007b7a" stopOpacity={0.3} />
                      <stop offset="95%" stopColor="#007b7a" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f3f5" />
                  <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: "#9d9d9d", fontSize: 12 }} dy={10} />
                  <YAxis hide />
                  <Tooltip
                    formatter={(v: number) => [faToman(v), "مبلغ"]}
                    contentStyle={{ borderRadius: "1rem", border: "none", boxShadow: "0 10px 15px -3px rgb(0 0 0 / 0.1)", textAlign: "right" }}
                  />
                  <Area type="monotone" dataKey="amount" stroke="#007b7a" strokeWidth={4} fillOpacity={1} fill="url(#colorAmount)" />
                </AreaChart>
              </ResponsiveContainer>
            )}
          </div>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, x: -20 }}
          animate={{ opacity: 1, x: 0 }}
          className="bg-surface p-8 rounded-[2.5rem] border border-border"
        >
          <div className="flex items-center justify-between mb-8">
            <h3 className="text-xl font-bold">فعالیت‌های اخیر</h3>
            <button onClick={() => navigate("/history")} className="text-primary text-sm font-bold hover:underline">
              مشاهده همه
            </button>
          </div>

          <div className="space-y-6">
            {(data?.recent_activity ?? []).length === 0 && (
              <p className="text-sm text-muted-foreground">فعالیتی برای نمایش وجود ندارد.</p>
            )}
            {(data?.recent_activity ?? []).map((activity) => (
              <div key={activity.reference} className="flex gap-4 group">
                <div className="shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center bg-primary/10 text-primary">
                  <TrendingUp size={20} />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between mb-1">
                    <h4 className="font-bold text-sm truncate">{activity.campaign_title ?? "کمک مالی"}</h4>
                    <span className="text-xs font-bold text-primary">{faNumber(activity.amount)}</span>
                  </div>
                  <p className="text-xs text-muted-foreground">{faDate(activity.created_at)}</p>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-10 p-6 bg-muted/30 rounded-3xl border border-dashed border-border text-center">
            <div className="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
              <Award className="text-secondary" />
            </div>
            <p className="text-sm font-bold mb-1">
              {data?.tier?.name ? `سطح فعلی: ${data.tier.name}` : "سطح حمایت شما"}
            </p>
            <p className="text-xs text-muted-foreground">
              امتیاز مهربانی شما: {faNumber(data?.kindness_points ?? 0)}
            </p>
          </div>
        </motion.div>
      </div>
    </div>
  );
}
