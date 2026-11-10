import { RouterProvider } from "react-router";
import { router } from "./routes";
import { ThemeProvider } from "next-themes";
import { Toaster } from "./components/ui/sonner";
import { AuthProvider } from "../contexts/AuthContext";

export default function App() {
  return (
    <ThemeProvider attribute="class" storageKey="macsa-donor-theme" defaultTheme="light" enableSystem={false}>
      <AuthProvider>
        <RouterProvider router={router} />
        <Toaster position="top-center" richColors />
      </AuthProvider>
    </ThemeProvider>
  );
}
