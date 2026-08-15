import React from "react";
import { Link } from "react-router";
import { Package, Search, Calendar, MapPin, Loader2, ArrowRight, Clock, CheckCircle2 } from "lucide-react";
import { api, type OrderDto } from "../../api/client";

export function Orders() {
  const [orders, setOrders] = React.useState<OrderDto[]>([]);
  const [loading, setLoading] = React.useState(true);

  React.useEffect(() => {
    let active = true;
    async function fetchOrders() {
      try {
        const res = await api.orders();
        if (active) {
          setOrders(res.orders || []);
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

  return (
    <div className="max-w-6xl mx-auto space-y-6 animate-fade-in">
      {/* Header Banner */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card rounded-2xl p-6 border shadow-sm">
        <div>
          <h1 className="text-2xl font-black text-foreground mb-1">سفارشات اخیر</h1>
          <p className="text-sm text-muted-foreground">
            لیست و پیگیری سفارش‌های استند و تاج گل خیریه شما
          </p>
        </div>
        <a
          href="/stand-sell-section.php"
          className="flex items-center gap-2 px-4 py-2 bg-primary text-white font-bold rounded-xl hover:bg-primary/90 transition-all shadow-sm shadow-primary/20"
        >
          <Package className="w-5 h-5" />
          <span>سفارش جدید استند</span>
        </a>
      </div>

      {loading ? (
        <div className="flex flex-col items-center justify-center py-20 text-muted-foreground">
          <Loader2 className="w-9 h-9 animate-spin text-primary mb-3" />
          <p className="font-medium text-sm">در حال دریافت سفارشات...</p>
        </div>
      ) : orders.length === 0 ? (
        <div className="bg-card border rounded-2xl p-12 text-center shadow-sm">
          <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 text-muted-foreground">
            <Package className="w-8 h-8" />
          </div>
          <h3 className="text-lg font-bold text-foreground mb-2">هنوز سفارشی ثبت نکرده‌اید</h3>
          <p className="text-sm text-muted-foreground mb-6">
            شما تا کنون هیچ سفارش استند یا تاج گل ثبت نکرده‌اید.
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
          {orders.map((order) => (
            <div
              key={order.id}
              className="bg-card border rounded-2xl p-6 flex flex-col md:flex-row gap-6 shadow-sm hover:border-primary/40 transition-all"
            >
              <div className="flex-1 space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-2 border-b pb-3">
                  <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-black tracking-wide">
                    <Search className="w-3.5 h-3.5" />
                    کد رهگیری: {order.tracking_code || "ثبت نشده"}
                  </span>
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
                    <span><strong className="text-foreground">آدرس تحویل:</strong> {order.address || "—"}</span>
                  </div>
                  {order.message && (
                    <div className="p-3 bg-muted/60 rounded-xl text-xs leading-relaxed text-foreground border border-border/50">
                      <span className="text-muted-foreground block mb-1 font-semibold">متن پیام:</span>
                      "{order.message}"
                    </div>
                  )}
                </div>
              </div>

              <div className="flex flex-row md:flex-col items-center md:items-end justify-between border-t md:border-t-0 md:border-r border-border pt-4 md:pt-0 md:pr-6 md:w-52 shrink-0 gap-4">
                <div className="text-right w-full">
                  <span className="block text-xs text-muted-foreground mb-1">مبلغ پرداختی:</span>
                  <span className="text-lg font-black text-primary">
                    {order.total_price ? Number(order.total_price).toLocaleString("fa-IR") + " تومان" : "رایگان"}
                  </span>
                  <div className="flex items-center gap-1 text-[11px] text-emerald-600 mt-1 font-semibold">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    <span>سفارش تأیید شده</span>
                  </div>
                </div>

                {order.image && (
                  <div className="w-16 h-24 shrink-0 bg-muted/50 rounded-xl p-1.5 border border-border flex items-center justify-center">
                    <img
                      src={order.image}
                      alt="Stand"
                      className="w-full h-full object-contain"
                    />
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
