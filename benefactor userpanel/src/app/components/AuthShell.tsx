import React from "react";
import { HeartHandshake } from "lucide-react";
import { motion } from "motion/react";

/**
 * Shared frame for the auth pages (login / forgot / reset).
 *
 * Layout mirrors the original hand-built HTML: a form panel on the right
 * (RTL) and a decorative teal→gold gradient hero on the left, which collapses
 * on small screens. `hero` lets each page supply its own hero content.
 */
export function AuthShell({
  children,
  hero,
}: {
  children: React.ReactNode;
  hero: React.ReactNode;
}) {
  return (
    <div className="min-h-screen flex bg-background text-foreground" dir="rtl">
      {/* FORM PANEL — first in DOM → right in RTL */}
      <main className="flex-1 flex items-center justify-center px-6 py-8 min-h-screen">
        <motion.div
          initial={{ opacity: 0, y: 24 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
          className="w-full max-w-md"
        >
          {/* Mobile brand head (hidden on desktop where the hero shows it) */}
          <div className="flex lg:hidden flex-col items-center gap-3 mb-8 text-center">
            <div className="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
              <HeartHandshake size={32} />
            </div>
            <span className="font-bold text-lg">پنل خیرین مکسا</span>
          </div>

          {children}
        </motion.div>
      </main>

      {/* HERO PANEL — second in DOM → left in RTL */}
      <aside className="relative hidden lg:flex flex-col w-[46%] min-h-screen overflow-hidden p-12 text-white select-none bg-[linear-gradient(155deg,#003e3d_0%,#005a59_35%,#007b7a_68%,#4fb2b0_100%)]">
        {/* Drifting orbs */}
        <Orb className="w-[420px] h-[420px] -top-28 -left-32" delay={0} />
        <Orb className="w-72 h-72 bottom-[15%] -right-20" delay={1.2} />
        <Orb className="w-40 h-40 bottom-[8%] left-[8%]" delay={0.6} />

        {/* Background line-art */}
        <svg
          className="absolute inset-0 w-full h-full pointer-events-none"
          viewBox="0 0 520 800"
          preserveAspectRatio="xMidYMid slice"
          fill="none"
        >
          <circle cx="60" cy="120" r="260" stroke="rgba(255,255,255,0.04)" strokeWidth="55" />
          <circle cx="60" cy="120" r="180" stroke="rgba(255,255,255,0.035)" strokeWidth="35" />
          <circle cx="460" cy="730" r="220" stroke="rgba(255,255,255,0.03)" strokeWidth="50" />
          <circle cx="460" cy="730" r="140" stroke="rgba(255,255,255,0.04)" strokeWidth="25" />
        </svg>

        <div className="relative z-10 flex flex-col h-full">{hero}</div>
      </aside>
    </div>
  );
}

function Orb({ className, delay }: { className: string; delay: number }) {
  return (
    <motion.div
      aria-hidden
      className={`absolute rounded-full border border-white/[0.07] bg-[radial-gradient(circle,rgba(255,255,255,0.06)_0%,transparent_70%)] ${className}`}
      animate={{ x: [0, 16, 0], y: [0, 20, 0] }}
      transition={{ duration: 9, repeat: Infinity, ease: "easeInOut", delay }}
    />
  );
}
