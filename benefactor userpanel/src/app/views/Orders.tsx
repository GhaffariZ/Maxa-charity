import React from "react";
import {
  Package,
  Search,
  Calendar,
  MapPin,
  ArrowRight,
  Clock,
  CheckCircle2,
  Truck,
  Copy,
  Check,
  ExternalLink,
  ShoppingBag,
  AlertCircle,
  XCircle,
  Phone,
  User,
} from "lucide-react";
import { api, type OrderDto, type OrderItemDto } from "../../api/client";
import { Loader } from "../components/ui/loader";

type TabType = "products" | "stands";

function getFulfillmentBadge(status?: string) {
  switch (status) {
    case "delivered":
      return {
        label: "تحویل داده شده",
        bg: "bg-emerald-500/10 text-emerald-600 border-emerald-500/20",
        icon: CheckCircle2,
      };
    case "shipped":
      return {
        label: "ارسال شده (در مسیر)",
        bg: "bg-blue-500/10 text-blue-600 border-blue-500/20",
        icon: Truck,
      };
    case "processing":
      return {
        label: "در حال بسته‌بندی و آماده‌سازی",
        bg: "bg-sky-500/10 text-sky-600 border-sky-500/20",
        icon: Clock,
      };
    case "cancelled":
      return {
        label: "لغو شده",
        bg: "bg-rose-500/10 text-rose-600 border-rose-500/20",
        icon: XCircle,
      };
    case "pending":
    default:
      return {
        label: "در انتظار بررسی و بسته‌بندی",
        bg: "bg-amber-500/10 text-amber-600 border-amber-500/20",
        icon: Clock,
      };
  }
}

export function Orders() {
  const [orders, setOrders] = React.useState<OrderDto[]>([]);
  const [loading, setLoading] = React.useState(true);
  const [activeTab, setActiveTab] = React.useState<TabType>("products");
  const [copiedCode, setCopiedCode] = React.useState<string | null>(null);

  React.useEffect(() => {
    let active = true;
    async function fetchOrders() {
      try {
        const res = await api.orders();
        if (active) {
          const list = res.orders || [];
          setOrders(list);
          // If user only has stands and no product orders, auto-switch to stands
          const hasProducts = list.some((o) => o.order_type === "product");
          const hasStands = list.some((o) => o.order_type !== "product");
          if (!hasProducts && hasStands) {
            setActiveTab("stands");
          }
        }
      } catch (e) {
        console.error("Failed to load orders", e);
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    }
    fetchOrders();
    return () => {
      active = false;
    };
  }, []);

  const copyToClipboard = (text: string) => {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
      setCopiedCode(text);
      setTimeout(() => setCopiedCode(null), 2500);
    });
  };

  const productOrders = orders.filter((o) => o.order_type === "product");
  const standOrders = orders.filter((o) => o.order_type !== "product");

  return (
    <div className="max-w-6xl mx-auto space-y-6 animate-fade-in pb-12">
      {/* Header Banner */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card rounded-2xl p-6 border shadow-sm">
        <div>
          <h1 className="text-2xl font-black text-foreground mb-1">پیگیری و سوابق سفارشات</h1>
          <p className="text-sm text-muted-foreground">
            پیگیری وضعیت آماده‌سازی، ارسال و کدهای رهگیری پستی خریدهای خیریه و سفارش‌های استند
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2.5">
          <a
            href="/shop.php"
            className="flex items-center gap-2 px-4 py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-primary/90 transition-all shadow-sm shadow-primary/20 text-sm"
          >
            <ShoppingBag className="w-4 h-4" />
            <span>فروشگاه خیریه مکسا</span>
          </a>
          <a
            href="/stand-sell-section.php"
            className="flex items-center gap-2 px-4 py-2.5 bg-muted text-foreground font-semibold rounded-xl hover:bg-muted/80 transition-all text-sm border"
          >
            <Package className="w-4 h-4" />
            <span>سفارش استند و تاج‌گل</span>
          </a>
        </div>
      </div>

      {/* Tabs Navigation */}
      <div className="flex items-center gap-2 p-1.5 bg-muted/60 rounded-2xl border w-fit">
        <button
          type="button"
          onClick={() => setActiveTab("products")}
          className={`flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all ${
            activeTab === "products"
              ? "bg-card text-foreground shadow-sm"
              : "text-muted-foreground hover:text-foreground"
          }`}
        >
          <ShoppingBag className="w-4 h-4 text-primary" />
          <span>محصولات فروشگاه خیریه</span>
          <span
            className={`px-2 py-0.5 rounded-full text-xs font-black ${
              activeTab === "products"
                ? "bg-primary/10 text-primary"
                : "bg-muted-foreground/10 text-muted-foreground"
            }`}
          >
            {productOrders.length.toLocaleString("fa-IR")}
          </span>
        </button>

        <button
          type="button"
          onClick={() => setActiveTab("stands")}
          className={`flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all ${
            activeTab === "stands"
              ? "bg-card text-foreground shadow-sm"
              : "text-muted-foreground hover:text-foreground"
          }`}
        >
          <Package className="w-4 h-4 text-primary" />
          <span>استند و تاج گل</span>
          <span
            className={`px-2 py-0.5 rounded-full text-xs font-black ${
              activeTab === "stands"
                ? "bg-primary/10 text-primary"
                : "bg-muted-foreground/10 text-muted-foreground"
            }`}
          >
            {standOrders.length.toLocaleString("fa-IR")}
          </span>
        </button>
      </div>

      {loading ? (
        <div className="flex flex-col items-center justify-center py-20 text-muted-foreground">
          <Loader label="در حال دریافت سفارش‌ها" className="mb-4" />
          <p className="font-medium text-sm" aria-hidden="true">
            در حال دریافت سفارش‌ها
          </p>
        </div>
      ) : activeTab === "products" ? (
        /* =================== SHOP PRODUCTS TAB =================== */
        productOrders.length === 0 ? (
          <div className="bg-card border rounded-2xl p-12 text-center shadow-sm">
            <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 text-muted-foreground">
              <ShoppingBag className="w-8 h-8" />
            </div>
            <h3 className="text-lg font-bold text-foreground mb-2">
              هنوز خریدی از فروشگاه خیریه انجام نداده‌اید
            </h3>
            <p className="text-sm text-muted-foreground mb-6 max-w-md mx-auto">
              عواید حاصل از فروش تمامی محصولات صنایع دستی و هدایای فروشگاه مکسا صرف هزینه دارو و درمان بیماران مبتلا به سرطان می‌شود.
            </p>
            <a
              href="/shop.php"
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-primary/90 transition-all text-sm shadow-sm shadow-primary/20"
            >
              مشاهده فروشگاه خیریه
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>
        ) : (
          <div className="grid gap-5">
            {productOrders.map((order) => {
              const badge = getFulfillmentBadge(order.fulfillment_status);
              const BadgeIcon = badge.icon;
              const items: OrderItemDto[] = order.items || [];

              return (
                <div
                  key={order.id}
                  className="bg-card border rounded-2xl p-6 flex flex-col gap-5 shadow-sm hover:border-primary/40 transition-all"
                >
                  {/* Order Top Bar */}
                  <div className="flex flex-wrap items-center justify-between gap-3 border-b pb-4">
                    <div className="flex flex-wrap items-center gap-2">
                      <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-black">
                        <Search className="w-3.5 h-3.5" />
                        شماره سفارش: {order.tracking_code || `ORD-${order.id}`}
                      </span>
                      <span
                        className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ${badge.bg}`}
                      >
                        <BadgeIcon className="w-3.5 h-3.5" />
                        {badge.label}
                      </span>
                    </div>

                    <div className="flex items-center gap-2 text-xs text-muted-foreground font-medium">
                      <Calendar className="w-3.5 h-3.5" />
                      <span>{order.order_date || order.created_at?.split(" ")[0]}</span>
                    </div>
                  </div>

                  {/* Postal Tracking Banner (if shipped/provided) */}
                  {order.postal_tracking_code ? (
                    <div className="p-4 rounded-xl bg-primary/5 border border-primary/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                          <Truck className="w-5 h-5" />
                        </div>
                        <div>
                          <div className="text-xs text-muted-foreground">
                            ارسال شده توسط{" "}
                            <strong className="text-foreground font-bold">
                              {order.carrier || "پست پیشتاز"}
                            </strong>
                          </div>
                          <div className="text-sm font-black text-foreground tracking-wider font-mono mt-0.5">
                            {order.postal_tracking_code}
                          </div>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 w-full sm:w-auto">
                        <button
                          type="button"
                          onClick={() => copyToClipboard(order.postal_tracking_code || "")}
                          className="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-card border text-xs font-bold text-foreground hover:bg-muted transition-colors shadow-xs"
                          title="کپی کد رهگیری"
                        >
                          {copiedCode === order.postal_tracking_code ? (
                            <>
                              <Check className="w-3.5 h-3.5 text-emerald-600" />
                              <span className="text-emerald-600">کپی شد!</span>
                            </>
                          ) : (
                            <>
                              <Copy className="w-3.5 h-3.5 text-muted-foreground" />
                              <span>کپی کد رهگیری</span>
                            </>
                          )}
                        </button>

                        <a
                          href={`https://tracking.post.ir/?id=${encodeURIComponent(
                            order.postal_tracking_code.trim()
                          )}`}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors shadow-xs"
                        >
                          <span>رهگیری آنلاین مرسوله</span>
                          <ExternalLink className="w-3.5 h-3.5" />
                        </a>
                      </div>
                    </div>
                  ) : (
                    <div className="p-3.5 rounded-xl bg-muted/40 border border-border/60 flex items-center gap-2.5 text-xs text-muted-foreground">
                      <Clock className="w-4 h-4 text-amber-500 shrink-0" />
                      <span>
                        سفارش شما در حال پردازش و بسته‌بندی است. به محض تحویل به اداره پست، کد رهگیری ۲۴ رقمی در این قسمت قرار می‌گیرد.
                      </span>
                    </div>
                  )}

                  {/* Items Table */}
                  <div className="space-y-2">
                    <h4 className="text-xs font-bold text-muted-foreground">اقلام خریداری‌شده:</h4>
                    {items.length > 0 ? (
                      <div className="border rounded-xl divide-y overflow-hidden text-sm">
                        {items.map((item, idx) => (
                          <div
                            key={item.id || idx}
                            className="p-3.5 flex flex-wrap items-center justify-between gap-3 bg-card hover:bg-muted/30 transition-colors"
                          >
                            <div className="flex items-center gap-3 min-w-[200px]">
                              <span className="w-6 h-6 rounded-lg bg-muted text-xs font-bold flex items-center justify-center text-muted-foreground">
                                {(idx + 1).toLocaleString("fa-IR")}
                              </span>
                              <span className="font-bold text-foreground">
                                {item.product_title}
                              </span>
                            </div>

                            <div className="flex items-center gap-6 text-xs text-muted-foreground">
                              <span>
                                تعداد:{" "}
                                <strong className="text-foreground font-bold">
                                  {Number(item.quantity).toLocaleString("fa-IR")}
                                </strong>
                              </span>
                              <span>
                                فی:{" "}
                                <strong className="text-foreground font-bold">
                                  {Number(item.unit_price).toLocaleString("fa-IR")} تومان
                                </strong>
                              </span>
                              <span className="text-sm font-black text-primary">
                                {Number(item.total_price).toLocaleString("fa-IR")} تومان
                              </span>
                            </div>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <p className="text-xs text-muted-foreground italic">
                        اطلاعات تفصیلی اقلام در دسترس نیست.
                      </p>
                    )}
                  </div>

                  {/* Shipping Address & Bottom Bar */}
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t">
                    <div className="md:col-span-2 space-y-2 text-xs">
                      <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-muted-foreground">
                        {order.recipient_name && (
                          <span className="flex items-center gap-1.5">
                            <User className="w-3.5 h-3.5 text-primary" />
                            گیرنده: <strong className="text-foreground">{order.recipient_name}</strong>
                          </span>
                        )}
                        {order.recipient_phone && (
                          <span className="flex items-center gap-1.5">
                            <Phone className="w-3.5 h-3.5 text-primary" />
                            تماس: <span className="font-mono">{order.recipient_phone}</span>
                          </span>
                        )}
                        {order.postal_code && (
                          <span className="flex items-center gap-1.5">
                            کد پستی: <span className="font-mono">{order.postal_code}</span>
                          </span>
                        )}
                      </div>

                      <div className="flex items-start gap-1.5 text-muted-foreground leading-relaxed">
                        <MapPin className="w-3.5 h-3.5 mt-0.5 shrink-0 text-primary" />
                        <span>
                          <strong className="text-foreground">نشانی ارسال:</strong>{" "}
                          {order.province || order.city ? `${order.province || ""}، ${order.city || ""} - ` : ""}
                          {order.address || "—"}
                        </span>
                      </div>

                      <div className="text-[11px] text-muted-foreground/80 flex items-center gap-1.5">
                        <AlertCircle className="w-3 h-3 text-amber-500 shrink-0" />
                        <span>هزینه ارسال به صورت پس‌کرایه بوده و زمان تحویل به مامور پست پرداخت می‌شود.</span>
                      </div>
                    </div>

                    <div className="flex flex-col items-start md:items-end justify-center border-t md:border-t-0 md:border-r md:pr-4 pt-3 md:pt-0">
                      <span className="text-xs text-muted-foreground mb-1">مبلغ کل سفارش:</span>
                      <span className="text-xl font-black text-primary">
                        {Number(order.total_price || 0).toLocaleString("fa-IR")} تومان
                      </span>
                      <div className="flex items-center gap-1 text-[11px] text-emerald-600 mt-1 font-semibold">
                        <CheckCircle2 className="w-3.5 h-3.5" />
                        <span>پرداخت آنلاین موفق</span>
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )
      ) : (
        /* =================== STANDS TAB =================== */
        standOrders.length === 0 ? (
          <div className="bg-card border rounded-2xl p-12 text-center shadow-sm">
            <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 text-muted-foreground">
              <Package className="w-8 h-8" />
            </div>
            <h3 className="text-lg font-bold text-foreground mb-2">
              هنوز سفارشی برای استند یا تاج گل ثبت نکرده‌اید
            </h3>
            <p className="text-sm text-muted-foreground mb-6 max-w-md mx-auto">
              با سفارش استندهای تبریک یا تسلیت خیریه مکسا، در شادباش‌ها و یادبودها همدل بیماران مبتلا به سرطان باشید.
            </p>
            <a
              href="/stand-sell-section.php"
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-primary/10 text-primary font-bold rounded-xl hover:bg-primary/20 transition-colors text-sm"
            >
              مشاهده و سفارش استند
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>
        ) : (
          <div className="grid gap-4">
            {standOrders.map((order) => {
              const badge = getFulfillmentBadge(order.fulfillment_status);
              const BadgeIcon = badge.icon;

              return (
                <div
                  key={order.id}
                  className="bg-card border rounded-2xl p-6 flex flex-col md:flex-row gap-6 shadow-sm hover:border-primary/40 transition-all"
                >
                  <div className="flex-1 space-y-4">
                    <div className="flex flex-wrap items-center justify-between gap-2 border-b pb-3">
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-black">
                          <Search className="w-3.5 h-3.5" />
                          کد پیگیری: {order.tracking_code || `ORD-${order.id}`}
                        </span>
                        {order.fulfillment_status && (
                          <span
                            className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border ${badge.bg}`}
                          >
                            <BadgeIcon className="w-3 h-3" />
                            {badge.label}
                          </span>
                        )}
                      </div>
                      <span className="text-xs font-medium text-muted-foreground flex items-center gap-1.5">
                        <Calendar className="w-3.5 h-3.5" />
                        {order.order_date || order.created_at?.split(" ")[0]}
                      </span>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                      <div className="bg-muted/40 p-3 rounded-xl">
                        <span className="text-xs text-muted-foreground block mb-1">از طرف:</span>
                        <span className="font-bold text-foreground">{order.from_user || "—"}</span>
                      </div>
                      <div className="bg-muted/40 p-3 rounded-xl">
                        <span className="text-xs text-muted-foreground block mb-1">تقدیم به:</span>
                        <span className="font-bold text-foreground">{order.to_user || "—"}</span>
                      </div>
                    </div>

                    <div className="text-sm space-y-2">
                      <div className="flex items-start gap-2 text-muted-foreground text-xs leading-relaxed">
                        <MapPin className="w-4 h-4 mt-0.5 shrink-0 text-primary" />
                        <span>
                          <strong className="text-foreground">محل برگزاری:</strong>{" "}
                          {order.province || order.city ? `${order.province || ""}، ${order.city || ""} - ` : ""}
                          {order.address || "—"}
                        </span>
                      </div>
                      {order.message && (
                        <div className="p-3 bg-muted/60 rounded-xl text-xs leading-relaxed text-foreground border border-border/50">
                          <span className="text-muted-foreground block mb-1 font-semibold">متن پیام استند:</span>
                          "{order.message}"
                        </div>
                      )}
                    </div>

                    {order.postal_tracking_code && (
                      <div className="p-3 rounded-xl bg-primary/5 border border-primary/20 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div className="flex items-center gap-2">
                          <Truck className="w-4 h-4 text-primary" />
                          <span>
                            کد رهگیری ارسال ({order.carrier || "پست"}):{" "}
                            <strong className="font-mono font-bold text-foreground">
                              {order.postal_tracking_code}
                            </strong>
                          </span>
                        </div>
                        <button
                          type="button"
                          onClick={() => copyToClipboard(order.postal_tracking_code || "")}
                          className="px-2.5 py-1 rounded-lg bg-card border text-[11px] font-semibold text-foreground hover:bg-muted"
                        >
                          {copiedCode === order.postal_tracking_code ? "کپی شد!" : "کپی کد"}
                        </button>
                      </div>
                    )}
                  </div>

                  <div className="flex flex-row md:flex-col items-center md:items-end justify-between border-t md:border-t-0 md:border-r border-border pt-4 md:pt-0 md:pr-6 md:w-52 shrink-0 gap-4">
                    <div className="text-right w-full">
                      <span className="block text-xs text-muted-foreground mb-1">مبلغ پرداختی:</span>
                      <span className="text-lg font-black text-primary">
                        {order.total_price
                          ? Number(order.total_price).toLocaleString("fa-IR") + " تومان"
                          : "رایگان"}
                      </span>
                      <div className="flex items-center gap-1 text-[11px] text-emerald-600 mt-1 font-semibold">
                        <CheckCircle2 className="w-3.5 h-3.5" />
                        <span>سفارش تأیید شده</span>
                      </div>
                    </div>

                    {order.image && (
                      <div className="w-20 h-28 shrink-0 bg-muted/50 rounded-xl p-1.5 border border-border flex items-center justify-center">
                        <img
                          src={order.image}
                          alt="Stand"
                          className="w-full h-full object-contain"
                        />
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )
      )}
    </div>
  );
}
