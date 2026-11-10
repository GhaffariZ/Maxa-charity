import type { CSSProperties } from "react";
import { cn } from "./utils";

export interface LoaderProps {
  variant?: "helix" | "pulse";
  size?: number;
  speed?: number;
  label?: string;
  className?: string;
}

// The supplied helix motion, in shared CSS so PHP and React use the same animation.
export function Loader({ variant = "helix", size = 48, speed = 1.8, label = "در حال بارگذاری", className }: LoaderProps) {
  return (
    <span className={cn("macsa-loader", className)} role="status" aria-label={label}
      style={{ "--loader-size": `${size}px`, "--loader-speed": `${speed}s` } as CSSProperties}>
      {variant === "helix" ? (
        <span className="macsa-loader__helix" aria-hidden="true">
          {Array.from({ length: 7 }, (_, row) => (
            <span className="macsa-loader__pair" key={row} style={{ "--row": row } as CSSProperties}><i /><i /></span>
          ))}
        </span>
      ) : (
        <svg className="macsa-loader__pulse" viewBox="0 0 120 64" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <path className="macsa-loader__track" d="M2 34H28L36 26L44 42L54 8L66 56L76 28L84 34H118" />
          <path className="macsa-loader__beat" pathLength="200" d="M2 34H28L36 26L44 42L54 8L66 56L76 28L84 34H118" />
        </svg>
      )}
    </span>
  );
}
