import React from "react";
import { api, setAccessToken, type UserDto, ApiRequestError } from "../api/client";

// ---------------------------------------------------------------------------
//  Auth state for the whole app. Holds the current user in React state and the
//  access token in memory (inside the api client). On mount it attempts a
//  silent refresh using the httpOnly cookie, so a page reload keeps the session
//  without ever persisting the access token to disk.
// ---------------------------------------------------------------------------

interface AuthContextValue {
  user: UserDto | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (payload: {
    email: string;
    password: string;
    first_name?: string;
    last_name?: string;
  }) => Promise<{ message: string }>;
  logout: () => Promise<void>;
  reloadUser: () => Promise<void>;
  setUser: (user: UserDto | null) => void;
}

const AuthContext = React.createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = React.useState<UserDto | null>(null);
  const [isLoading, setIsLoading] = React.useState(true);

  const reloadUser = React.useCallback(async () => {
    const { user } = await api.me();
    setUser(user);
  }, []);

  // Bootstrap: try to restore a session from the refresh cookie.
  React.useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        // apiRequest auto-refreshes on 401, so calling /user/me is enough:
        // if the cookie is valid we get a token + user; otherwise it throws.
        const { user } = await api.me();
        if (!cancelled) setUser(user);
      } catch {
        if (!cancelled) {
          setAccessToken(null);
          setUser(null);
        }
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, []);

  const login = React.useCallback(async (email: string, password: string) => {
    const { access_token } = await api.login(email, password);
    setAccessToken(access_token);
    await reloadUser();
  }, [reloadUser]);

  const register = React.useCallback(
    (payload: { email: string; password: string; first_name?: string; last_name?: string }) =>
      api.register(payload),
    []
  );

  const logout = React.useCallback(async () => {
    try {
      await api.logout();
    } catch {
      /* even if the server call fails, clear local state */
    }
    setAccessToken(null);
    setUser(null);
  }, []);

  const value: AuthContextValue = {
    user,
    isAuthenticated: user !== null,
    isLoading,
    login,
    register,
    logout,
    reloadUser,
    setUser,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const ctx = React.useContext(AuthContext);
  if (!ctx) {
    throw new Error("useAuth must be used within an <AuthProvider>");
  }
  return ctx;
}

// Re-export for convenience in form handlers.
export { ApiRequestError };
