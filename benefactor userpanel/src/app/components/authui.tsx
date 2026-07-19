import React from "react";
import { Eye, EyeOff, Loader2 } from "lucide-react";

// Shared form primitives for the auth pages. Styled with the panel's design
// tokens (primary teal, destructive red, Vazirmatn via the global font).

export function Field({
  label,
  icon,
  error,
  errorMsg,
  action,
  inputProps,
}: {
  label: string;
  icon: React.ReactNode;
  error?: boolean;
  errorMsg?: string;
  action?: React.ReactNode;
  inputProps: React.InputHTMLAttributes<HTMLInputElement>;
}) {
  return (
    <div className="mb-[1.1rem]">
      <label className="block text-[0.83rem] font-semibold mb-[0.45rem]">{label}</label>
      <div className="relative">
        <span
          className={`absolute inset-y-0 right-3 flex items-center pointer-events-none transition-colors ${
            error ? "text-destructive" : "text-muted-foreground"
          }`}
        >
          {icon}
        </span>
        <input
          {...inputProps}
          className={`w-full h-[2.9rem] ps-3 pe-10 text-[0.88rem] bg-card border-[1.5px] rounded-[0.7rem] outline-none transition-all text-right placeholder:text-muted-foreground placeholder:text-[0.85rem] ${
            error
              ? "border-destructive focus:ring-[3px] focus:ring-destructive/10"
              : "border-border focus:border-primary focus:ring-[3px] focus:ring-primary/10"
          } ${action ? "pe-10 ps-10" : ""}`}
        />
        {action ? (
          <span className="absolute inset-y-0 left-3 flex items-center">{action}</span>
        ) : null}
      </div>
      {error && errorMsg ? (
        <span className="block text-[0.77rem] text-destructive font-medium mt-[0.35rem]">
          {errorMsg}
        </span>
      ) : null}
    </div>
  );
}

export function PasswordToggle({
  shown,
  onToggle,
}: {
  shown: boolean;
  onToggle: () => void;
}) {
  return (
    <button
      type="button"
      onClick={onToggle}
      aria-label="نمایش رمز عبور"
      className="text-muted-foreground hover:text-foreground transition-colors p-1 flex"
    >
      {shown ? <EyeOff size={16} /> : <Eye size={16} />}
    </button>
  );
}

type Status = "idle" | "loading" | "success";

export function CtaButton({
  status,
  idleLabel,
  loadingLabel,
  successLabel,
  icon,
  ...rest
}: {
  status: Status;
  idleLabel: string;
  loadingLabel: string;
  successLabel: string;
  icon: React.ReactNode;
} & React.ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      {...rest}
      disabled={status !== "idle" || rest.disabled}
      className={`w-full h-[2.9rem] rounded-[0.7rem] text-white text-[0.95rem] font-bold flex items-center justify-center gap-2 transition-all ${
        status === "success"
          ? "bg-accent"
          : "bg-primary hover:bg-primary-dark hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-px active:translate-y-0"
      } disabled:cursor-default`}
    >
      {status === "loading" ? (
        <>
          <Loader2 size={17} className="animate-spin" />
          {loadingLabel}
        </>
      ) : status === "success" ? (
        successLabel
      ) : (
        <>
          {icon}
          {idleLabel}
        </>
      )}
    </button>
  );
}

const STRENGTH_COLORS = ["#d4183d", "#f4a61e", "#4fb2b0", "#007b7a"];
const STRENGTH_NAMES = ["ضعیف", "متوسط", "خوب", "قوی"];

export function scorePassword(v: string): number {
  let s = 0;
  if (v.length >= 8) s++;
  if (/[A-Z]/.test(v)) s++;
  if (/[0-9]/.test(v)) s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;
  return s;
}

export function PasswordStrength({ value }: { value: string }) {
  if (!value) return null;
  const s = scorePassword(value);
  return (
    <div className="-mt-[0.4rem] mb-[0.95rem]">
      <div className="flex gap-[3px] mb-[5px]">
        {[1, 2, 3, 4].map((i) => (
          <div
            key={i}
            className="flex-1 h-[3px] rounded-sm transition-colors"
            style={{ background: i <= s ? STRENGTH_COLORS[s - 1] : "var(--border)" }}
          />
        ))}
      </div>
      {s > 0 ? (
        <span className="text-[0.75rem] font-semibold" style={{ color: STRENGTH_COLORS[s - 1] }}>
          قدرت رمز عبور: {STRENGTH_NAMES[s - 1]}
        </span>
      ) : null}
    </div>
  );
}

export const isValidEmail = (v: string) => /\S+@\S+\.\S+/.test(v.trim());
