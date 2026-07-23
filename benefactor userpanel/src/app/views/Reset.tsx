import React from "react";
import { useNavigate, useSearchParams } from "react-router";
import { Lock, Check, LogIn, ShieldCheck } from "lucide-react";
import { toast } from "sonner";
import { AuthShell } from "../components/AuthShell";
import {
  Field,
  PasswordToggle,
  CtaButton,
  PasswordStrength,
} from "../components/authui";
import { Brandbar, IconBadge, BackLink } from "./Forgot";
import { api, ApiRequestError } from "../../api/client";

export function Reset() {
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const token = params.get("token") ?? "";

  const [pw, setPw] = React.useState("");
  const [pw2, setPw2] = React.useState("");
  const [showPw, setShowPw] = React.useState(false);
  const [showPw2, setShowPw2] = React.useState(false);
  const [errors, setErrors] = React.useState<{ pw?: boolean; pw2?: boolean }>({});
  const [status, setStatus] = React.useState<"idle" | "loading">("idle");
  const [done, setDone] = React.useState(false);

  const reqs = [
    { ok: pw.length >= 10, label: "حداقل ۱۰ کاراکتر" },
    { ok: /[A-Z]/.test(pw) && /[a-z]/.test(pw), label: "شامل حروف بزرگ و کوچک" },
    { ok: /[0-9]/.test(pw), label: "شامل عدد" },
  ];

  async function submit() {
    const next: { pw?: boolean; pw2?: boolean } = {};
    if (pw.length < 10) next.pw = true;
    else if (pw !== pw2) next.pw2 = true;
    setErrors(next);
    if (next.pw || next.pw2) return;

    if (!token) {
      toast.error("لینک بازنشانی نامعتبر است. لطفاً دوباره درخواست دهید.");
      return;
    }

    setStatus("loading");
    try {
      await api.resetPassword(token, pw);
      setStatus("idle");
      setDone(true);
    } catch (e) {
      setStatus("idle");
      if (e instanceof ApiRequestError) {
        if (e.fields?.password) {
          setErrors({ pw: true });
          toast.error(e.fields.password);
        } else {
          toast.error(e.message);
        }
      } else {
        toast.error("خطا در برقراری ارتباط با سرور.");
      }
    }
  }

  return (
    <AuthShell hero={<ResetHero />}>
      <Brandbar />

      {!done ? (
        <div>
          <IconBadge>
            <Lock size={28} className="text-primary" />
          </IconBadge>
          <h1 className="text-[1.7rem] font-extrabold tracking-tight mb-1.5">تعیین رمز عبور جدید</h1>
          <p className="text-sm text-muted-foreground leading-7 mb-7">
            یک رمز عبور قوی و جدید برای حساب کاربری خود انتخاب کنید.
          </p>

          <Field
            label="رمز عبور جدید"
            icon={<Lock size={16} />}
            error={errors.pw}
            errorMsg="رمز عبور باید حداقل ۱۰ کاراکتر باشد."
            action={<PasswordToggle shown={showPw} onToggle={() => setShowPw((s) => !s)} />}
            inputProps={{
              type: showPw ? "text" : "password",
              placeholder: "رمز عبور جدید",
              autoComplete: "new-password",
              value: pw,
              onChange: (e) => setPw(e.target.value),
            }}
          />
          <PasswordStrength value={pw} />

          <Field
            label="تکرار رمز عبور"
            icon={<Lock size={16} />}
            error={errors.pw2}
            errorMsg="رمز عبور و تکرار آن یکسان نیستند."
            action={<PasswordToggle shown={showPw2} onToggle={() => setShowPw2((s) => !s)} />}
            inputProps={{
              type: showPw2 ? "text" : "password",
              placeholder: "رمز عبور را دوباره وارد کنید",
              autoComplete: "new-password",
              value: pw2,
              onChange: (e) => setPw2(e.target.value),
            }}
          />

          <ul className="flex flex-col gap-2 my-4">
            {reqs.map((r) => (
              <li key={r.label} className="flex items-center gap-2 text-[0.82rem]">
                <span
                  className={`w-[18px] h-[18px] rounded-full flex items-center justify-center transition-colors ${
                    r.ok ? "bg-primary text-white" : "bg-muted text-muted-foreground"
                  }`}
                >
                  <Check size={11} strokeWidth={3} />
                </span>
                <span className={r.ok ? "text-foreground" : "text-muted-foreground"}>{r.label}</span>
              </li>
            ))}
          </ul>

          <CtaButton
            status={status === "loading" ? "loading" : "idle"}
            onClick={submit}
            idleLabel="ثبت رمز عبور جدید"
            loadingLabel="در حال ذخیره…"
            successLabel=""
            icon={<Check size={17} strokeWidth={2.4} />}
          />

          <BackLink />
        </div>
      ) : (
        <div className="text-center">
          <IconBadge center>
            <Check size={36} strokeWidth={2.4} className="text-primary" />
          </IconBadge>
          <h1 className="text-[1.55rem] font-extrabold tracking-tight mb-2">رمز عبور تغییر کرد</h1>
          <p className="text-sm text-muted-foreground leading-7 mb-6">
            رمز عبور شما با موفقیت بازنشانی شد. اکنون می‌توانید با رمز جدید وارد حساب کاربری خود شوید.
          </p>
          <CtaButton
            status="idle"
            onClick={() => navigate("/login")}
            idleLabel="رفتن به صفحهٔ ورود"
            loadingLabel=""
            successLabel=""
            icon={<LogIn size={17} className="scale-x-[-1]" />}
          />
        </div>
      )}
    </AuthShell>
  );
}

function ResetHero() {
  return (
    <div className="flex-1 flex flex-col justify-center gap-7">
      <div className="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
        <ShieldCheck size={32} />
      </div>
      <h2 className="text-[clamp(1.8rem,2.6vw,2.3rem)] font-extrabold leading-snug">
        یک رمز <span className="text-secondary">امن</span>
        <br />
        انتخاب کنید.
      </h2>
      <p className="text-[0.95rem] text-white/70 leading-loose max-w-md">
        رمز عبور قوی، اولین خط دفاع از حساب کاربری و کمک‌های شماست.
      </p>
      <div className="flex flex-col gap-4">
        {[
          "از ترکیب حروف، اعداد و نمادها استفاده کنید",
          "از رمزهای تکراری و قابل حدس پرهیز کنید",
          "رمز عبور خود را با کسی به اشتراک نگذارید",
        ].map((txt, i) => (
          <div key={i} className="flex items-center gap-3 text-[0.9rem] text-white/80">
            <Check size={18} className="shrink-0" />
            {txt}
          </div>
        ))}
      </div>
    </div>
  );
}
