// Persian (Farsi) number/locale helpers shared across views.

const FA_DIGITS = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];

/** Convert ASCII digits in a string to Persian digits. */
export function toFaDigits(input: string | number): string {
  return String(input).replace(/[0-9]/g, (d) => FA_DIGITS[Number(d)]);
}

/** Format a number with thousands separators, then Persian digits. */
export function faNumber(value: number): string {
  return toFaDigits(value.toLocaleString("en-US"));
}

/** Format a Toman amount, e.g. "۱۲٬۴۰۰٬۰۰۰ تومان". */
export function faToman(value: number): string {
  return `${faNumber(value)} تومان`;
}

/**
 * Format an ISO/SQL UTC datetime as a Jalali (Persian) date using the built-in
 * Intl calendar — no extra dependency. Falls back to the raw string on error.
 */
export function faDate(utc: string | null | undefined): string {
  if (!utc) return "—";
  // SQL "YYYY-MM-DD HH:MM:SS" (UTC) → ISO so Date parses it as UTC.
  const iso = utc.includes("T") ? utc : utc.replace(" ", "T") + "Z";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return utc;
  try {
    return new Intl.DateTimeFormat("fa-IR-u-ca-persian", {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
    }).format(d);
  } catch {
    return utc;
  }
}
