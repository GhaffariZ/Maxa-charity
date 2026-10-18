import React from "react";
import { RotateCw } from "lucide-react";
import { toast } from "sonner";
import { api, ApiRequestError } from "../../api/client";

export function VerificationResend({ email, initialCooldown = 0 }: { email: string; initialCooldown?: number }) {
  const [remaining, setRemaining] = React.useState(initialCooldown);
  const [sending, setSending] = React.useState(false);

  React.useEffect(() => {
    if (remaining <= 0) return;
    const timer = window.setTimeout(() => setRemaining((value) => value - 1), 1000);
    return () => window.clearTimeout(timer);
  }, [remaining]);

  async function resend() {
    if (sending || remaining > 0) return;
    setSending(true);
    try {
      const result = await api.resendVerification(email.trim());
      setRemaining(60);
      toast.message(result.message);
    } catch (error) {
      toast.error(error instanceof ApiRequestError ? error.message : "درخواست ارسال مجدد ناموفق بود. دوباره تلاش کنید.");
    } finally {
      setSending(false);
    }
  }

  return (
    <div className="mt-5 rounded-xl border border-primary/20 bg-primary/5 p-4 text-sm leading-7">
      <p>ایمیل تأیید را دریافت نکردید؟ پوشهٔ اسپم و درستی آدرس ایمیل را بررسی کنید.</p>
      <button
        type="button"
        onClick={resend}
        disabled={sending || remaining > 0}
        className="mt-2 inline-flex items-center gap-2 font-bold text-primary disabled:opacity-60 disabled:cursor-wait"
      >
        <RotateCw size={15} className={sending ? "animate-spin" : ""} />
        {sending ? "در حال درخواست…" : remaining > 0 ? `ارسال مجدد تا ${remaining} ثانیه دیگر` : "ارسال مجدد ایمیل تأیید"}
      </button>
    </div>
  );
}
