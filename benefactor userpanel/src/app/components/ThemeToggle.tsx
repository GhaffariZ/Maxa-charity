import type { MouseEvent } from "react";
import { flushSync } from "react-dom";
import { Moon, Sun } from "lucide-react";
import { useTheme } from "next-themes";

export function ThemeToggle() {
  const { resolvedTheme, setTheme } = useTheme();
  const dark = resolvedTheme === "dark";

  function toggle(event: MouseEvent<HTMLButtonElement>) {
    const root = document.documentElement;
    if (root.dataset.themeTransition) return;
    const apply = () => flushSync(() => setTheme(dark ? "light" : "dark"));
    if (matchMedia("(prefers-reduced-motion: reduce)").matches) { apply(); return; }

    const clear = () => { delete root.dataset.themeTransition; };
    if (!document.startViewTransition) {
      root.dataset.themeTransition = "fade";
      apply();
      window.setTimeout(clear, 260);
      return;
    }

    const box = event.currentTarget.getBoundingClientRect();
    const x = box.x + box.width / 2, y = box.y + box.height / 2;
    const radius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
    root.style.setProperty("--theme-x", `${x}px`);
    root.style.setProperty("--theme-y", `${y}px`);
    root.style.setProperty("--theme-radius", `${radius}px`);
    root.dataset.themeTransition = "reveal";
    try {
      const transition = document.startViewTransition(apply);
      // Hidden tabs and interrupted transitions may skip the animation; the theme still changes.
      void transition.ready.catch(() => {});
      void transition.finished.then(clear, clear);
    } catch {
      apply();
      clear();
    }
  }

  return (
    <button type="button" className="donor-theme-toggle" onClick={toggle}
      aria-label="حالت تاریک" aria-pressed={dark} title={dark ? "تغییر به حالت روشن" : "تغییر به حالت تاریک"}>
      {dark ? <Sun size={20} strokeWidth={1.75} aria-hidden="true" /> : <Moon size={20} strokeWidth={1.75} aria-hidden="true" />}
    </button>
  );
}
