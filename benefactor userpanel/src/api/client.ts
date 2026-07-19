// ---------------------------------------------------------------------------
//  API client for the Maksa backend.
//
//  - Base URL from VITE_API_URL (defaults to "/api" — same origin, so a future
//    domain change needs no rebuild).
//  - The access token lives in memory only (never localStorage), so it can't be
//    read by XSS. The refresh token is an httpOnly cookie the browser sends
//    automatically with `credentials: "include"`.
//  - On a 401 we transparently try ONE refresh, then retry the original request.
//  - Every error is normalized to { code, message, fields } so the UI can rely
//    on a single shape.
// ---------------------------------------------------------------------------

const API_BASE = (import.meta.env.VITE_API_URL ?? "/api").replace(/\/$/, "");

// In-memory access token (module scope — cleared on full page reload).
let accessToken: string | null = null;
export function setAccessToken(token: string | null): void {
  accessToken = token;
}
export function getAccessToken(): string | null {
  return accessToken;
}

export interface ApiError {
  code: string;
  message: string;
  /** Per-field validation messages, when code === "validation_failed". */
  fields?: Record<string, string>;
  status: number;
}

export class ApiRequestError extends Error {
  code: string;
  fields?: Record<string, string>;
  status: number;
  constructor(error: ApiError) {
    super(error.message);
    this.name = "ApiRequestError";
    this.code = error.code;
    this.fields = error.fields;
    this.status = error.status;
  }
}

interface RequestOptions {
  method?: "GET" | "POST" | "PATCH" | "PUT" | "DELETE";
  body?: unknown;
  /** Set false for endpoints that must not trigger a refresh-on-401 (e.g. login). */
  retryOnAuthFail?: boolean;
  /** Pass a FormData to send multipart (file upload); skips JSON encoding. */
  formData?: FormData;
  signal?: AbortSignal;
}

// A single shared in-flight refresh so concurrent 401s don't stampede.
let refreshPromise: Promise<boolean> | null = null;

async function doRefresh(): Promise<boolean> {
  if (!refreshPromise) {
    refreshPromise = (async () => {
      try {
        const res = await fetch(`${API_BASE}/auth/refresh`, {
          method: "POST",
          credentials: "include",
          headers: { "Content-Type": "application/json" },
        });
        if (!res.ok) return false;
        const json = await res.json();
        const token = json?.data?.access_token;
        if (typeof token === "string") {
          setAccessToken(token);
          return true;
        }
        return false;
      } catch {
        return false;
      } finally {
        // Allow the next refresh cycle.
        setTimeout(() => (refreshPromise = null), 0);
      }
    })();
  }
  return refreshPromise;
}

async function rawRequest<T>(path: string, opts: RequestOptions): Promise<T> {
  const headers: Record<string, string> = {};
  if (accessToken) headers["Authorization"] = `Bearer ${accessToken}`;

  let body: BodyInit | undefined;
  if (opts.formData) {
    body = opts.formData; // browser sets multipart boundary
  } else if (opts.body !== undefined) {
    headers["Content-Type"] = "application/json";
    body = JSON.stringify(opts.body);
  }

  const res = await fetch(`${API_BASE}${path}`, {
    method: opts.method ?? "GET",
    credentials: "include",
    headers,
    body,
    signal: opts.signal,
  });

  if (res.status === 204) return undefined as T;

  let json: any = null;
  try {
    json = await res.json();
  } catch {
    /* non-JSON (shouldn't happen for the API) */
  }

  if (!res.ok || json?.success === false) {
    const err: ApiError = {
      code: json?.error?.code ?? "unknown_error",
      message: json?.error?.message ?? "خطایی رخ داد. لطفاً دوباره تلاش کنید.",
      fields: json?.error?.fields,
      status: res.status,
    };
    throw new ApiRequestError(err);
  }

  return json?.data as T;
}

export async function apiRequest<T = unknown>(path: string, opts: RequestOptions = {}): Promise<T> {
  const retryOnAuthFail = opts.retryOnAuthFail ?? true;
  try {
    return await rawRequest<T>(path, opts);
  } catch (e) {
    if (
      e instanceof ApiRequestError &&
      e.status === 401 &&
      retryOnAuthFail &&
      !path.startsWith("/auth/")
    ) {
      const ok = await doRefresh();
      if (ok) {
        return rawRequest<T>(path, opts);
      }
      setAccessToken(null);
    }
    throw e;
  }
}

// ---- Typed endpoint helpers ------------------------------------------------

export interface UserDto {
  id: number;
  email: string;
  email_verified: boolean;
  status: string;
  first_name: string | null;
  last_name: string | null;
  phone: string | null;
  avatar_url: string | null;
  postal_address: string | null;
  kindness_points: number;
  tier: { slug: string | null; name: string | null };
  member_since: string | null;
  last_login_at: string | null;
}

export const api = {
  // auth
  login: (email: string, password: string) =>
    apiRequest<{ access_token: string; expires_in: number }>("/auth/login", {
      method: "POST",
      body: { email, password },
      retryOnAuthFail: false,
    }),
  register: (payload: { email: string; password: string; first_name?: string; last_name?: string }) =>
    apiRequest<{ message: string }>("/auth/register", { method: "POST", body: payload, retryOnAuthFail: false }),
  verifyEmail: (token: string) =>
    apiRequest<{ message: string }>("/auth/verify-email", { method: "POST", body: { token }, retryOnAuthFail: false }),
  resendVerification: (email: string) =>
    apiRequest<{ message: string }>("/auth/resend-verification", { method: "POST", body: { email }, retryOnAuthFail: false }),
  logout: () => apiRequest<{ message: string }>("/auth/logout", { method: "POST", retryOnAuthFail: false }),
  forgotPassword: (email: string) =>
    apiRequest<{ message: string }>("/auth/forgot-password", { method: "POST", body: { email }, retryOnAuthFail: false }),
  resetPassword: (token: string, password: string) =>
    apiRequest<{ message: string }>("/auth/reset-password", { method: "POST", body: { token, password }, retryOnAuthFail: false }),
  changePassword: (old_password: string, new_password: string) =>
    apiRequest<{ message: string }>("/auth/change-password", { method: "POST", body: { old_password, new_password } }),

  // user
  me: () => apiRequest<{ user: UserDto }>("/user/me"),
  impacts: () => apiRequest<{ impacts: ImpactDto[] }>("/user/impacts"),
  updateMe: (payload: Partial<Pick<UserDto, "first_name" | "last_name" | "phone" | "postal_address">>) =>
    apiRequest<{ user: UserDto }>("/user/me", { method: "PATCH", body: payload }),
  deleteMe: () => apiRequest<{ message: string }>("/user/me", { method: "DELETE", body: { confirm: "DELETE" } }),
  uploadAvatar: (file: File) => {
    const fd = new FormData();
    fd.append("avatar", file);
    return apiRequest<{ avatar_url: string }>("/user/me/avatar", { method: "POST", formData: fd });
  },
  notificationPrefs: () =>
    apiRequest<{ preferences: { news: boolean; impact_reports: boolean; new_campaigns: boolean } }>(
      "/user/notification-prefs"
    ),
  updateNotificationPrefs: (prefs: { news: boolean; impact_reports: boolean; new_campaigns: boolean }) =>
    apiRequest("/user/notification-prefs", { method: "PUT", body: prefs }),
  dashboard: () => apiRequest<DashboardDto>("/user/dashboard"),
  getMedicalRecord: () => apiRequest<{ record: MedicalRecordDto | null }>("/medical-records/me"),

  // campaigns
  campaigns: (status?: "active" | "completed") =>
    apiRequest<{ campaigns: CampaignDto[] }>(`/campaigns${status ? `?status=${status}` : ""}`),
  campaign: (slug: string) => apiRequest<{ campaign: CampaignDto }>(`/campaigns/${encodeURIComponent(slug)}`),
  suggestCampaign: (payload: { title?: string; description: string; contact?: string }) =>
    apiRequest<{ message: string }>("/campaigns/suggest", { method: "POST", body: payload }),

  // donations
  startDonation: (amount: number, campaign_slug?: string) =>
    apiRequest<{ reference: string; redirect_url: string }>("/donations", {
      method: "POST",
      body: { amount, campaign_slug },
    }),
  donationHistory: (page = 1, perPage = 10, q = "") =>
    apiRequest<{ items: DonationDto[]; total: number; page: number; per_page: number }>(
      `/donations?page=${page}&per_page=${perPage}${q ? `&q=${encodeURIComponent(q)}` : ""}`
    ),

  // notifications
  notifications: () => apiRequest<{ notifications: NotificationDto[]; unread: number }>("/notifications"),
  markNotificationRead: (id: number) => apiRequest(`/notifications/${id}/read`, { method: "POST" }),
  markAllNotificationsRead: () => apiRequest("/notifications/read-all", { method: "POST" }),

  // engagement
  requestTaxCertificate: (payload: { year_jalali?: number; note?: string }) =>
    apiRequest<{ message: string }>("/tax-certificate", { method: "POST", body: payload }),
};

export interface CampaignDto {
  id: number;
  slug: string;
  title: string;
  description: string | null;
  image_url: string | null;
  category: string | null;
  goal_amount: number;
  raised_amount: number;
  donor_count: number;
  is_general_fund: boolean;
  status: string;
  progress: number;
  days_left: number | null;
}

export interface ImpactDto {
  id: number;
  title: string;
  description: string;
  image: string;
  stat_text: string;
  quantity_unit?: string;
}

export interface DonationDto {
  reference: string;
  amount: number;
  type: string;
  status: string;
  receipt_number: string | null;
  campaign_title: string | null;
  created_at: string;
  paid_at: string | null;
}

export interface NotificationDto {
  id: number;
  type: string;
  title: string;
  body: string | null;
  link: string | null;
  read: boolean;
  created_at: string;
}

export interface DashboardDto {
  stats: {
    total_amount: number;
    donation_count: number;
    campaigns_supported: number;
    days_supporting: number;
    monthly_series: { month: string; amount: number }[];
  };
  recent_activity: {
    reference: string;
    amount: number;
    status: string;
    campaign_title: string | null;
    created_at: string;
  }[];
  kindness_points: number;
  tier: { slug: string | null; name: string | null };
  unread_notifications: number;
}

export interface MedicalRecordDto {
  id: number;
  user_id: number;
  full_name: string;
  mobile: string;
  age: number | null;
  gender: string | null;
  province: string;
  city: string;
  cancer_type: string | null;
  diagnosis_status: string | null;
  description: string | null;
  documents: string[];
  created_at: string;
}

