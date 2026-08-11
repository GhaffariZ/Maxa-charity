import React from "react";
import { useNavigate } from "react-router";
import { Users, Clock, ArrowRight, Heart, Loader2, X } from "lucide-react";
import { motion } from "motion/react";
import { toast } from "sonner";
import { ImageWithFallback } from "../components/figma/ImageWithFallback";
import { api, ApiRequestError, type CampaignDto } from "../../api/client";
import { faNumber, toFaDigits } from "../lib/format";

export function Campaigns() {
  const navigate = useNavigate();
  const [filter, setFilter] = React.useState<"active" | "completed">("active");
  const [campaigns, setCampaigns] = React.useState<CampaignDto[]>([]);
  const [loading, setLoading] = React.useState(true);
  const [showSuggest, setShowSuggest] = React.useState(false);

  React.useEffect(() => {
    setLoading(true);
    api
      .campaigns(filter)
      .then((r) => setCampaigns(r.campaigns.filter((c) => !c.is_general_fund).reverse()))
      .catch(() => toast.error("دریافت کمپین‌ها ناموفق بود."))
      .finally(() => setLoading(false));
  }, [filter]);

  return (
    <div className="space-y-10">
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div className="max-w-xl">
          <h2 className="text-3xl font-extrabold mb-3">کمپین‌های پیشنهادی</h2>
          <p className="text-muted-foreground">شما می‌توانید مستقیماً از پروژه‌های خاص و مورد علاقهٔ خود حمایت کنید.</p>
        </div>

        <div className="inline-flex self-end md:self-auto bg-surface border border-border rounded-2xl p-1 justify-start gap-2">
          {(["active", "completed"] as const).map((f) => (
            <button
              key={f}
              onClick={() => setFilter(f)}
              className={`px-4 md:px-6 py-2.5 rounded-xl font-bold text-sm transition-all ${
                filter === f ? "bg-primary text-white shadow-lg shadow-primary/20" : "text-muted-foreground hover:bg-muted/50"
              }`}
            >
              {f === "active" ? "فعال" : "تکمیل شده"}
            </button>
          ))}
        </div>
      </div>

      {loading ? (
        <div className="flex items-center justify-center py-24">
          <Loader2 className="animate-spin text-primary" size={32} />
        </div>
      ) : campaigns.length === 0 ? (
        <div className="text-center py-24 text-muted-foreground">کمپینی برای نمایش وجود ندارد.</div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
          {campaigns.map((campaign, idx) => (
            <motion.div
              key={campaign.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: idx * 0.1 }}
              className="bg-surface rounded-[2.5rem] border border-border overflow-hidden flex flex-col group hover:border-primary/30 transition-all shadow-sm hover:shadow-xl hover:shadow-primary/5"
            >
              <div className="relative h-60 overflow-hidden">
                <ImageWithFallback
                  src={campaign.image_url ?? ""}
                  alt={campaign.title}
                  className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                />
                {campaign.category && (
                  <div className="absolute top-6 right-6">
                    <span className="bg-white/90 backdrop-blur-md text-primary-dark font-bold text-xs px-4 py-2 rounded-full shadow-sm">
                      {campaign.category}
                    </span>
                  </div>
                )}
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                {campaign.days_left !== null && (
                  <div className="absolute bottom-6 right-6 left-6 text-white">
                    <div className="flex items-center gap-2 text-xs font-medium mb-1 opacity-90">
                      <Clock size={14} />
                      <span>{toFaDigits(campaign.days_left)} روز باقی‌مانده</span>
                    </div>
                  </div>
                )}
              </div>

              <div className="p-8 flex-1 flex flex-col">
                <h3 className="text-xl font-bold mb-3 leading-snug group-hover:text-primary transition-colors">
                  {campaign.title}
                </h3>
                <p className="text-sm text-muted-foreground leading-relaxed mb-6 line-clamp-2">{campaign.description}</p>

                <div className="mt-auto space-y-4">
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span className="font-bold text-primary">{toFaDigits(campaign.progress)}% تکمیل شده</span>
                      <span className="text-muted-foreground font-medium">
                        {faNumber(campaign.raised_amount)} / {faNumber(campaign.goal_amount)}
                      </span>
                    </div>
                    <div className="h-2.5 bg-muted rounded-full overflow-hidden">
                      <motion.div
                        initial={{ width: 0 }}
                        animate={{ width: `${campaign.progress}%` }}
                        transition={{ duration: 1, delay: 0.5 }}
                        className="h-full bg-primary rounded-full relative"
                      >
                        <div className="absolute inset-0 bg-white/20 animate-pulse" />
                      </motion.div>
                    </div>
                  </div>

                  <div className="flex items-center justify-between pt-4 border-t border-border">
                    <div className="flex items-center gap-2 text-muted-foreground">
                      <Users size={18} />
                      <span className="text-sm font-medium">{faNumber(campaign.donor_count)} حامی</span>
                    </div>
                    <button
                      onClick={() => navigate(`/payments?campaign=${encodeURIComponent(campaign.slug)}`)}
                      title="حمایت از این کمپین"
                      className="bg-muted hover:bg-primary hover:text-white text-foreground p-3 rounded-2xl transition-all group/btn shadow-sm"
                    >
                      <ArrowRight size={20} className="group-hover/btn:-translate-x-1 transition-transform" />
                    </button>
                  </div>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      )}

      <div className="bg-primary/5 rounded-[2.5rem] p-10 flex flex-col lg:flex-row items-center gap-10 border border-primary/10">
        <div className="w-20 h-20 bg-primary/10 rounded-3xl flex items-center justify-center text-primary shrink-0">
          <Heart size={40} className="animate-pulse" />
        </div>
        <div className="flex-1 text-center lg:text-right">
          <h3 className="text-2xl font-bold mb-2">ایده‌ای برای یک کمپین جدید دارید؟</h3>
          <p className="text-muted-foreground">
            اگر پروژهٔ خیریه‌ای می‌شناسید که نیاز به حمایت دارد، با ما در میان بگذارید تا پس از بررسی در این بخش قرار گیرد.
          </p>
        </div>
        <button
          onClick={() => setShowSuggest(true)}
          className="bg-primary text-white px-10 py-4 rounded-2xl font-bold hover:bg-primary-dark transition-all shadow-lg shadow-primary/20"
        >
          ارسال پیشنهاد
        </button>
      </div>

      {showSuggest && <SuggestModal onClose={() => setShowSuggest(false)} />}
    </div>
  );
}

function SuggestModal({ onClose }: { onClose: () => void }) {
  const [title, setTitle] = React.useState("");
  const [description, setDescription] = React.useState("");
  const [contact, setContact] = React.useState("");
  const [busy, setBusy] = React.useState(false);

  const submit = async () => {
    if (description.trim().length < 10) {
      toast.error("لطفاً توضیحات کامل‌تری وارد کنید (حداقل ۱۰ کاراکتر).");
      return;
    }
    setBusy(true);
    try {
      const res = await api.suggestCampaign({
        title: title.trim() || undefined,
        description: description.trim(),
        contact: contact.trim() || undefined,
      });
      toast.success(res.message);
      onClose();
    } catch (e) {
      toast.error(e instanceof ApiRequestError ? e.message : "ثبت پیشنهاد ناموفق بود.");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4" dir="rtl">
      <div className="bg-surface rounded-[2rem] border border-border w-full max-w-lg p-8 shadow-2xl">
        <div className="flex items-center justify-between mb-6">
          <h3 className="text-xl font-bold">پیشنهاد کمپین جدید</h3>
          <button onClick={onClose} className="p-2 rounded-xl hover:bg-muted text-muted-foreground">
            <X size={20} />
          </button>
        </div>
        <div className="space-y-4">
          <input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            placeholder="عنوان (اختیاری)"
            className="w-full bg-muted/30 border border-border rounded-xl py-3 px-4 outline-none text-sm focus:border-primary/40"
          />
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="توضیح پروژه و نیازها…"
            className="w-full bg-muted/30 border border-border rounded-xl py-3 px-4 outline-none text-sm focus:border-primary/40 min-h-[120px]"
          />
          <input
            value={contact}
            onChange={(e) => setContact(e.target.value)}
            placeholder="راه ارتباطی (اختیاری)"
            className="w-full bg-muted/30 border border-border rounded-xl py-3 px-4 outline-none text-sm focus:border-primary/40"
          />
          <button
            onClick={submit}
            disabled={busy}
            className="w-full bg-primary text-white rounded-xl py-3.5 font-bold disabled:opacity-60 flex items-center justify-center gap-2"
          >
            {busy && <Loader2 size={18} className="animate-spin" />}
            ارسال پیشنهاد
          </button>
        </div>
      </div>
    </div>
  );
}
