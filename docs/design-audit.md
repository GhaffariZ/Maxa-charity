# MACSA Preserve redesign: implementation and Step 4 audit

Date: 2026-09-30. Status: **BLOCKED, not complete**. Four Section 14 failures remain. Your instruction that any Fail blocks completion is enforced.

Design-rule source: [tasteskill v2 experimental](../.agents/skills/design-taste-frontend/SKILL.md). Section 14 says: "If a single checkbox cannot be honestly ticked, the page is not done. Fix it before delivering."

## Implemented scope

Homepage components, shared navigation/footer, contact appearance, public document assembly, news footer execution, and additive hero image delivery. Preserve mode uses typography, spacing/rhythm, color recalibration, quiet accessible interactions, and selective hero/key-section recomposition, in that order. No replacement framework or motion library was added.

The shared header replaces duplicated homepage navigation. Keyboard menus now expose state, close with Escape and restore focus. Native sticky navigation replaces scroll-offset scripts. The hero retains its carousel and original photographs but uses explicit navigation rather than autoplay. Stories no longer duplicate or move continuously, and statistics show their final values. Newsletter requests use the existing contact endpoint and clearly describe a request sent to the MACSA team, rather than claiming automatic subscription.

Seven WebP variants reduce the combined selected photo payload from 11,048,085 bytes to 309,392 bytes (about 97%); original photo files and URLs remain available. Text compression is enabled when Apache mod_deflate is present. Shared critical CSS is included in the document, and font/hero discovery uses preload. Invalid empty-island map path data was removed without changing province paths or anchor IDs.

## Evidence and limits

Actual PHP templates were rendered using the existing fake PDO fixture, then served locally with mocked hero/news/story/contact APIs. **This is not a live database or production deployment test.** No real newsletter, contact, payment, or patient-intake request was sent. Unseen CMS content, authenticated sessions, live branch data, real mail delivery, actual search rankings and production response times remain unverified.

Runnable check: `node tests/public-site-smoke.cjs`; set `PLAYWRIGHT_MODULE` to an available Playwright module and `BROWSER_CHANNEL=msedge` when using installed Edge. PHP CLI needs mbstring. The check renders the real public components and compares protected source values against Git HEAD. A standalone local fixture preview uses `--serve` and optional `PREVIEW_PORT`.

PASS: PHP syntax checks for every changed PHP file; JavaScript syntax; Git whitespace check; 10 homepage combinations (1440/1024/768/390/320 pixels, light/dark); one page H1 and one shared header; no horizontal overflow; hero CTA within a 900px viewport; keyboard mega menu; drawer Escape/focus restoration; native sticky behavior; explicit slide navigation/no autoplay; API failure fallback; newsletter failure retains email and retry submits the expected payload; unchanged contact field order. Contact screenshot is a visual check, not a submission check.

Latest Lighthouse against the local compressed fixture: **performance: 92, accessibility: 100, best-practices: 100, seo: 91**. LCP **3.3 s**, CLS **0.019**, TBT **110 ms**. INP requires real interaction/field data and is not certified here. The local preview includes the actual large inline regional map, but uses fixture content. These scores must not be presented as production scores.

Evidence: [responsive checks](design-audit/checks.json), [line counts](design-audit/line-counts.json), [CTA geometry](design-audit/copy-geometry.json), [full Lighthouse report](design-audit/lighthouse.json).

## Em-dash audit

PASS for the inspected homepage strings in all ten views: zero visible em dashes. Removed the news-title em dash and em dashes in touched comments. Hero CMS title/description normalise that character for display. No global database rewrite was made. Live CMS/news/story/alt content is unverified, so this result is limited to the rendered evidence, not a claim that every production record is clear.

## Preservation audit

| Protected item | Changed values |
| --- | --- |
| Existing public route structure and Apache rewrite/error targets | None |
| Existing href/data-link target values in touched templates | None |
| Primary navigation labels and order | None |
| Form field names and contact field order | None |
| Existing section/anchor IDs | None |
| Brand logo/wordmark image files | None |
| Existing footer privacy and copyright wording | None |
| robots.txt and sitemap.php | None |

The source check compares literal target/name/ID sets; dynamic destinations are passed through as before. Homepage/header targets are compared together because duplicated navigation moved to the shared header. This audit does not certify destinations that require live CMS data. It also does not imply the old targets are all working.

New asset URLs, **additions rather than route migrations**:

- `/uploads/hero/hero_1780827718_9482.png.webp`
- `/uploads/hero/hero_1780343574_6054.png.webp`
- `/dashboard/components/hamrah/images/1.png.webp` through `5.png.webp`
- `/assets/public-site.js` (new script)

`public-site.css` is a new source file included inline. Existing title data in the CMS was not edited; the homepage now uses the shared title template instead of the hard-coded "CTA Hero Slider" title. The generic shared meta description was added. Existing heading text remains; extra component H1s became H2s and subordinate headings now follow a consistent hierarchy.

Existing suspicious targets deliberately preserved: disabled `/patient-option` (committed page uses `/patientoption`), `contactus.html`, `/MACSAservices.html`, `/amoozesh_maharati.html`, `/image-gallery.html`, `/video-gallery.html`, `/under-construction.html`, and service-menu `#` placeholders. CMS pages might resolve some of these; no live resolution was available. Mega-menu `javascript:void(0);` targets trigger Lighthouse's non-crawlable-links finding. Repairing/removing protected destinations requires your approval. Section 11.F states: "Never modify without explicit user approval" for URL structure, primary nav labels, field names/order, logo and legal copy.

## Brand fidelity audit

PASS in the tested implementation. Original brand teal **#008f8a**, orange accent **#faa61a**, self-hosted **Vazirmatn, sans-serif**, header pale logo backing and footer inverse wordmark survive. Logo files are byte-unchanged. The footer inverse logo retains a teal backing within the shared page theme. Accessible action shades (#006b67 in light, #80d4cb in dark) supplement the base teal rather than replacing the identity. Radius tokens are 10px controls/16px content and imagery; slider state dots are circular. Real original photography and Persian copy voice survive.

## Section 14 Pre-Flight Check: every box

PASS applies to the scoped template/fixture evidence above; N/A states the reason. No production-wide certification is implied.

| # | Check | Result | Evidence or reason |
| --- | --- | --- | --- |
| 1 | Brief inference | PASS (scope) | Public Persian charity site for patients, families, donors, volunteers and care professionals; modernise while preserving identity and conversion routes. |
| 2 | Dial values | PASS (scope) | DESIGN_VARIANCE 6, MOTION_INTENSITY 3, VISUAL_DENSITY 5. Real photographs and varied sections carry the design; patient-facing interactions remain quiet. |
| 3 | Design system | PASS (scope) | Existing MACSA identity, native PHP/CSS/JavaScript. No imported visual system. |
| 4 | Redesign mode | PASS (scope) | Section 11 audit and Preserve mode declaration preceded implementation and received user OK. |
| 5 | ZERO em-dashes (`em dash`) anywhere on the page. | PASS (scope) | Zero visible em dashes in all ten homepage views. Static touched page strings scanned; CMS hero text normalises the character. Live CMS/news/story records remain unverified. |
| 6 | Page Theme Lock | PASS (scope) | Auto theme applies to every content section and footer; original branded navigation and event banner keep their identity colors. |
| 7 | Color Consistency Lock | PASS (scope) | Orange #faa61a is the shared action accent; teal #008f8a remains the brand base. Accessible teal action shades are deliberate semantic tokens. |
| 8 | Shape Consistency Lock | PASS (scope) | Controls 10px, media/content 16px; slider indicators use circles as state markers. |
| 9 | Button Contrast Check | PASS (scope) | Local Lighthouse accessibility 100; dark/light action tokens and orange text checked. |
| 10 | CTA Button Wrap | PASS (scope) | All inspected desktop primary CTAs occupy one line; geometry recorded in copy-geometry.json. |
| 11 | Form Contrast Check | PASS (scope) | Newsletter and contact controls have explicit surface/text/placeholder/focus tokens. This pass does not certify unseen donation or intake states. |
| 12 | Serif discipline | N/A | N/A: no serif type. |
| 13 | Premium-consumer palette check | N/A | N/A: charity brief, with established teal/orange brand. |
| 14 | Italic descender clearance | N/A | N/A: no italic display copy. |
| 15 | Hero fits the viewport | PASS (scope) | Headline approximately two lines at 1440/1024/390/320 and one at 768; seed description seven words; CTA visible in every tested 900px-high viewport. |
| 16 | Hero top padding | PASS (scope) | Hero outer top spacing 28px desktop, 16px tablet/mobile. |
| 17 | Hero stack discipline | PASS (scope) | Four elements: charity eyebrow, title, description, action. Loading/error status is outside the hero. |
| 18 | EYEBROW COUNT (mechanical) | PASS (scope) | Zero uppercase/tracking eyebrows across the inspected sections; semantic Persian labels remain. |
| 19 | Split-Header Ban | PASS (scope) | Section titles and explanatory copy stack vertically. |
| 20 | Zigzag Alternation Cap | PASS (scope) | Gallery, service collage, editorial stories, news and regional statistics interrupt any alternating split sequence. |
| 21 | No Duplicate CTA Intent | FAIL | FAIL: preserved header donation and donation-band actions share the /onlinedonation intent. Removing an existing link requires approval under the preservation brief. |
| 22 | Logo wall = logo only | N/A | N/A: no partner logo wall. |
| 23 | Bento Background Diversity | N/A | N/A: no bento layout. The audience gallery uses five distinct real photographs. |
| 24 | "Used by / Trusted by" logo wall | N/A | N/A: no trusted-by wall. |
| 25 | Copy Self-Audit | PASS (scope) | Static visible copy reviewed; corrected the existing تجهیزات typo. No invented testimonials or claims added. Database-generated copy is not certified. |
| 26 | Motion motivated | PASS (scope) | Slider transition follows explicit navigation; drawer/menu transition communicates state. Autoplay, testimonial marquee and counter animation removed. |
| 27 | Marquee max-one-per-page | PASS (scope) | Zero marquees; stories are a scrollable static list. |
| 28 | Navigation on ONE line | PASS (scope) | Desktop navigation fits one line at 1440px, header 78px; narrower widths use the drawer. |
| 29 | Section-Layout-Repetition | FAIL | FAIL under the literal no-two-sections rule: services, stories and statistics retain two-column layouts, despite different content and presentation. |
| 30 | Bento has rhythm AND exact cell count | N/A | N/A: no bento. Gallery contains five items and five photo cells. |
| 31 | Long lists use the right UI component | PASS (scope) | Service lists use a compact grid, navigation groups use a mega menu, story collections use a scrollable editorial list. |
| 32 | Real images used | PASS (scope) | Original site photography preserved; seven smaller WebP variants added. No generated fake interfaces or decorative SVG art. |
| 33 | No pills/labels overlaid on images | PASS (scope) | Photo gallery labels sit below images; news badge moved into content rather than over the photo. Map labels describe real places. |
| 34 | No photo-credit captions as decoration | PASS (scope) | No decorative photo credits. |
| 35 | No version footers | PASS (scope) | No version footer. |
| 36 | No micro-meta-sentences | PASS (scope) | No added micro-meta copy. |
| 37 | No decoration text strip at hero bottom | PASS (scope) | No decorative hero-bottom strip. |
| 38 | No floating top-right sub-text | PASS (scope) | No floating heading subtext. |
| 39 | No scoring/progress bars with filled background tracks | PASS (scope) | No comparison score bars. |
| 40 | No locale / city-name / time / weather strips | PASS (scope) | The Iran coverage map is directly relevant to nationwide care; event countdown represents a real event date, not ambient decoration. |
| 41 | No scroll cues | PASS (scope) | No scroll cue. |
| 42 | No version labels in hero | PASS (scope) | No hero version label. |
| 43 | No section-numbering eyebrows | PASS (scope) | No numbered eyebrows. |
| 44 | No decorative dots | PASS (scope) | Slider dots identify active slides; news decorative dot removed. |
| 45 | No `border-t` + `border-b` on every row | PASS (scope) | Story/service rows use one bottom border, not both borders. |
| 46 | Content density | PASS (scope) | No long data table; seed paragraphs stay within 25 words. Existing statistics are retained, without claiming independent validation. |
| 47 | Quotes ≤ 3 lines | PASS (scope) | Seed testimonial paragraphs fit one to three lines at all measured widths; attribution contains no em dash. |
| 48 | Motion claimed = motion shown | N/A | N/A: motion dial is 3. |
| 49 | GSAP sticky-stack / horizontal-pan | N/A | N/A: no GSAP pinned stack or horizontal pan. |
| 50 | No `window.addEventListener('scroll')` | PASS (scope) | No window scroll listeners remain in the touched public navigation/banner/statistics implementation; sticky behavior is native CSS. |
| 51 | Reduced motion | PASS (scope) | Reduced-motion override suppresses animations/transitions; all responsive checks ran with reduced motion. |
| 52 | Dark mode | PASS (scope) | Semantic dark tokens defined; both modes tested at five widths and captured. |
| 53 | Mobile collapse | PASS (scope) | Explicit single-column collapse below 1024px, with mobile spacing and 320px title adjustment. |
| 54 | Viewport stability | PASS (scope) | Drawer uses 100dvh with a 100vh fallback; hero has natural mobile height and no h-screen. |
| 55 | `useEffect` animations | N/A | N/A: no React/useEffect. |
| 56 | Empty / loading / error | PASS (scope) | Hero loading/empty/error fallback, existing news empty state, and newsletter pending/failure/retry/success request status present. |
| 57 | Cards omitted | PASS (scope) | Action links, services, stories and statistics use spacing and dividing lines; remaining frames contain photos or meaningful grouped content. |
| 58 | Icons | FAIL | FAIL: existing Iconoir icons and inline SVG icons remain. Iconoir is outside the skill allowed set. This is an outstanding implementation issue, not a protected asset restriction. |
| 59 | Motion | N/A | N/A: native PHP/JavaScript; no React client boundary or memoization requirement. |
| 60 | No AI Tells | PASS (scope) | Vazirmatn and real MACSA assets/copy retained; no generic personas, AI-purple, or three-equal feature cards introduced. |
| 61 | Core Web Vitals | FAIL | FAIL: latest local Lighthouse mobile LCP exceeds 2.5s. CLS is below 0.1; TBT is a lab proxy, not a measurement of field INP. |
| 62 | One design system | PASS (scope) | One preserved MACSA visual system; no mixed component-library styling. |

## Remaining blockers

1. Mobile LCP exceeds the 2.5-second rule. Further optimisation and a production measurement are required; a high overall score does not override this failure.
2. Existing donation actions repeat one intent. Removing an existing CTA/link conflicts with the approved preservation constraint and needs an explicit decision.
3. Multiple existing two-column sections fail the literal layout-family uniqueness rule. They need more recomposition or a user-approved rule adjustment.
4. Existing Iconoir/inline SVG icons need an allowed-library migration. Logo and map artwork must remain intact.

Separately, live CMS/database, mail, branch and ranking verification is unavailable. Approval to repair specific protected targets should identify the destinations; no replacement destinations will be invented. The implementation is saved for review and is not marked complete or deployed.

## Captures

[Desktop light](design-audit/home-1440-light.webp), [desktop dark](design-audit/home-1440-dark.webp), [mobile light](design-audit/home-390-light.webp), [mobile dark](design-audit/home-390-dark.webp), [contact mobile](design-audit/contact-390.webp).
