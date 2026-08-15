import React from "react";
import { Link } from "react-router";
import { Package, Search, Calendar, MapPin, Loader2, ArrowRight } from "lucide-react";
import { api } from "../../api/client";

export function Orders() {
  const [orders, setOrders] = React.useState<any[]>([]);
  const [loading, setLoading] = React.useState(true);

  React.useEffect(() => {
    async function fetchOrders() {
      try {
        const res = await fetch((import.meta.env.VITE_API_URL ?? "/api") + "/user/orders", {
          credentials: "include"
        });
        const data = await res.json();
        setOrders(data.orders || []);
      } catch (e) {
        console.error(e);
      } finally {
        setLoading(false);
      }
    }
    fetchOrders();
  }, []);

  return (
    <div className="max-w-6xl mx-auto space-y-6">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card rounded-2xl p-6 border shadow-sm">
        <div>
          <h1 className="text-2xl font-black text-foreground mb-1">O3U?OO_O4OO O O_O</h1>
          <p className="text-sm text-muted-foreground">
            O OO_O?OrO O3U?OO_O4OO O O_O O_U_O O O OUU,U?O_ (OO3O U+O_ U^ O"UU^O).
          </p>
        </div>
        <Link
          to="/"
          className="flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary font-bold rounded-lg hover:bg-primary/20 transition-colors"
        >
          <Package className="w-5 h-5" />
          O3U?OO_O4 O_O_O_O_
        </Link>
      </div>

      {loading ? (
        <div className="flex flex-col items-center justify-center py-20 text-muted-foreground">
          <Loader2 className="w-8 h-8 animate-spin mb-4" />
          <p>O_O O-OU, O_OO_O,O_O_O O_OO_O_...</p>
        </div>
      ) : orders.length === 0 ? (
        <div className="bg-card border rounded-2xl p-12 text-center shadow-sm">
          <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
            <Package className="w-8 h-8 text-muted-foreground" />
          </div>
          <h3 className="text-lg font-bold text-foreground mb-2">O3U?OO_O4O O OUO U+O_OO O_!</h3>
          <p className="text-muted-foreground mb-6">O4UO O_OU+U^O1 O_O O_U,U^U+U O3U?OO_O4O O OO"O  U+U_O_O_UO_O_.</p>
        </div>
      ) : (
        <div className="grid gap-4">
          {orders.map((order) => (
            <div key={order.id} className="bg-card border rounded-2xl p-6 flex flex-col md:flex-row gap-6 shadow-sm hover:border-primary/50 transition-colors">
              <div className="flex-1 space-y-4">
                <div className="flex items-center justify-between">
                  <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold">
                    <Search className="w-3.5 h-3.5" />
                    U_O_ O_U_O,O_O_O : {order.tracking_code || '---'}
                  </span>
                  <span className="text-sm font-medium text-muted-foreground flex items-center gap-1">
                    <Calendar className="w-4 h-4" />
                    {order.order_date}
                  </span>
                </div>
                
                <div className="grid md:grid-cols-2 gap-4 text-sm">
                  <div>
                    <span className="text-muted-foreground block mb-1">OO1 O?O_U_:</span>
                    <span className="font-bold text-foreground">{order.from_user}</span>
                  </div>
                  <div>
                    <span className="text-muted-foreground block mb-1">O"U O?O_U_:</span>
                    <span className="font-bold text-foreground">{order.to_user}</span>
                  </div>
                </div>

                <div className="text-sm border-t pt-4 mt-4">
                  <div className="flex items-start gap-2 text-muted-foreground">
                    <MapPin className="w-4 h-4 mt-0.5 shrink-0" />
                    <span>{order.address}</span>
                  </div>
                  {order.message && (
                    <div className="mt-2 p-3 bg-muted rounded-lg italic text-foreground text-sm">
                      "{order.message}"
                    </div>
                  )}
                </div>
              </div>
              
              <div className="flex flex-col items-end justify-between border-t md:border-t-0 md:border-r pt-4 md:pt-0 md:pr-6 md:w-48 shrink-0">
                <div className="text-left md:text-right w-full mb-4">
                  <span className="block text-xs text-muted-foreground mb-1">UO"U,O_ U_U,O:</span>
                  <span className="text-lg font-black text-primary">
                    {order.total_price ? order.total_price.toLocaleString() + ' O U^UOU+' : 'O_OU_O_OU+'}
                  </span>
                </div>
                {/* Image thumb if available */}
                {order.image && (
                  <img src={order.image} alt="Stand" className="w-16 h-20 object-contain mx-auto md:mr-auto md:ml-0 rounded bg-muted/50 p-1" />
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
