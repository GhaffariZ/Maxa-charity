import React from "react";
import { Link } from "react-router";
import { Mail, Send, KeyRound, ArrowLeft } from "lucide-react";
import { AuthShell } from "../components/AuthShell";
import { Field, CtaButton, isValidEmail } from "../components/authui";
import { api } from "../../api/client";

export function Forgot() {
  const [email, setEmail] = React.useState("");
  const [err, setErr] = React.useState(false);
  const [status, setStatus] = React.useState<"idle" | "loading">("idle");
  const [sent, setSent] = React.useState(false);
  const [cooldown, setCooldown] = React.useState(0);

  React.useEffect(() => {
    if (cooldown <= 0) return;
    const id = setInterval(() => setCooldown((n) => n - 1), 1000);
    return () => clearInterval(id);
  }, [cooldown]);

  async function send() {
    if (!isValidEmail(email)) {
      setErr(true);
      return;
    }
    setErr(false);
    setStatus("loading");
    try {
      // The API always returns success (no account enumeration), so we always
      // advance to the "check your email" state regardless of whether the
      // address is registered.
      await api.forgotPassword(email.trim());
    } catch {
      /* still show the same confirmation — never reveal account existence */
    } finally {
      setStatus("idle");
      setSent(true);
      setCooldown(30);
    }
  }

  return (
    <AuthShell hero={<ForgotHero />}>
      <Brandbar />

      {!sent ? (
        <div>
          <IconBadge>
            <KeyRound size={28} className="text-primary" />
          </IconBadge>
          <h1 className="text-[1.7rem] font-extrabold tracking-tight mb-1.5">فراموشی رمز عبور</h1>
          <p className="text-sm text-muted-foreground leading-7 mb-7">
            آدرس ایمیل حساب کاربری خود را وارد کنید. یک لینک بازیابی رمز عبور برایتان ارسال می‌کنیم.
          </p>

          <Field
            label="آدرس ایمیل"
            icon={<Mail size={16} />}
            error={err}
            errorMsg="لطفاً یک آدرس ایمیل معتبر وارد کنید."
            inputProps={{
              type: "email",
              dir: "ltr",
              placeholder: "you@example.com",
              autoComplete: "email",
              value: email,
              onChange: (e) => setEmail(e.target.value),
            }}
          />

          <CtaButton
            status={status === "loading" ? "loading" : "idle"}
            onClick={send}
            idleLabel="ارسال لینک بازیابی"
            loadingLabel="در حال ارسال…"
            successLabel=""
            icon={<Send size={17} />}
          />

          <BackLink />
        </div>
      ) : (
        <div className="text-center">
          <IconBadge center>
            <Mail size={34} className="text-primary" />
          </IconBadge>
          <h1 className="text-[1.55rem] font-extrabold tracking-tight mb-2">ایمیل را بررسی کنید</h1>
          <p className="text-sm text-muted-foreground leading-7 mb-5">
            لینک بازیابی رمز عبور به آدرس{" "}
            <span className="font-bold text-foreground" dir="ltr">
              {email.trim()}
            </span>{" "}
            ارسال شد.
          </p>
          <div className="bg-muted/50 rounded-2xl p-4 text-[0.8rem] text-muted-foreground leading-6 mb-5">
            اگر ایمیل را دریافت نکردید، پوشهٔ هرزنامه (Spam) را بررسی کنید و مطمئن شوید آدرس ایمیل
            درست وارد شده است.
          </div>
          <p className="text-[0.85rem] text-muted-foreground">
            ایمیلی دریافت نکردید؟{" "}
            <button
              disabled={cooldown > 0}
              onClick={() => {
                if (cooldown > 0) return;
                setCooldown(30);
                api.forgotPassword(email.trim()).catch(() => {});
              }}
              className="text-primary font-bold hover:underline disabled:text-muted-foreground disabled:no-underline"
            >
              {cooldown > 0 ? `ارسال دوباره (${cooldown} ثانیه)` : "ارسال دوباره"}
            </button>
          </p>

          <BackLink />
        </div>
      )}
    </AuthShell>
  );
}

function ForgotHero() {
  return (
    <div className="flex-1 flex flex-col justify-center gap-7">
      <div className="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
        <KeyRound size={32} />
      </div>
      <h2 className="text-[clamp(1.8rem,2.6vw,2.3rem)] font-extrabold leading-snug">
        نگران نباشید،
        <br />
        <span className="text-secondary">بازیابی</span> آسان است.
      </h2>
      <p className="text-[0.95rem] text-white/70 leading-loose max-w-md">
        در سه گام ساده رمز عبور خود را بازنشانی کنید و به مسیر نیکوکاری بازگردید.
      </p>
      <div className="flex flex-col gap-4">
        {[
          "آدرس ایمیل خود را وارد کنید",
          "روی لینک ارسال‌شده در ایمیل کلیک کنید",
          "رمز عبور جدید خود را تعیین کنید",
        ].map((txt, i) => (
          <div key={i} className="flex items-center gap-3">
            <div className="w-8 h-8 rounded-full bg-white/10 border border-white/15 flex items-center justify-center text-sm font-bold shrink-0">
              {["۱", "۲", "۳"][i]}
            </div>
            <span className="text-[0.9rem] text-white/80">{txt}</span>
          </div>
        ))}
      </div>
    </div>
  );
}

// ---- small shared bits used by Forgot & Reset --------------------------------

export function Brandbar() {
  return (
    <div className="flex items-center gap-2.5 mb-8">
      <div className="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
        <KeyRound size={20} />
      </div>
      <span className="font-extrabold text-lg">خیرین مکسا</span>
    </div>
  );
}

export function IconBadge({ children, center }: { children: React.ReactNode; center?: boolean }) {
  return (
    <div
      className={`w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-5 ${
        center ? "mx-auto" : ""
      }`}
    >
      {children}
    </div>
  );
}

export function BackLink() {
  return (
    <p className="text-center mt-6">
      <Link
        to="/login"
        className="inline-flex items-center gap-1.5 text-[0.85rem] font-semibold text-muted-foreground hover:text-primary transition-colors"
      >
        <ArrowLeft size={15} />
        بازگشت به صفحهٔ ورود
      </Link>
    </p>
  );
}
