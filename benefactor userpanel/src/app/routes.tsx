import { createBrowserRouter } from "react-router";
import { Dashboard } from "./views/Dashboard";
import { Layout } from "./components/Layout";
import { Payments } from "./views/Payments";
import { Campaigns } from "./views/Campaigns";
import { Profile } from "./views/Profile";
import { Impact } from "./views/Impact";
import { History } from "./views/History";
import { Login } from "./views/Login";
import { Forgot } from "./views/Forgot";
import { Reset } from "./views/Reset";
import { Verify } from "./views/Verify";
import { RequireAuth, RedirectIfAuthed } from "./auth";

export const router = createBrowserRouter(
  [
    // Public auth routes (bounce to dashboard if already signed in).
    { path: "/login", element: <RedirectIfAuthed><Login /></RedirectIfAuthed> },
    { path: "/forgot", element: <RedirectIfAuthed><Forgot /></RedirectIfAuthed> },
    { path: "/reset", element: <RedirectIfAuthed><Reset /></RedirectIfAuthed> },
    // Email verification is reachable whether or not the user is signed in.
    { path: "/verify", element: <Verify /> },

    // Private dashboard (requires auth).
    {
      path: "/",
      element: (
        <RequireAuth>
          <Layout />
        </RequireAuth>
      ),
      children: [
        { index: true, Component: Dashboard },
        { path: "payments", Component: Payments },
        { path: "campaigns", Component: Campaigns },
        { path: "profile", Component: Profile },
        { path: "impact", Component: Impact },
        { path: "history", Component: History },
      ],
    },
  ],
  {
    basename: "/benefactor-dashboard",
  }
);
