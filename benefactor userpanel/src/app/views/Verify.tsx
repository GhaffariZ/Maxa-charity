import React from "react";
import { Link, useSearchParams } from "react-router";
import { CheckCircle2, XCircle, Loader2, LogIn, MailCheck } from "lucide-react";
import { AuthShell } from "../components/AuthShell";
import { Brandbar, IconBadge } from "./Forgot";
import { api, ApiRequestError } from "../../api/client";

export function Verify() {
  const [params] = useSearchParams();
  const token = params.get("token") ?? "";
  const [status, setStatus] = React.useState<"loading" | "success" | "error">("loading");
  const [message, setMessage] = React.useState("");

  React.useEffect(() => {
    let cancelled = false;
    if (!token) {
      setStatus("error");
      setMessage("لینک تأیید نامعتبر است.");
      return;
    }
    (async () => {
      try {
        const res = await api.verifyEmail(token);
        if (!cancelled) {
          setStatus("success");
          setMessage(res.message);
        }
      } catch (e) {
        if (!cancelled) {
          setStatus("error");
          setMessage(e instanceof ApiRequestError ? e.message : "تأیید ایمیل ناموفق بود.");
        }
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [token]);

  return (
    <AuthShell hero={<VerifyHero />}>
      <Brandbar />
      <div className="text-center">
        {status === "loading" && (
          <>
            <IconBadge center>
              <Loader2 size={32} className="text-primary animate-spin" />
            </IconBadge>
            <h1 className="text-[1.55rem] font-extrabold tracking-tight mb-2">در حال تأیید ایمیل…</h1>
            <p className="text-sm text-muted-foreground leading-7">لطفاً چند لحظه صبر کنید.</p>
          </>
        )}

        {status === "success" && (
          <>
            <IconBadge center>
              <CheckCircle2 size={36} className="text-primary" />
            </IconBadge>
            <h1 className="text-[1.55rem] font-extrabold tracking-tight mb-2">ایمیل تأیید شد</h1>
            <p className="text-sm text-muted-foreground leading-7 mb-6">{message}</p>
            <Link
              to="/login"
              className="inline-flex items-center justify-center gap-2 w-full h-[2.9rem] rounded-[0.7rem] bg-primary text-white font-bold hover:bg-primary-dark transition-all"
            >
              <LogIn size={17} className="scale-x-[-1]" />
              رفتن به صفحهٔ ورود
            </Link>
          </>
        )}

        {status === "error" && (
          <>
            <IconBadge center>
              <XCircle size={36} className="text-destructive" />
            </IconBadge>
            <h1 className="text-[1.55rem] font-extrabold tracking-tight mb-2">تأیید ناموفق بود</h1>
            <p className="text-sm text-muted-foreground leading-7 mb-6">{message}</p>
            <Link
              to="/login"
              className="inline-flex items-center justify-center gap-2 w-full h-[2.9rem] rounded-[0.7rem] bg-muted text-foreground font-bold hover:bg-muted/70 transition-all"
            >
              بازگشت به صفحهٔ ورود
            </Link>
          </>
        )}
      </div>
    </AuthShell>
  );
}

function VerifyHero() {
  return (
    <div className="flex-1 flex flex-col justify-center gap-7">
      <div className="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
        <MailCheck size={32} />
      </div>
      <h2 className="text-[clamp(1.8rem,2.6vw,2.3rem)] font-extrabold leading-snug">
        فقط یک قدم
        <br />
        تا <span className="text-secondary">آغاز</span> مانده.
      </h2>
      <p className="text-[0.95rem] text-white/70 leading-loose max-w-md">
        با تأیید ایمیل، حساب کاربری شما فعال می‌شود و می‌توانید سفر نیکوکاری خود را آغاز کنید.
      </p>
    </div>
  );
}
