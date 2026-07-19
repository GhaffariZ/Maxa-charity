import React from "react";
import { Link, Outlet, useLocation, useNavigate } from "react-router";
import {
  LayoutDashboard,
  CreditCard,
  History,
  Megaphone,
  UserCircle,
  HeartHandshake,
  Award,
  Menu,
  X,
  Bell,
  LogOut
} from "lucide-react";
import { motion, AnimatePresence } from "motion/react";
import { ImageWithFallback } from "./figma/ImageWithFallback";
import { useAuth } from "../../contexts/AuthContext";

const navItems = [
  { id: "dashboard", label: "داشبورد", icon: LayoutDashboard, path: "/" },
  { id: "payments", label: "پرداخت آنلاین", icon: CreditCard, path: "/payments" },
  { id: "history", label: "تاریخچه پرداخت", icon: History, path: "/history" },
  { id: "campaigns", label: "کمپین‌ها", icon: Megaphone, path: "/campaigns" },
  { id: "impact", label: "اثرات کمک", icon: HeartHandshake, path: "/impact" },
  { id: "profile", label: "پروفایل", icon: UserCircle, path: "/profile" },
];

export function Layout() {
  const [isSidebarOpen, setIsSidebarOpen] = React.useState(false);
  const location = useLocation();
  const navigate = useNavigate();
  const { user, logout } = useAuth();

  const fullName =
    [user?.first_name, user?.last_name].filter(Boolean).join(" ").trim() || "کاربر مکسا";
  const initials =
    [user?.first_name?.[0], user?.last_name?.[0]].filter(Boolean).join("") || "م";
  const tierName = user?.tier?.name ?? null;

  const handleLogout = async () => {
    await logout();
    navigate("/login", { replace: true });
  };

  return (
    <div className="min-h-screen bg-background text-foreground flex flex-col md:flex-row" dir="rtl">
      {/* Mobile Header */}
      <header className="md:hidden flex items-center justify-between p-4 bg-surface border-b border-border sticky top-0 z-50">
        <div className="flex items-center gap-2">
          <div className="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white">
            <HeartHandshake size={24} />
          </div>
          <span className="font-bold text-lg">مکسا</span>
        </div>
        <button 
          onClick={() => setIsSidebarOpen(!isSidebarOpen)}
          className="p-2 hover:bg-muted rounded-lg"
        >
          {isSidebarOpen ? <X /> : <Menu />}
        </button>
      </header>

      {/* Sidebar */}
      <aside className={`
        fixed top-16 bottom-0 right-0 z-45 w-72 bg-surface border-l border-border transition-transform duration-300 ease-in-out md:translate-x-0 md:static
        ${isSidebarOpen ? "translate-x-0" : "translate-x-full"}
      `}>
        <div className="h-full flex flex-col p-6">
          <div className="hidden md:flex items-center gap-3 mb-10 p-2">
            <div className="w-12 h-12 bg-primary rounded-2xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
              <HeartHandshake size={28} />
            </div>
            <div>
              <h1 className="font-extrabold text-xl leading-tight">مکسا</h1>
              <p className="text-xs text-muted-foreground">پنل اختصاصی حامیان</p>
            </div>
          </div>

          <nav className="flex-1 space-y-2">
            {navItems.map((item) => {
              const isActive = location.pathname === item.path;
              return (
                <Link
                  key={item.id}
                  to={item.path}
                  onClick={() => setIsSidebarOpen(false)}
                  className={`
                    flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all group
                    ${isActive 
                      ? "bg-primary text-white shadow-md shadow-primary/20" 
                      : "hover:bg-primary-light/10 text-muted-foreground hover:text-primary"}
                  `}
                >
                  <item.icon size={22} />
                  <span className="font-medium">{item.label}</span>
                </Link>
              );
            })}
          </nav>

          <div className="mt-auto pt-6 border-t border-border space-y-3">
            <div className="bg-muted/50 rounded-2xl p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-secondary flex items-center justify-center text-white font-bold overflow-hidden">
                {user?.avatar_url ? (
                  <img src={user.avatar_url} alt={fullName} className="w-full h-full object-cover" />
                ) : (
                  initials
                )}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-bold truncate">{fullName}</p>
                {tierName && (
                  <div className="flex items-center gap-1 text-[10px] text-secondary font-bold">
                    <Award size={12} />
                    <span>{tierName}</span>
                  </div>
                )}
              </div>
            </div>
            <button
              onClick={handleLogout}
              className="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-2xl text-sm font-medium text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-colors"
            >
              <LogOut size={18} />
              <span>خروج از حساب</span>
            </button>
          </div>
        </div>
      </aside>

      {/* Overlay */}
      {isSidebarOpen && (
        <div 
          className="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 md:hidden"
          onClick={() => setIsSidebarOpen(false)}
        />
      )}

      {/* Main Content */}
      <main className="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
        <header className="hidden md:flex items-center justify-between px-8 py-4 bg-surface/80 backdrop-blur-md border-b border-border sticky top-0 z-20">
          <div className="flex items-center gap-4">
            <h2 className="text-lg font-bold">
              {navItems.find(i => i.path === location.pathname)?.label || "داشبورد"}
            </h2>
          </div>
          <div className="flex items-center gap-4">
            <button className="p-2.5 bg-muted/50 hover:bg-muted text-muted-foreground rounded-xl transition-colors relative">
              <Bell size={20} />
              <span className="absolute top-2.5 right-2.5 w-2 h-2 bg-destructive rounded-full border-2 border-surface"></span>
            </button>
            <div className="h-8 w-[1px] bg-border mx-2"></div>
            <div className="text-left">
              <p className="text-xs text-muted-foreground">خوش آمدید،</p>
              <p className="text-sm font-bold">{fullName}</p>
            </div>
          </div>
        </header>

        <div className="flex-1 p-4 md:p-8">
          <Outlet />
        </div>
      </main>
    </div>
  );
}
