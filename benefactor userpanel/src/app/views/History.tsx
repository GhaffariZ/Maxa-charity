import React from "react";
import { useSearchParams } from "react-router";
import {
  Search,
  Filter,
  CheckCircle2,
  Clock,
  AlertCircle,
  FileText,
  ChevronLeft,
  Loader2,
} from "lucide-react";
import { motion } from "motion/react";
import { toast } from "sonner";
import { api, ApiRequestError, type DonationDto } from "../../api/client";
import { faNumber, faDate, toFaDigits } from "../lib/format";

const PER_PAGE = 10;

const STATUS_META: Record<string, { label: string; cls: string; Icon: typeof CheckCircle2 }> = {
  success: { label: "موفق", cls: "bg-success/10 text-success", Icon: CheckCircle2 },
  pending: { label: "در انتظار", cls: "bg-warning/10 text-warning", Icon: Clock },
  failed: { label: "ناموفق", cls: "bg-destructive/10 text-destructive", Icon: AlertCircle },
  refunded: { label: "بازگشت", cls: "bg-muted text-muted-foreground", Icon: AlertCircle },
  canceled: { label: "لغو شده", cls: "bg-muted text-muted-foreground", Icon: AlertCircle },
};

export function History() {
  const [params, setParams] = useSearchParams();
  const [items, setItems] = React.useState<DonationDto[]>([]);
  const [total, setTotal] = React.useState(0);
  const [page, setPage] = React.useState(1);
  const [search, setSearch] = React.useState("");
  const [loading, setLoading] = React.useState(true);
  const [requestingCert, setRequestingCert] = React.useState(false);

  // Show a toast when we land here from the payment gateway callback.
  React.useEffect(() => {
    const payment = params.get("payment");
    if (payment === "success") {
      toast.success("پرداخت شما با موفقیت ثبت شد. سپاسگزاریم!");
    } else if (payment === "failed") {
      toast.error("پرداخت ناتمام ماند یا لغو شد.");
    }
    if (payment) {
      params.delete("payment");
      params.delete("ref");
      setParams(params, { replace: true });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Debounced fetch on page/search change.
  React.useEffect(() => {
    setLoading(true);
    const handle = setTimeout(() => {
      api
        .donationHistory(page, PER_PAGE, search)
        .then((r) => {
          setItems(r.items);
          setTotal(r.total);
        })
        .catch(() => toast.error("دریافت تاریخچه ناموفق بود."))
        .finally(() => setLoading(false));
    }, search ? 350 : 0);
    return () => clearTimeout(handle);
  }, [page, search]);

  const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));

  const requestCertificate = async () => {
    setRequestingCert(true);
    try {
      const res = await api.requestTaxCertificate({});
      toast.success(res.message);
    } catch (e) {
      toast.error(e instanceof ApiRequestError ? e.message : "ثبت درخواست ناموفق بود.");
    } finally {
      setRequestingCert(false);
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <h2 className="text-3xl font-extrabold mb-2">تاریخچه پرداخت‌ها</h2>
          <p className="text-muted-foreground">مشاهده سوابق و دریافت رسیدهای مشارکت‌های شما.</p>
        </div>

        <div className="flex items-center gap-3">
          <div className="relative">
            <Search className="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground" size={18} />
            <input
              type="text"
              value={search}
              onChange={(e) => {
                setPage(1);
                setSearch(e.target.value);
              }}
              placeholder="جستجو در سوابق..."
              className="bg-surface border border-border rounded-xl pr-12 pl-4 py-3 outline-none focus:ring-2 ring-primary/20 w-64 text-sm"
            />
          </div>
          <button className="p-3 bg-surface border border-border rounded-xl text-muted-foreground hover:bg-muted/50 transition-all">
            <Filter size={20} />
          </button>
        </div>
      </div>

      <div className="bg-surface rounded-[2.5rem] border border-border overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-right">
            <thead>
              <tr className="bg-muted/30 border-b border-border">
                <th className="px-8 py-6 font-bold text-sm">شماره تراکنش</th>
                <th className="px-8 py-6 font-bold text-sm">تاریخ</th>
                <th className="px-8 py-6 font-bold text-sm">مبلغ (تومان)</th>
                <th className="px-8 py-6 font-bold text-sm">هدف پرداخت</th>
                <th className="px-8 py-6 font-bold text-sm">وضعیت</th>
                <th className="px-8 py-6 font-bold text-sm">رسید</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {loading && (
                <tr>
                  <td colSpan={6} className="px-8 py-16 text-center text-muted-foreground">
                    <Loader2 className="animate-spin inline" size={24} />
                  </td>
                </tr>
              )}

              {!loading && items.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-8 py-16 text-center text-muted-foreground">
                    هنوز تراکنشی ثبت نشده است.
                  </td>
                </tr>
              )}

              {!loading &&
                items.map((p, idx) => {
                  const meta = STATUS_META[p.status] ?? STATUS_META.failed;
                  return (
                    <motion.tr
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: idx * 0.04 }}
                      key={p.reference}
                      className="hover:bg-muted/10 transition-colors group"
                    >
                      <td className="px-8 py-6">
                        <span className="font-mono text-xs font-bold bg-muted/50 px-2 py-1 rounded-md" dir="ltr">
                          {p.reference}
                        </span>
                      </td>
                      <td className="px-8 py-6 text-sm font-medium">{faDate(p.paid_at ?? p.created_at)}</td>
                      <td className="px-8 py-6">
                        <span className="font-extrabold text-primary">{faNumber(p.amount)}</span>
                      </td>
                      <td className="px-8 py-6 text-sm text-muted-foreground">{p.campaign_title ?? "صندوق عمومی"}</td>
                      <td className="px-8 py-6">
                        <span className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold w-fit ${meta.cls}`}>
                          <meta.Icon size={14} />
                          {meta.label}
                        </span>
                      </td>
                      <td className="px-8 py-6">
                        {p.status === "success" && p.receipt_number ? (
                          <span className="font-mono text-xs text-muted-foreground" dir="ltr">
                            {p.receipt_number}
                          </span>
                        ) : (
                          <span className="text-xs text-muted-foreground">—</span>
                        )}
                      </td>
                    </motion.tr>
                  );
                })}
            </tbody>
          </table>
        </div>

        <div className="p-8 border-t border-border flex items-center justify-between">
          <p className="text-sm text-muted-foreground">
            نمایش {faNumber(items.length)} از {faNumber(total)} مورد
          </p>
          <div className="flex gap-2">
            <button
              disabled={page <= 1}
              onClick={() => setPage((p) => Math.max(1, p - 1))}
              className="w-10 h-10 flex items-center justify-center rounded-xl border border-border hover:bg-muted/50 transition-all font-bold disabled:opacity-40 disabled:cursor-not-allowed"
            >
              ›
            </button>
            <span className="w-10 h-10 flex items-center justify-center rounded-xl border border-border bg-primary text-white font-bold">
              {toFaDigits(page)}
            </span>
            <button
              disabled={page >= totalPages}
              onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
              className="w-10 h-10 flex items-center justify-center rounded-xl border border-border hover:bg-muted/50 transition-all font-bold disabled:opacity-40 disabled:cursor-not-allowed"
            >
              ‹
            </button>
          </div>
        </div>
      </div>

      <div className="bg-surface p-8 rounded-[2.5rem] border border-border flex items-start gap-6">
        <div className="w-14 h-14 bg-secondary/10 text-secondary rounded-2xl flex items-center justify-center shrink-0">
          <FileText size={28} />
        </div>
        <div>
          <h3 className="text-lg font-bold mb-1">درخواست گواهی کسر از مالیات</h3>
          <p className="text-sm text-muted-foreground mb-4">
            طبق قوانین جاری، مشارکت‌های نقدی شما در موسسات خیریه معتبر می‌تواند از درآمد مشمول مالیات شما کسر گردد.
          </p>
          <button
            onClick={requestCertificate}
            disabled={requestingCert}
            className="text-secondary font-bold hover:underline flex items-center gap-1 disabled:opacity-60"
          >
            {requestingCert && <Loader2 size={16} className="animate-spin" />}
            ارسال درخواست صدور گواهی
            <ChevronLeft size={16} />
          </button>
        </div>
      </div>
    </div>
  );
}
