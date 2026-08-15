import React from "react";
import { Navigate, useLocation } from "react-router";
import { Loader2 } from "lucide-react";
import { useAuth } from "../contexts/AuthContext";

// ---------------------------------------------------------------------------
// Route guards backed by the real AuthContext (JWT access token in memory +
// httpOnly refresh cookie). While the session is being restored on first load
// we show a spinner instead of bouncing, so a reload on a private page doesn't
// flash the login screen.
// ---------------------------------------------------------------------------

function FullScreenLoader() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-background" dir="rtl">
      <Loader2 className="animate-spin text-primary" size={32} />
    </div>
  );
}

/** Gate for private routes: redirect to /login when not authenticated. */
export function RequireAuth({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, isLoading } = useAuth();
  const location = useLocation();

  if (isLoading) return <FullScreenLoader />;
  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }
  return <>{children}</>;
}

/** Wrapper for auth pages: bounce already-logged-in users to the dashboard. */
export function RedirectIfAuthed({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, isLoading } = useAuth();
  const location = useLocation();

  if (isLoading) return <FullScreenLoader />;
  if (isAuthenticated) {
    const queryParams = new URLSearchParams(location.search);
    const returnUrl = queryParams.get("returnUrl");
    if (returnUrl) {
      if (returnUrl.startsWith("http") || returnUrl.startsWith("/stand-order.php")) {
        window.location.href = returnUrl;
        return <FullScreenLoader />;
      }
      return <Navigate to={returnUrl} replace />;
    }
    return <Navigate to="/" replace />;
  }
  return <>{children}</>;
}
