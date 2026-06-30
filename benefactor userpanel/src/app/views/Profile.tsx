import React from "react";
import { useNavigate } from "react-router";
import {
  User,
  Mail,
  Phone,
  MapPin,
  Shield,
  Bell,
  Camera,
  Save,
  Award,
  Calendar,
  ChevronLeft,
  Loader2,
  LogOut,
  FileText,
} from "lucide-react";
import { toast } from "sonner";
import { useAuth } from "../../contexts/AuthContext";
import { api, ApiRequestError, MedicalRecordDto } from "../../api/client";
import { faNumber, faDate } from "../lib/format";

type Prefs = { news: boolean; impact_reports: boolean; new_campaigns: boolean };

export function Profile() {
  const navigate = useNavigate();
  const { user, reloadUser, logout } = useAuth();

  const [fullName, setFullName] = React.useState("");
  const [phone, setPhone] = React.useState("");
  const [address, setAddress] = React.useState("");
  const [saving, setSaving] = React.useState(false);

  const [prefs, setPrefs] = React.useState<Prefs>({ news: true, impact_reports: true, new_campaigns: true });
  const [donationCount, setDonationCount] = React.useState<number | null>(null);
  const [showPwForm, setShowPwForm] = React.useState(false);
  
  const [medicalRecord, setMedicalRecord] = React.useState<MedicalRecordDto | null>(null);
  const [loadingRecord, setLoadingRecord] = React.useState(true);

  const fileRef = React.useRef<HTMLInputElement>(null);

  // Populate the form once the user is available.
  React.useEffect(() => {
    if (user) {
      setFullName([user.first_name, user.last_name].filter(Boolean).join(" "));
      setPhone(user.phone ?? "");
      setAddress(user.postal_address ?? "");
    }
  }, [user]);

  React.useEffect(() => {
    api.notificationPrefs().then((r) => setPrefs(r.preferences)).catch(() => {});
    api.dashboard().then((d) => setDonationCount(d.stats.donation_count)).catch(() => {});
    api.getMedicalRecord().then((r) => {
      setMedicalRecord(r.record);
      setLoadingRecord(false);
    }).catch(() => setLoadingRecord(false));
  }, []);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    // The API stores first/last separately — split on the first space.
    const trimmed = fullName.trim();
    const idx = trimmed.indexOf(" ");
    const first_name = idx === -1 ? trimmed : trimmed.slice(0, idx);
    const last_name = idx === -1 ? "" : trimmed.slice(idx + 1);

    try {
      await api.updateMe({
        first_name: first_name || undefined,
        last_name: last_name || undefined,
        phone: phone.trim() || undefined,
        postal_address: address.trim() || undefined,
      });
      await reloadUser();
      toast.success("تنظیمات با موفقیت ذخیره شد.");
    } catch (err) {
      if (err instanceof ApiRequestError && err.fields) {
        toast.error(Object.values(err.fields)[0] ?? err.message);
      } else {
        toast.error("ذخیرهٔ تغییرات ناموفق بود.");
      }
    } finally {
      setSaving(false);
    }
  };

  const handleAvatar = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    try {
      await api.uploadAvatar(file);
      await reloadUser();
      toast.success("تصویر پروفایل به‌روزرسانی شد.");
    } catch (err) {
      toast.error(err instanceof ApiRequestError ? err.message : "بارگذاری تصویر ناموفق بود.");
    } finally {
      if (fileRef.current) fileRef.current.value = "";
    }
  };

  const togglePref = async (key: keyof Prefs) => {
    const next = { ...prefs, [key]: !prefs[key] };
    setPrefs(next); // optimistic
    try {
      await api.updateNotificationPrefs(next);
    } catch {
      setPrefs(prefs); // revert
      toast.error("به‌روزرسانی تنظیمات ناموفق بود.");
    }
  };

  const handleLogout = async () => {
    await logout();
    navigate("/login", { replace: true });
  };

  const initials =
    [user?.first_name?.[0], user?.last_name?.[0]].filter(Boolean).join("") || "م";

  return (
    <div className="max-w-4xl mx-auto space-y-10">
      <div className="flex flex-col md:flex-row items-center gap-8 bg-surface p-8 rounded-[2.5rem] border border-border">
        <div className="relative group">
          <div className="w-32 h-32 rounded-[2.5rem] bg-secondary flex items-center justify-center text-white text-4xl font-extrabold shadow-xl shadow-secondary/20 overflow-hidden">
            {user?.avatar_url ? (
              <img src={user.avatar_url} alt="avatar" className="w-full h-full object-cover" />
            ) : (
              initials
            )}
          </div>
          <button
            onClick={() => fileRef.current?.click()}
            className="absolute -bottom-2 -right-2 w-10 h-10 bg-primary text-white rounded-xl flex items-center justify-center shadow-lg border-4 border-surface group-hover:scale-110 transition-transform"
          >
            <Camera size={18} />
          </button>
          <input ref={fileRef} type="file" accept="image/jpeg,image/png,image/webp" hidden onChange={handleAvatar} />
        </div>

        <div className="flex-1 text-center md:text-right">
          <div className="flex flex-col md:flex-row items-center gap-4 mb-2 justify-center md:justify-start">
            <h2 className="text-3xl font-extrabold">{fullName || "کاربر مکسا"}</h2>
            {user?.tier?.name && (
              <span className="flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-secondary/10 text-secondary text-sm font-bold">
                <Award size={16} />
                {user.tier.name}
              </span>
            )}
          </div>
          <p className="text-muted-foreground flex items-center justify-center md:justify-start gap-2">
            <Calendar size={16} />
            عضویت از {faDate(user?.member_since)}
          </p>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div className="bg-muted/30 p-4 rounded-2xl text-center">
            <p className="text-xs text-muted-foreground mb-1">امتیاز مهربانی</p>
            <p className="text-xl font-extrabold text-primary">{faNumber(user?.kindness_points ?? 0)}</p>
          </div>
          <div className="bg-muted/30 p-4 rounded-2xl text-center">
            <p className="text-xs text-muted-foreground mb-1">تعداد مشارکت</p>
            <p className="text-xl font-extrabold text-primary">
              {donationCount === null ? "—" : faNumber(donationCount)}
            </p>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-8">
          <section className="bg-surface p-8 rounded-[2.5rem] border border-border">
            <h3 className="text-xl font-bold mb-8 flex items-center gap-3">
              <User className="text-primary" size={24} />
              اطلاعات شخصی
            </h3>

            <form onSubmit={handleSave} className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-2">
                <label className="text-sm font-bold text-muted-foreground pr-2">نام و نام خانوادگی</label>
                <div className="relative">
                  <User className="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground/50" size={18} />
                  <input
                    type="text"
                    value={fullName}
                    onChange={(e) => setFullName(e.target.value)}
                    className="w-full bg-muted/30 border-2 border-transparent focus:border-primary/30 focus:bg-surface rounded-2xl py-4 pr-12 pl-4 outline-none transition-all font-medium"
                  />
                </div>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-bold text-muted-foreground pr-2">شماره همراه</label>
                <div className="relative">
                  <Phone className="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground/50" size={18} />
                  <input
                    type="text"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    dir="ltr"
                    placeholder="09123456789"
                    className="w-full bg-muted/30 border-2 border-transparent focus:border-primary/30 focus:bg-surface rounded-2xl py-4 pr-12 pl-4 outline-none transition-all font-medium text-right"
                  />
                </div>
              </div>

              <div className="space-y-2 md:col-span-2">
                <label className="text-sm font-bold text-muted-foreground pr-2">ایمیل</label>
                <div className="relative">
                  <Mail className="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground/50" size={18} />
                  <input
                    type="email"
                    value={user?.email ?? ""}
                    readOnly
                    dir="ltr"
                    title="برای تغییر ایمیل با پشتیبانی تماس بگیرید."
                    className="w-full bg-muted/50 border-2 border-transparent rounded-2xl py-4 pr-12 pl-4 outline-none font-medium text-right text-muted-foreground cursor-not-allowed"
                  />
                </div>
              </div>

              <div className="space-y-2 md:col-span-2">
                <label className="text-sm font-bold text-muted-foreground pr-2">آدرس برای ارسال رسید فیزیکی (اختیاری)</label>
                <div className="relative">
                  <MapPin className="absolute right-4 top-4 text-muted-foreground/50" size={18} />
                  <textarea
                    value={address}
                    onChange={(e) => setAddress(e.target.value)}
                    className="w-full bg-muted/30 border-2 border-transparent focus:border-primary/30 focus:bg-surface rounded-2xl py-4 pr-12 pl-4 outline-none transition-all font-medium min-h-[100px]"
                    placeholder="آدرس خود را وارد کنید..."
                  />
                </div>
              </div>

              <div className="md:col-span-2 pt-4">
                <button
                  type="submit"
                  disabled={saving}
                  className="bg-primary hover:bg-primary-dark text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-lg shadow-primary/20 flex items-center gap-2 disabled:opacity-60"
                >
                  {saving ? <Loader2 size={20} className="animate-spin" /> : <Save size={20} />}
                  ذخیره تغییرات
                </button>
              </div>
            </form>
          </section>

          <section className="bg-surface p-8 rounded-[2.5rem] border border-border">
            <h3 className="text-xl font-bold mb-8 flex items-center gap-3">
              <FileText className="text-primary" size={24} />
              پرونده ی مجازی
            </h3>

            {loadingRecord ? (
              <div className="flex justify-center p-4">
                <Loader2 size={24} className="animate-spin text-primary" />
              </div>
            ) : medicalRecord ? (
              <div className="bg-muted/30 p-6 rounded-2xl flex flex-col gap-4">
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span className="text-muted-foreground block mb-1">نوع بیماری</span>
                    <span className="font-bold">{medicalRecord.cancer_type || "نامشخص"}</span>
                  </div>
                  <div>
                    <span className="text-muted-foreground block mb-1">وضعیت تشخیص</span>
                    <span className="font-bold">{medicalRecord.diagnosis_status || "نامشخص"}</span>
                  </div>
                  <div>
                    <span className="text-muted-foreground block mb-1">استان / شهر</span>
                    <span className="font-bold">{medicalRecord.province} - {medicalRecord.city}</span>
                  </div>
                  <div>
                    <span className="text-muted-foreground block mb-1">تاریخ ثبت</span>
                    <span className="font-bold" dir="ltr">{faDate(medicalRecord.created_at)}</span>
                  </div>
                </div>
                <div className="pt-2 border-t border-border mt-2">
                  <a
                    href="/patientintake"
                    className="inline-flex bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md items-center gap-2"
                  >
                    ویرایش پرونده
                  </a>
                </div>
              </div>
            ) : (
              <div className="bg-muted/30 p-6 rounded-2xl text-center space-y-4">
                <p className="text-muted-foreground">شما پرونده پزشکی مجازی ندارید.</p>
                <a
                  href="/patientintake"
                  className="inline-flex bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md items-center gap-2"
                >
                  ایجاد پرونده پزشکی
                </a>
              </div>
            )}
          </section>
        </div>

        <div className="space-y-8">
          <section className="bg-surface p-8 rounded-[2.5rem] border border-border">
            <h3 className="text-lg font-bold mb-6 flex items-center gap-3">
              <Bell className="text-primary" size={20} />
              تنظیمات اطلاع‌رسانی
            </h3>

            <div className="space-y-6">
              {([
                { id: "news", label: "اخبار و رویدادهای مکسا" },
                { id: "impact_reports", label: "گزارش‌های دوره‌ای اثرگذاری" },
                { id: "new_campaigns", label: "کمپین‌های جدید" },
              ] as const).map((item) => (
                <div key={item.id} className="flex items-center justify-between">
                  <span className="text-sm font-medium">{item.label}</span>
                  <label className="relative inline-flex items-center cursor-pointer">
                    <input
                      type="checkbox"
                      checked={prefs[item.id]}
                      onChange={() => togglePref(item.id)}
                      className="sr-only peer"
                    />
                    <div className="w-11 h-6 bg-muted rounded-full peer peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                  </label>
                </div>
              ))}
            </div>
          </section>

          <section className="bg-surface p-8 rounded-[2.5rem] border border-border">
            <h3 className="text-lg font-bold mb-6 flex items-center gap-3">
              <Shield className="text-primary" size={20} />
              امنیت حساب
            </h3>
            <button
              onClick={() => setShowPwForm((s) => !s)}
              className="w-full text-right py-3 px-4 rounded-xl hover:bg-muted/50 transition-all text-sm font-bold flex items-center justify-between group"
            >
              تغییر رمز عبور
              <ChevronLeft size={16} className="group-hover:-translate-x-1 transition-transform" />
            </button>

            {showPwForm && <ChangePasswordForm onDone={() => setShowPwForm(false)} />}

            <button
              onClick={handleLogout}
              className="w-full text-right py-3 px-4 rounded-xl hover:bg-muted/50 transition-all text-sm font-bold flex items-center justify-between group text-destructive"
            >
              خروج از حساب
              <LogOut size={16} className="group-hover:-translate-x-1 transition-transform" />
            </button>
          </section>
        </div>
      </div>
    </div>
  );
}

function ChangePasswordForm({ onDone }: { onDone: () => void }) {
  const [oldPw, setOldPw] = React.useState("");
  const [newPw, setNewPw] = React.useState("");
  const [busy, setBusy] = React.useState(false);
  const { logout } = useAuth();
  const navigate = useNavigate();

  const submit = async () => {
    if (newPw.length < 10) {
      toast.error("رمز عبور جدید باید حداقل ۱۰ کاراکتر باشد.");
      return;
    }
    setBusy(true);
    try {
      await api.changePassword(oldPw, newPw);
      toast.success("رمز عبور تغییر کرد. لطفاً دوباره وارد شوید.");
      await logout();
      navigate("/login", { replace: true });
    } catch (err) {
      toast.error(err instanceof ApiRequestError ? err.message : "تغییر رمز عبور ناموفق بود.");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="bg-muted/30 rounded-2xl p-4 my-2 space-y-3">
      <input
        type="password"
        value={oldPw}
        onChange={(e) => setOldPw(e.target.value)}
        placeholder="رمز عبور فعلی"
        autoComplete="current-password"
        className="w-full bg-surface border border-border rounded-xl py-3 px-4 outline-none text-sm focus:border-primary/40"
      />
      <input
        type="password"
        value={newPw}
        onChange={(e) => setNewPw(e.target.value)}
        placeholder="رمز عبور جدید (حداقل ۱۰ کاراکتر)"
        autoComplete="new-password"
        className="w-full bg-surface border border-border rounded-xl py-3 px-4 outline-none text-sm focus:border-primary/40"
      />
      <div className="flex gap-2">
        <button
          onClick={submit}
          disabled={busy}
          className="flex-1 bg-primary text-white rounded-xl py-3 text-sm font-bold disabled:opacity-60 flex items-center justify-center gap-2"
        >
          {busy && <Loader2 size={16} className="animate-spin" />}
          ثبت رمز جدید
        </button>
        <button onClick={onDone} className="px-4 rounded-xl text-sm font-bold text-muted-foreground hover:bg-muted">
          انصراف
        </button>
      </div>
    </div>
  );
}
