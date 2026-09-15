import React from "react";
import { Link, useNavigate, useLocation } from "react-router";
import {
  Mail,
  Lock,
  User,
  LogIn,
  UserPlus,
  HeartHandshake,
  ShieldCheck,
  CheckCircle2,
  Star,
  Smartphone,
  KeyRound,
  RotateCw,
  ArrowRight,
} from "lucide-react";
import { toast } from "sonner";
import { AuthShell } from "../components/AuthShell";
import {
  Field,
  PasswordToggle,
  CtaButton,
  PasswordStrength,
  isValidEmail,
} from "../components/authui";
import { useAuth } from "../../contexts/AuthContext";
import { api, ApiRequestError } from "../../api/client";

type Status = "idle" | "loading" | "success";

export function Login() {
  const [tab, setTab] = React.useState<"in" | "up">("in");

  return (
    <AuthShell hero={<LoginHero />}>
      {/* Tabs */}
      <div className="flex bg-muted rounded-xl p-1 gap-0.5 mb-8">
        {(["in", "up"] as const).map((t) => (
          <button
            key={t}
            onClick={() => setTab(t)}
            className={`flex-1 h-9 rounded-lg text-sm transition-all ${
              tab === t
                ? "bg-card text-primary font-bold shadow-sm"
                : "text-muted-foreground font-medium hover:text-foreground"
            }`}
          >
            {t === "in" ? "ورود" : "ثبت‌نام"}
          </button>
        ))}
      </div>

      {tab === "in" ? <SignInPanel /> : <SignUpPanel onSwitch={() => setTab("in")} />}
    </AuthShell>
  );
}

function SignInPanel() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login, loginWithOtp } = useAuth();

  // Parse returnUrl from query params if available
  const queryParams = new URLSearchParams(location.search);
  const returnUrl = queryParams.get("returnUrl");
  const from = returnUrl || (location.state as { from?: { pathname?: string } })?.from?.pathname || "/";

  // Login mode: "otp" (phone) vs "password" (email)
  const [method, setMethod] = React.useState<"otp" | "password">("otp");

  // Email+Password state
  const [email, setEmail] = React.useState("");
  const [pw, setPw] = React.useState("");
  const [showPw, setShowPw] = React.useState(false);
  const [errors, setErrors] = React.useState<{ email?: boolean; pw?: boolean; phone?: boolean; code?: boolean }>({});
  const [status, setStatus] = React.useState<Status>("idle");

  // Phone OTP state
  const [phone, setPhone] = React.useState("");
  const [otpCode, setOtpCode] = React.useState("");
  const [otpStep, setOtpStep] = React.useState<"phone" | "code">("phone");
  const [countdown, setCountdown] = React.useState(120);
  const [canResend, setCanResend] = React.useState(false);

  // Countdown timer effect
  React.useEffect(() => {
    let timer: any;
    if (otpStep === "code" && countdown > 0) {
      timer = setInterval(() => setCountdown((c) => c - 1), 1000);
    } else if (countdown <= 0) {
      setCanResend(true);
    }
    return () => clearInterval(timer);
  }, [otpStep, countdown]);

  function handleSuccessRedirect() {
    setStatus("success");
    setTimeout(() => {
      const redirect = localStorage.getItem("redirect_to_patientintake");
      if (redirect) {
        localStorage.removeItem("redirect_to_patientintake");
        window.location.href = redirect;
      } else {
        if (from.startsWith("http") || from.startsWith("/stand-order.php")) {
          window.location.href = from;
        } else {
          navigate(from, { replace: true });
        }
      }
    }, 600);
  }

  async function submitPasswordLogin() {
    const next = { email: !isValidEmail(email), pw: !pw };
    setErrors(next);
    if (next.email || next.pw) return;

    setStatus("loading");
    try {
      await login(email.trim(), pw);
      handleSuccessRedirect();
    } catch (e) {
      setStatus("idle");
      if (e instanceof ApiRequestError) {
        if (e.code === "email_unverified") {
          toast.error("ابتدا ایمیل خود را تأیید کنید.", {
            description: "لینک تأیید به ایمیل شما ارسال شده است.",
          });
        } else if (e.code === "account_locked" || e.code === "rate_limited") {
          toast.error(e.message);
        } else {
          setErrors({ email: true, pw: true });
          toast.error(e.message || "ایمیل یا رمز عبور نادرست است.");
        }
      } else {
        toast.error("خطا در برقراری ارتباط با سرور.");
      }
    }
  }

  async function handleSendOtp() {
    const valid = /^09[0-9]{9}$/.test(phone.trim());
    if (!valid) {
      setErrors({ phone: true });
      toast.error("لطفاً یک شماره موبایل معتبر (مانند 09123456789) وارد کنید.");
      return;
    }
    setErrors({});
    setStatus("loading");

    try {
      const res = await api.sendOtp(phone.trim(), "login");
      setStatus("idle");
      setOtpStep("code");
      setCountdown(res.resend_after || 120);
      setCanResend(false);
      if (res.debug_code) {
        setOtpCode(res.debug_code);
      }
      toast.success("کد تأیید پیامک شد.");
    } catch (e) {
      setStatus("idle");
      toast.error(e instanceof ApiRequestError ? e.message : "خطا در ارسال پیامک.");
    }
  }

  async function submitOtpLogin() {
    if (otpCode.trim().length < 4) {
      setErrors({ code: true });
      toast.error("لطفاً کد تایید ۵ رقمی را وارد کنید.");
      return;
    }

    setStatus("loading");
    try {
      await loginWithOtp(phone.trim(), otpCode.trim());
      handleSuccessRedirect();
    } catch (e) {
      setStatus("idle");
      toast.error(e instanceof ApiRequestError ? e.message : "کد وارد شده صحیح نیست.");
    }
  }

  return (
    <div>
      <div className="mb-7">
        <h2 className="text-[1.7rem] font-extrabold tracking-tight mb-1.5">خوش آمدید</h2>
        <p className="text-sm text-muted-foreground leading-7">
          برای ادامهٔ مسیر نیکوکاری، وارد حساب کاربری خود شوید.
        </p>
      </div>

      {/* Sub-toggle: OTP vs Password */}
      <div className="flex border border-border/60 bg-surface/50 rounded-xl p-1 mb-6 text-xs font-bold gap-1">
        <button
          type="button"
          onClick={() => {
            setMethod("otp");
            setOtpStep("phone");
          }}
          className={`flex-1 py-2 rounded-lg flex items-center justify-center gap-1.5 transition-all ${
            method === "otp"
              ? "bg-primary text-white shadow-sm"
              : "text-muted-foreground hover:text-foreground"
          }`}
        >
          <Smartphone size={15} />
          ورود با شماره موبایل (پیامک)
        </button>
        <button
          type="button"
          onClick={() => setMethod("password")}
          className={`flex-1 py-2 rounded-lg flex items-center justify-center gap-1.5 transition-all ${
            method === "password"
              ? "bg-primary text-white shadow-sm"
              : "text-muted-foreground hover:text-foreground"
          }`}
        >
          <KeyRound size={15} />
          ورود با رمز عبور
        </button>
      </div>

      {method === "otp" ? (
        otpStep === "phone" ? (
          <div>
            <Field
              label="شماره تلفن همراه"
              icon={<Smartphone size={16} />}
              error={errors.phone}
              errorMsg="شماره همراه باید ۱۱ رقم با فرمت 0912... باشد."
              inputProps={{
                type: "tel",
                dir: "ltr",
                placeholder: "09123456789",
                autoComplete: "tel",
                value: phone,
                onChange: (e) => setPhone(e.target.value),
              }}
            />

            <CtaButton
              status={status}
              onClick={handleSendOtp}
              idleLabel="دریافت کد تأیید"
              loadingLabel="در حال ارسال پیامک…"
              successLabel="ارسال شد!"
              icon={<LogIn size={17} className="scale-x-[-1]" />}
            />
          </div>
        ) : (
          <div>
            <div className="bg-primary/5 border border-primary/20 rounded-2xl p-4 mb-5 text-sm text-center">
              کد ۵ رقمی به شماره <strong dir="ltr" className="font-bold text-primary">{phone}</strong> پیامک شد.
              <button
                type="button"
                onClick={() => setOtpStep("phone")}
                className="block mx-auto mt-2 text-xs font-bold text-primary hover:underline"
              >
                ویرایش شماره
              </button>
            </div>

            <Field
              label="کد تأیید پیامک‌شده"
              icon={<KeyRound size={16} />}
              error={errors.code}
              errorMsg="لطفاً کد تایید را وارد کنید."
              inputProps={{
                type: "text",
                dir: "ltr",
                placeholder: "• • • • •",
                maxLength: 6,
                value: otpCode,
                onChange: (e) => setOtpCode(e.target.value),
              }}
            />

            <div className="flex items-center justify-between text-xs text-muted-foreground mb-6">
              {canResend ? (
                <button
                  type="button"
                  onClick={handleSendOtp}
                  className="font-bold text-primary hover:underline flex items-center gap-1"
                >
                  <RotateCw size={13} />
                  ارسال مجدد کد
                </button>
              ) : (
                <span>ارسال مجدد تا {countdown} ثانیه دیگر</span>
              )}
            </div>

            <CtaButton
              status={status}
              onClick={submitOtpLogin}
              idleLabel="ورود به حساب کاربری"
              loadingLabel="در حال بررسی…"
              successLabel="ورود موفق!"
              icon={<LogIn size={17} className="scale-x-[-1]" />}
            />
          </div>
        )
      ) : (
        <div>
          <Field
            label="آدرس ایمیل"
            icon={<Mail size={16} />}
            error={errors.email}
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

          <Field
            label="رمز عبور"
            icon={<Lock size={16} />}
            error={errors.pw}
            errorMsg="لطفاً رمز عبور خود را وارد کنید."
            action={<PasswordToggle shown={showPw} onToggle={() => setShowPw((s) => !s)} />}
            inputProps={{
              type: showPw ? "text" : "password",
              placeholder: "رمز عبور خود را وارد کنید",
              autoComplete: "current-password",
              value: pw,
              onChange: (e) => setPw(e.target.value),
            }}
          />

          <div className="flex items-center justify-between mb-6">
            <label className="flex items-center gap-2 text-sm cursor-pointer select-none">
              <input type="checkbox" className="w-4 h-4 accent-primary rounded" />
              <span>مرا به خاطر بسپار</span>
            </label>
            <Link to="/forgot" className="text-[0.83rem] font-semibold text-primary hover:underline">
              فراموشی رمز عبور؟
            </Link>
          </div>

          <CtaButton
            status={status}
            onClick={submitPasswordLogin}
            idleLabel="ورود به پنل"
            loadingLabel="در حال ورود…"
            successLabel="ورود موفق!"
            icon={<LogIn size={17} className="scale-x-[-1]" />}
          />
        </div>
      )}
    </div>
  );
}

function SignUpPanel({ onSwitch }: { onSwitch: () => void }) {
  const { register } = useAuth();
  const [firstName, setFirstName] = React.useState("");
  const [lastName, setLastName] = React.useState("");
  const [email, setEmail] = React.useState("");
  const [pw, setPw] = React.useState("");
  const [showPw, setShowPw] = React.useState(false);
  const [terms, setTerms] = React.useState(true);
  const [termsErr, setTermsErr] = React.useState(false);
  const [status, setStatus] = React.useState<Status>("idle");
  const [errors, setErrors] = React.useState<{ email?: boolean; pw?: boolean }>({});

  async function submit() {
    if (!terms) {
      setTermsErr(true);
      setTimeout(() => setTermsErr(false), 2000);
      return;
    }
    const next = { email: !isValidEmail(email), pw: pw.length < 10 };
    setErrors(next);
    if (next.email || next.pw) {
      if (next.pw) toast.error("رمز عبور باید حداقل ۱۰ کاراکتر و قوی باشد.");
      return;
    }

    setStatus("loading");
    try {
      const res = await register({
        email: email.trim(),
        password: pw,
        first_name: firstName.trim() || undefined,
        last_name: lastName.trim() || undefined,
      });
      setStatus("success");
      toast.success(res.message || "حساب ساخته شد!", {
        description: "لینک تأیید به ایمیل شما ارسال شد.",
      });
    } catch (e) {
      setStatus("idle");
      if (e instanceof ApiRequestError) {
        if (e.fields?.password) {
          setErrors({ pw: true });
          toast.error(e.fields.password);
        } else if (e.fields?.email) {
          setErrors({ email: true });
          toast.error(e.fields.email);
        } else {
          toast.error(e.message);
        }
      } else {
        toast.error("خطا در برقراری ارتباط با سرور.");
      }
    }
  }

  return (
    <div>
      <div className="mb-7">
        <h2 className="text-[1.7rem] font-extrabold tracking-tight mb-1.5">به ما بپیوندید</h2>
        <p className="text-sm text-muted-foreground leading-7">
          حساب کاربری بسازید و در ایجاد تغییری ماندگار سهیم شوید.
        </p>
      </div>

      <div className="grid grid-cols-2 gap-2.5">
        <Field
          label="نام"
          icon={<User size={16} />}
          inputProps={{
            type: "text",
            placeholder: "سارا",
            autoComplete: "given-name",
            value: firstName,
            onChange: (e) => setFirstName(e.target.value),
          }}
        />
        <Field
          label="نام خانوادگی"
          icon={<User size={16} />}
          inputProps={{
            type: "text",
            placeholder: "رشیدی",
            autoComplete: "family-name",
            value: lastName,
            onChange: (e) => setLastName(e.target.value),
          }}
        />
      </div>

      <Field
        label="آدرس ایمیل"
        icon={<Mail size={16} />}
        error={errors.email}
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

      <Field
        label="رمز عبور"
        icon={<Lock size={16} />}
        error={errors.pw}
        errorMsg="رمز عبور باید حداقل ۱۰ کاراکتر و قوی باشد."
        action={<PasswordToggle shown={showPw} onToggle={() => setShowPw((s) => !s)} />}
        inputProps={{
          type: showPw ? "text" : "password",
          placeholder: "حداقل ۱۰ کاراکتر",
          autoComplete: "new-password",
          value: pw,
          onChange: (e) => setPw(e.target.value),
        }}
      />
      <PasswordStrength value={pw} />

      <div className="mb-[1.15rem]">
        <label className="flex items-start gap-2 cursor-pointer select-none">
          <input
            type="checkbox"
            className="w-4 h-4 mt-0.5 accent-primary rounded"
            checked={terms}
            onChange={(e) => setTerms(e.target.checked)}
          />
          <span className={`text-[0.83rem] leading-relaxed ${termsErr ? "text-destructive" : "text-muted-foreground"}`}>
            با <a className="text-primary font-semibold">قوانین استفاده</a> و{" "}
            <a className="text-primary font-semibold">سیاست حریم خصوصی</a> موافقم
          </span>
        </label>
      </div>

      <CtaButton
        status={status}
        onClick={submit}
        idleLabel="ایجاد حساب کاربری"
        loadingLabel="در حال ایجاد حساب…"
        successLabel="حساب ساخته شد!"
        icon={<UserPlus size={17} />}
      />

      <p className="text-center text-[0.855rem] text-muted-foreground mt-6">
        حساب کاربری دارید؟{" "}
        <button onClick={onSwitch} className="text-primary font-bold hover:underline">
          وارد شوید ←
        </button>
      </p>
    </div>
  );
}

function LoginHero() {
  return (
    <>
      <div className="flex items-center gap-3">
        <div className="w-11 h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center">
          <HeartHandshake size={24} />
        </div>
        <span className="text-xl font-extrabold">خیرین مکسا</span>
      </div>

      <div className="flex-1 flex flex-col justify-center gap-7">
        <h1 className="text-[clamp(2rem,3vw,2.55rem)] font-extrabold leading-snug tracking-tight">
          بخشیدن با <span className="text-secondary">هدف</span>،<br />
          دگرگونی با <span className="text-secondary">قلب</span>
        </h1>
        <p className="text-[0.95rem] text-white/70 leading-loose max-w-md">
          به جامعه‌ای از خیرین بپیوندید که باور دارند هر کار نیک، بزرگ یا کوچک، موجی از تغییر در
          زندگی مردم و جامعه می‌آفریند.
        </p>

        <div className="flex">
          {[
            ["۱۴٬۰۰۰+", "زندگیِ دگرگون‌شده"],
            ["۳۸۰+", "پروژهٔ تأمین‌شده"],
            ["٪۹۷", "منابع تخصیص‌یافته"],
          ].map(([num, lbl], i) => (
            <div
              key={lbl}
              className={`flex flex-col gap-1 pe-6 ${i > 0 ? "ps-6 border-s border-white/15" : ""}`}
            >
              <span className="text-2xl font-extrabold">{num}</span>
              <span className="text-xs font-semibold text-white/50">{lbl}</span>
            </div>
          ))}
        </div>

        <div className="bg-white/[0.08] backdrop-blur-md border border-white/[0.13] rounded-2xl p-5 flex flex-col gap-3.5">
          <div className="text-4xl leading-none text-secondary h-4">«</div>
          <p className="text-[0.9rem] text-white/85 leading-loose">
            حضور در خیرین مکسا به من نشان داد که حتی کمک‌های کوچک هم می‌توانند موجی از تغییر در یک
            جامعه ایجاد کنند. این پلتفرم، بخشیدن را برایم واقعی و ملموس کرد.
          </p>
          <div className="flex items-center gap-2.5">
            <div className="w-9 h-9 rounded-full bg-secondary flex items-center justify-center text-sm font-bold">
              س‌ر
            </div>
            <div>
              <div className="text-[0.87rem] font-bold">سارا رشیدی</div>
              <div className="text-[0.74rem] text-white/50">خیر از سال ۱۴۰۱ · ۲۴ کمک</div>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-5 flex-wrap pt-1">
          {[
            [<Star size={11} key="s" />, "پلتفرم معتمد"],
            [<ShieldCheck size={11} key="l" />, "امن و محرمانه"],
            [<CheckCircle2 size={11} key="c" />, "نهاد تأییدشده"],
          ].map(([ic, lbl]) => (
            <div key={String(lbl)} className="flex items-center gap-1.5 text-[0.76rem] font-semibold text-white/50">
              <span className="w-[18px] h-[18px] rounded-full bg-white/10 flex items-center justify-center">
                {ic}
              </span>
              {lbl}
            </div>
          ))}
        </div>
      </div>
    </>
  );
}
