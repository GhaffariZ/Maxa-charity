import React from "react";
import { useSearchParams } from "react-router";
import {
  CreditCard,
  Info,
  ShieldCheck,
  ArrowRightLeft,
  Heart,
  Loader2,
} from "lucide-react";
import { toast } from "sonner";
import { api, ApiRequestError, type CampaignDto } from "../../api/client";
import { faNumber } from "../lib/format";

const presetAmounts = [
  { label: "۱۰۰,۰۰۰", value: 100000 },
  { label: "۲۰۰,۰۰۰", value: 200000 },
  { label: "۵۰۰,۰۰۰", value: 500000 },
  { label: "۱,۰۰۰,۰۰۰", value: 1000000 },
];

/** Parse a Persian/English digit string (with separators) into a Toman number. */
function parseAmount(input: string): number {
  const normalized = input
    .replace(/[۰-۹]/g, (d) => String("۰۱۲۳۴۵۶۷۸۹".indexOf(d)))
    .replace(/[^0-9]/g, "");
  return normalized ? parseInt(normalized, 10) : 0;
}

export function Payments() {
  const [selectedAmount, setSelectedAmount] = React.useState<number | null>(null);
  const [customAmount, setCustomAmount] = React.useState("");
  const [campaigns, setCampaigns] = React.useState<CampaignDto[]>([]);
  const [selectedSlug, setSelectedSlug] = React.useState<string>("");
  const [submitting, setSubmitting] = React.useState(false);
  const [params] = useSearchParams();
  const preselect = params.get("campaign");

  React.useEffect(() => {
    api
      .campaigns("active")
      .then((r) => {
        setCampaigns(r.campaigns);
        // Honor ?campaign=slug from a campaign card; else default to general fund.
        const fromQuery = preselect ? r.campaigns.find((c) => c.slug === preselect) : undefined;
        const fallback = r.campaigns.find((c) => c.is_general_fund) ?? r.campaigns[0];
        const chosen = fromQuery ?? fallback;
        if (chosen) setSelectedSlug(chosen.slug);
      })
      .catch(() => toast.error("دریافت فهرست کمپین‌ها ناموفق بود."));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [preselect]);

  const amount = customAmount ? parseAmount(customAmount) : selectedAmount ?? 0;

  const handlePayment = async () => {
    if (amount < 1000) {
      toast.error("لطفاً مبلغی معتبر (حداقل ۱,۰۰۰ تومان) وارد کنید.");
      return;
    }
    setSubmitting(true);
    try {
      const { redirect_url } = await api.startDonation(amount, selectedSlug || undefined);
      toast.success("در حال انتقال به درگاه پرداخت ایمن...");
      // Hand off to the bank gateway.
      window.location.href = redirect_url;
    } catch (e) {
      setSubmitting(false);
      toast.error(e instanceof ApiRequestError ? e.message : "اتصال به درگاه ناموفق بود.");
    }
  };

  const selectedTitle = campaigns.find((c) => c.slug === selectedSlug)?.title ?? "—";

  return (
    <div className="max-w-4xl mx-auto">
      <div className="text-center mb-10">
        <h2 className="text-3xl font-extrabold mb-3">پرداخت آنلاین و یاری‌رسانی</h2>
        <p className="text-muted-foreground">با انتخاب مبلغ و هدف کمک، سهمی در سلامتی دیگران داشته باشید.</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-8">
          <section className="bg-surface p-8 rounded-[2.5rem] border border-border">
            <h3 className="text-lg font-bold mb-6 flex items-center gap-2">
              <span className="w-8 h-8 bg-primary/10 text-primary rounded-lg flex items-center justify-center text-sm">۱</span>
              انتخاب مبلغ (تومان)
            </h3>

            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
              {presetAmounts.map((a) => (
                <button
                  key={a.value}
                  onClick={() => {
                    setSelectedAmount(a.value);
                    setCustomAmount("");
                  }}
                  className={`py-4 px-2 rounded-2xl font-bold transition-all border-2 ${
                    selectedAmount === a.value && !customAmount
                      ? "bg-primary text-white border-primary shadow-lg shadow-primary/20"
                      : "bg-muted/30 border-transparent hover:border-primary/30"
                  }`}
                >
                  {a.label}
                </button>
              ))}
            </div>

            <div className="relative">
              <input
                type="text"
                inputMode="numeric"
                placeholder="مبلغ دلخواه را وارد کنید..."
                value={customAmount}
                onChange={(e) => {
                  setCustomAmount(e.target.value);
                  setSelectedAmount(null);
                }}
                className="w-full bg-muted/50 border-2 border-transparent focus:border-primary/30 focus:bg-surface rounded-2xl py-4 px-6 outline-none transition-all font-bold text-lg"
              />
              <span className="absolute left-6 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">تومان</span>
            </div>
          </section>

          <section className="bg-surface p-8 rounded-[2.5rem] border border-border">
            <h3 className="text-lg font-bold mb-6 flex items-center gap-2">
              <span className="w-8 h-8 bg-primary/10 text-primary rounded-lg flex items-center justify-center text-sm">۲</span>
              هدف از یاری‌رسانی
            </h3>

            <div className="space-y-3">
              {campaigns.length === 0 && (
                <p className="text-sm text-muted-foreground">در حال بارگذاری کمپین‌ها…</p>
              )}
              {campaigns.map((c) => (
                <label
                  key={c.slug}
                  className={`flex items-center gap-4 p-4 rounded-2xl cursor-pointer border-2 transition-all ${
                    selectedSlug === c.slug
                      ? "bg-primary/5 border-primary"
                      : "bg-muted/30 border-transparent hover:bg-muted/50"
                  }`}
                >
                  <input
                    type="radio"
                    name="campaign"
                    className="w-5 h-5 accent-primary"
                    checked={selectedSlug === c.slug}
                    onChange={() => setSelectedSlug(c.slug)}
                  />
                  <span className="font-bold flex-1">{c.title}</span>
                </label>
              ))}
            </div>
          </section>
        </div>

        <div className="space-y-6">
          <section className="bg-surface p-8 rounded-[2.5rem] border border-border">
            <h3 className="text-lg font-bold mb-6">خلاصه مشارکت</h3>

            <div className="space-y-4 mb-8">
              <div className="flex justify-between items-center text-sm">
                <span className="text-muted-foreground">مبلغ کمک:</span>
                <span className="font-bold">{faNumber(amount)} تومان</span>
              </div>
              <div className="flex justify-between items-center text-sm">
                <span className="text-muted-foreground">هدف:</span>
                <span className="font-bold text-primary truncate max-w-[150px]">{selectedTitle}</span>
              </div>
              <div className="flex justify-between items-center text-sm">
                <span className="text-muted-foreground">کارمزد بانکی:</span>
                <span className="font-bold text-success">رایگان</span>
              </div>
              <div className="pt-4 border-t border-border flex justify-between items-center">
                <span className="font-bold">مبلغ قابل پرداخت:</span>
                <span className="text-xl font-extrabold text-primary">{faNumber(amount)}</span>
              </div>
            </div>

            <button
              onClick={handlePayment}
              disabled={submitting}
              className="w-full bg-primary hover:bg-primary-dark text-white py-5 rounded-[1.5rem] font-extrabold transition-all shadow-xl shadow-primary/20 flex items-center justify-center gap-2 group disabled:opacity-60"
            >
              {submitting ? (
                <>
                  <Loader2 size={20} className="animate-spin" />
                  در حال اتصال…
                </>
              ) : (
                <>
                  اتصال به درگاه بانکی
                  <CreditCard size={20} className="group-hover:scale-110 transition-transform" />
                </>
              )}
            </button>

            <div className="mt-6 flex items-center justify-center gap-4">
              <div className="flex flex-col items-center gap-1">
                <div className="w-10 h-10 bg-muted/50 rounded-full flex items-center justify-center text-muted-foreground">
                  <ShieldCheck size={20} />
                </div>
                <span className="text-[10px] text-muted-foreground">امن و معتبر</span>
              </div>
              <div className="flex flex-col items-center gap-1">
                <div className="w-10 h-10 bg-muted/50 rounded-full flex items-center justify-center text-muted-foreground">
                  <ArrowRightLeft size={20} />
                </div>
                <span className="text-[10px] text-muted-foreground">شفافیت کامل</span>
              </div>
            </div>
          </section>

          <div className="bg-secondary/10 p-6 rounded-[2rem] border border-secondary/20 relative overflow-hidden">
            <Heart className="absolute -bottom-4 -right-4 w-24 h-24 text-secondary/10" />
            <div className="relative z-10">
              <div className="w-10 h-10 bg-secondary text-white rounded-xl flex items-center justify-center mb-4">
                <Info size={20} />
              </div>
              <h4 className="font-bold mb-2">آیا می‌دانستید؟</h4>
              <p className="text-xs text-muted-foreground leading-relaxed">
                مکسا تمامی کمک‌های شما را بدون هیچ‌گونه کسر هزینه‌های اداری، مستقیماً صرف خدمات درمانی و حمایتی بیماران می‌کند.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
