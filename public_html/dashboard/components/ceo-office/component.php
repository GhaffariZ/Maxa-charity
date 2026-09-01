<!-- ============================================================
     CEO Office — مدیرعامل
     Creative introduction page for the Chief Executive Officer
     ============================================================ -->
<section class="ceo-office" dir="rtl">

<style>
/* Self-hosted Vazirmatn variable font */
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
       url('/webfont/Vazirmatn[wght].woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}

.ceo-office {
  --ceo-primary: #007d82;
  --ceo-primary-dark: #005f63;
  --ceo-primary-light: #0da1a5;
  --ceo-accent: #d4af37;
  --ceo-accent-soft: #fff7d6;
  --ceo-dark: #0f172a;
  --ceo-muted: #64748b;
  --ceo-bg: #f6fbfb;
  --ceo-card: #ffffff;
  --ceo-border: #e2e8f0;
  --ceo-radius: 22px;
  --ceo-radius-md: 14px;
  --ceo-shadow: 0 24px 60px rgba(0, 80, 85, 0.10);
  --ceo-shadow-lg: 0 32px 80px rgba(0, 80, 85, 0.16);

  font-family: 'Vazirmatn', Tahoma, sans-serif;
  color: var(--ceo-dark);
  line-height: 1.7;
  background: var(--ceo-bg);
  padding: 0 0 60px;
}

.ceo-office *, .ceo-office *::before, .ceo-office *::after {
  box-sizing: border-box;
}

.ceo-office a {
  color: inherit;
  text-decoration: none;
}

.ceo-office img {
  max-width: 100%;
  display: block;
}

/* ===== HERO SECTION ===== */
.ceo-hero {
  position: relative;
  min-height: 520px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: linear-gradient(160deg, #003d3f 0%, #005f63 35%, #007d82 60%, #0da1a5 100%);
}

.ceo-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 20% 30%, rgba(212, 175, 55, 0.15), transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(13, 161, 165, 0.25), transparent 50%),
    radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.04), transparent 60%);
  pointer-events: none;
}

/* Animated decorative circles */
.ceo-hero::after {
  content: '';
  position: absolute;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.06);
  top: -120px;
  left: -100px;
  pointer-events: none;
}

.ceo-hero-inner {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 80px 32px 60px;
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 48px;
  align-items: center;
}

.ceo-hero-text {
  color: #ffffff;
}

.ceo-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(212, 175, 55, 0.2);
  border: 1px solid rgba(212, 175, 55, 0.35);
  color: #fbbf24;
  padding: 6px 16px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 20px;
  backdrop-filter: blur(8px);
}

.ceo-hero-badge svg {
  width: 16px;
  height: 16px;
}

.ceo-hero h1 {
  margin: 0 0 8px;
  font-size: 42px;
  font-weight: 900;
  line-height: 1.3;
  letter-spacing: -0.02em;
}

.ceo-hero h1 span {
  display: block;
  background: linear-gradient(135deg, #fbbf24, #f59e0b, #d4af37);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.ceo-hero-role {
  font-size: 18px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.85);
  margin-bottom: 16px;
}

.ceo-hero-desc {
  font-size: 15px;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.9;
  max-width: 520px;
}

.ceo-hero-actions {
  display: flex;
  gap: 12px;
  margin-top: 28px;
  flex-wrap: wrap;
}

.ceo-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 999px;
  font-size: 14px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  text-decoration: none;
}

.ceo-btn-primary {
  background: linear-gradient(135deg, var(--ceo-accent), #b78f1e);
  color: #1a1a1a;
  box-shadow: 0 8px 24px rgba(212, 175, 55, 0.35);
}

.ceo-btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 32px rgba(212, 175, 55, 0.45);
}

.ceo-btn-outline {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
}

.ceo-btn-outline:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.4);
  transform: translateY(-2px);
}

/* Portrait */
.ceo-portrait {
  position: relative;
  display: flex;
  justify-content: center;
}

.ceo-portrait-frame {
  position: relative;
  width: 320px;
  height: 400px;
  border-radius: 24px;
  overflow: hidden;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.02));
  border: 2px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 32px 64px rgba(0, 0, 0, 0.3);
}

.ceo-portrait-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Decorative ring behind portrait */
.ceo-portrait-ring {
  position: absolute;
  width: 360px;
  height: 360px;
  border-radius: 50%;
  border: 2px dashed rgba(212, 175, 55, 0.25);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
  animation: ceo-spin 40s linear infinite;
}

@keyframes ceo-spin {
  from { transform: translate(-50%, -50%) rotate(0deg); }
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

.ceo-portrait-accent {
  position: absolute;
  bottom: -20px;
  right: -20px;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--ceo-accent), #b78f1e);
  opacity: 0.3;
  filter: blur(20px);
  pointer-events: none;
}

/* ===== STATS BAR ===== */
.ceo-stats {
  max-width: 1200px;
  margin: -40px auto 0;
  padding: 0 32px;
  position: relative;
  z-index: 10;
}

.ceo-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  background: var(--ceo-card);
  border-radius: var(--ceo-radius);
  padding: 28px 32px;
  box-shadow: var(--ceo-shadow-lg);
  border: 1px solid rgba(0, 125, 130, 0.08);
}

.ceo-stat {
  text-align: center;
  padding: 12px 8px;
  border-radius: var(--ceo-radius-md);
  transition: background 0.3s ease;
}

.ceo-stat:hover {
  background: rgba(0, 125, 130, 0.04);
}

.ceo-stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(0, 125, 130, 0.1), rgba(13, 161, 165, 0.06));
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
}

.ceo-stat-icon svg {
  width: 22px;
  height: 22px;
  color: var(--ceo-primary);
}

.ceo-stat-value {
  font-size: 28px;
  font-weight: 900;
  color: var(--ceo-primary-dark);
  line-height: 1.2;
}

.ceo-stat-label {
  font-size: 13px;
  color: var(--ceo-muted);
  margin-top: 4px;
  font-weight: 600;
}

/* ===== MESSAGE SECTION ===== */
.ceo-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 32px 0;
}

.ceo-section-header {
  text-align: center;
  margin-bottom: 40px;
}

.ceo-section-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(0, 125, 130, 0.08);
  color: var(--ceo-primary);
  padding: 6px 16px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 14px;
}

.ceo-section-title {
  font-size: 30px;
  font-weight: 900;
  color: var(--ceo-dark);
  margin: 0 0 8px;
}

.ceo-section-subtitle {
  font-size: 15px;
  color: var(--ceo-muted);
  max-width: 600px;
  margin: 0 auto;
}

.ceo-message-card {
  background: var(--ceo-card);
  border-radius: var(--ceo-radius);
  padding: 40px;
  box-shadow: var(--ceo-shadow);
  border: 1px solid rgba(0, 125, 130, 0.08);
  position: relative;
  overflow: hidden;
}

.ceo-message-card::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.08), transparent 70%);
  pointer-events: none;
}

.ceo-message-quote {
  position: relative;
  z-index: 1;
}

.ceo-quote-icon {
  font-size: 64px;
  line-height: 1;
  color: rgba(0, 125, 130, 0.12);
  font-family: Georgia, serif;
  margin-bottom: -20px;
}

.ceo-message-text {
  font-size: 17px;
  line-height: 2;
  color: #374151;
  margin-bottom: 24px;
}

.ceo-message-text p {
  margin-bottom: 16px;
}

.ceo-message-text p:last-child {
  margin-bottom: 0;
}

.ceo-message-signature {
  display: flex;
  align-items: center;
  gap: 16px;
  padding-top: 20px;
  border-top: 1px solid rgba(0, 125, 130, 0.1);
}

.ceo-sig-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  overflow: hidden;
  border: 2px solid rgba(0, 125, 130, 0.15);
  flex-shrink: 0;
}

.ceo-sig-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.ceo-sig-info h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 800;
  color: var(--ceo-dark);
}

.ceo-sig-info p {
  margin: 2px 0 0;
  font-size: 13px;
  color: var(--ceo-muted);
  font-weight: 600;
}

/* ===== BIOGRAPHY SECTION ===== */
.ceo-bio-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.ceo-bio-card {
  background: var(--ceo-card);
  border-radius: var(--ceo-radius);
  padding: 32px;
  box-shadow: var(--ceo-shadow);
  border: 1px solid rgba(0, 125, 130, 0.08);
  position: relative;
  overflow: hidden;
}

.ceo-bio-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, var(--ceo-primary), var(--ceo-primary-light));
  border-radius: 0 4px 4px 0;
}

.ceo-bio-card-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(0, 125, 130, 0.1), rgba(13, 161, 165, 0.05));
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.ceo-bio-card-icon svg {
  width: 20px;
  height: 20px;
  color: var(--ceo-primary);
}

.ceo-bio-card h3 {
  margin: 0 0 12px;
  font-size: 17px;
  font-weight: 800;
  color: var(--ceo-dark);
}

.ceo-bio-card p,
.ceo-bio-card li {
  font-size: 14px;
  color: #475569;
  line-height: 1.9;
}

.ceo-bio-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.ceo-bio-card ul li {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 6px;
}

.ceo-bio-card ul li::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--ceo-primary);
  flex-shrink: 0;
  margin-top: 8px;
}

/* ===== VISION SECTION ===== */
.ceo-vision {
  background: linear-gradient(160deg, #003d3f, #005f63 50%, #007d82);
  border-radius: var(--ceo-radius);
  padding: 48px;
  color: #ffffff;
  position: relative;
  overflow: hidden;
}

.ceo-vision::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.12), transparent 50%),
    radial-gradient(circle at 20% 80%, rgba(13, 161, 165, 0.2), transparent 50%);
  pointer-events: none;
}

.ceo-vision-inner {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 32px;
  align-items: start;
}

.ceo-vision-icon {
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: rgba(212, 175, 55, 0.2);
  border: 1px solid rgba(212, 175, 55, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ceo-vision-icon svg {
  width: 28px;
  height: 28px;
  color: #fbbf24;
}

.ceo-vision h3 {
  margin: 0 0 12px;
  font-size: 22px;
  font-weight: 800;
}

.ceo-vision p {
  font-size: 15px;
  line-height: 2;
  color: rgba(255, 255, 255, 0.8);
  margin: 0;
}

.ceo-vision-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 20px;
}

.ceo-vision-tag {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: rgba(255, 255, 255, 0.9);
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
  backdrop-filter: blur(8px);
}

/* ===== TIMELINE ===== */
.ceo-timeline {
  position: relative;
  padding-right: 32px;
}

.ceo-timeline::before {
  content: '';
  position: absolute;
  right: 12px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, var(--ceo-primary), rgba(0, 125, 130, 0.15));
  border-radius: 2px;
}

.ceo-timeline-item {
  position: relative;
  padding: 0 0 32px 0;
}

.ceo-timeline-item:last-child {
  padding-bottom: 0;
}

.ceo-timeline-dot {
  position: absolute;
  right: -26px;
  top: 4px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: var(--ceo-primary);
  border: 3px solid var(--ceo-bg);
  box-shadow: 0 0 0 2px var(--ceo-primary);
}

.ceo-timeline-year {
  display: inline-block;
  background: rgba(0, 125, 130, 0.1);
  color: var(--ceo-primary-dark);
  padding: 3px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 8px;
}

.ceo-timeline-item h4 {
  margin: 0 0 4px;
  font-size: 15px;
  font-weight: 700;
  color: var(--ceo-dark);
}

.ceo-timeline-item p {
  margin: 0;
  font-size: 13px;
  color: var(--ceo-muted);
  line-height: 1.7;
}

/* ===== CONTACT CARD ===== */
.ceo-contact-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.ceo-contact-card {
  background: var(--ceo-card);
  border-radius: var(--ceo-radius);
  padding: 32px;
  box-shadow: var(--ceo-shadow);
  border: 1px solid rgba(0, 125, 130, 0.08);
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.ceo-contact-card h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: var(--ceo-dark);
  display: flex;
  align-items: center;
  gap: 10px;
}

.ceo-contact-card h3 svg {
  width: 20px;
  height: 20px;
  color: var(--ceo-primary);
}

.ceo-contact-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.ceo-contact-list li {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(0, 125, 130, 0.06);
  font-size: 14px;
  color: #475569;
}

.ceo-contact-list li:last-child {
  border-bottom: none;
}

.ceo-contact-list li svg {
  width: 18px;
  height: 18px;
  color: var(--ceo-primary);
  flex-shrink: 0;
}

.ceo-contact-list li strong {
  font-weight: 700;
  color: var(--ceo-dark);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .ceo-hero-inner {
    grid-template-columns: 1fr;
    text-align: center;
    padding: 60px 24px 40px;
  }

  .ceo-hero-desc {
    margin: 0 auto;
  }

  .ceo-hero-actions {
    justify-content: center;
  }

  .ceo-portrait {
    order: -1;
  }

  .ceo-portrait-frame {
    width: 240px;
    height: 300px;
  }

  .ceo-portrait-ring {
    width: 280px;
    height: 280px;
  }

  .ceo-stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .ceo-bio-grid {
    grid-template-columns: 1fr;
  }

  .ceo-contact-grid {
    grid-template-columns: 1fr;
  }

  .ceo-vision-inner {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .ceo-vision-icon {
    margin: 0 auto;
  }

  .ceo-vision-tags {
    justify-content: center;
  }
}

@media (max-width: 600px) {
  .ceo-hero h1 {
    font-size: 28px;
  }

  .ceo-hero-role {
    font-size: 15px;
  }

  .ceo-portrait-frame {
    width: 200px;
    height: 260px;
  }

  .ceo-stats-grid {
    grid-template-columns: 1fr 1fr;
    padding: 20px 16px;
    gap: 10px;
  }

  .ceo-stat-value {
    font-size: 22px;
  }

  .ceo-message-card {
    padding: 24px 20px;
  }

  .ceo-message-text {
    font-size: 15px;
  }

  .ceo-vision {
    padding: 28px 20px;
  }

  .ceo-section {
    padding: 40px 16px 0;
  }

  .ceo-section-title {
    font-size: 24px;
  }

  .ceo-bio-card {
    padding: 24px 20px;
  }

  .ceo-contact-card {
    padding: 24px 20px;
  }

  .ceo-hero-actions {
    flex-direction: column;
    align-items: center;
  }
}

/* ===== ANIMATIONS ===== */
.ceo-fade-up {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1),
              transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.ceo-fade-up.ceo-visible {
  opacity: 1;
  transform: translateY(0);
}
</style>

<!-- ===== HERO ===== -->
<div class="ceo-hero">
  <div class="ceo-hero-inner">
    <div class="ceo-hero-text">
      <div class="ceo-hero-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        مدیریت ارشد مکسا
      </div>
      <h1>
        مدیرعامل
        <span>مؤسسه مکسا</span>
      </h1>
      <div class="ceo-hero-role">مدیرعامل و عضو هیئت مدیره — مؤسسه نیکوکاری کنترل سرطان ایرانیان</div>
      <p class="ceo-hero-desc">
        رهبری و مدیریت راهبردی مؤسسه مکسا با هدف ارتقای مراقبت‌های حمایتی و تسکینی بیماران مبتلا به سرطان در سراسر کشور
      </p>
      <div class="ceo-hero-actions">
        <a href="#ceo-message" class="ceo-btn ceo-btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          پیام مدیرعامل
        </a>
        <a href="#ceo-bio" class="ceo-btn ceo-btn-outline">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          بیوگرافی
        </a>
      </div>
    </div>
    <div class="ceo-portrait">
      <div class="ceo-portrait-ring"></div>
      <div class="ceo-portrait-frame">
        <img src="{{image1}}" alt="مدیرعامل مؤسسه مکسا">
      </div>
      <div class="ceo-portrait-accent"></div>
    </div>
  </div>
</div>

<!-- ===== STATS ===== -->
<div class="ceo-stats ceo-fade-up">
  <div class="ceo-stats-grid">
    <div class="ceo-stat">
      <div class="ceo-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div class="ceo-stat-value">۱۵+</div>
      <div class="ceo-stat-label">سال تجربه مدیریتی</div>
    </div>
    <div class="ceo-stat">
      <div class="ceo-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="ceo-stat-value">۷</div>
      <div class="ceo-stat-label">شعبه فعال در سراسر کشور</div>
    </div>
    <div class="ceo-stat">
      <div class="ceo-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
      </div>
      <div class="ceo-stat-value">۲۰۰+</div>
      <div class="ceo-stat-label">بیمار تحت پوشش مراقبتی</div>
    </div>
    <div class="ceo-stat">
      <div class="ceo-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
      </div>
      <div class="ceo-stat-value">۵۰+</div>
      <div class="ceo-stat-label">کارگاه و همایش آموزشی</div>
    </div>
  </div>
</div>

<!-- ===== MESSAGE FROM CEO ===== -->
<div class="ceo-section ceo-fade-up" id="ceo-message">
  <div class="ceo-section-header">
    <div class="ceo-section-tag">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      پیام مدیرعامل
    </div>
    <h2 class="ceo-section-title">باور ما، زندگی با کرامت برای همه بیماران است</h2>
    <p class="ceo-section-subtitle">دیدگاه و رویکرد مدیرعامل مؤسسه مکسا به مراقبت‌های حمایتی و تسکینی</p>
  </div>

  <div class="ceo-message-card">
    <div class="ceo-message-quote">
      <div class="ceo-quote-icon">"</div>
      <div class="ceo-message-text">
        <p>
          مؤسسه نیکوکاری کنترل سرطان ایرانیان (مکسا) با هدف تأمین مراقبت‌های جامع حمایتی و تسکینی برای بیماران مبتلا به سرطان تأسیس شد. ما باور داریم که هر بیمار، صرف‌نظر از شرایط اقتصادی و اجتماعی خود، حق دارد به مراقبت‌های باکیفیت و انسانی دسترسی داشته باشد.
        </p>
        <p>
          تیم چندرشته‌ای مکسا شامل پزشکان، پرستاران، روانشناسان، مددکاران اجتماعی و متخصصین تغذیه، با همکاری یکدیگر خدمات جامعی را از مشاوره ژنتیک تا مراقبت‌های پایان زندگی ارائه می‌دهند. هدف ما نه‌تنها درمان، بلکه بهبود کیفیت زندگی بیماران و خانواده‌هایشان در تمام مراحل بیماری است.
        </p>
        <p>
          با حمایت شما خیرین عزیز، توانسته‌ایم شعب خود را در شهرهای مختلف کشور گسترش دهیم و خدمات آموزشی و پژوهشی گسترده‌ای را در حوزه مراقبت‌های تسکینی برگزار کنیم. از شما دعوت می‌کنیم تا در این مسیر خداپسندانه، ما را یاری کنید.
        </p>
      </div>
      <div class="ceo-message-signature">
        <div class="ceo-sig-avatar">
          <img src="{{image1}}" alt="مدیرعامل مکسا">
        </div>
        <div class="ceo-sig-info">
          <h4>مدیرعامل مؤسسه مکسا</h4>
          <p>مؤسسه نیکوکاری کنترل سرطان ایرانیان</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== BIOGRAPHY ===== -->
<div class="ceo-section ceo-fade-up" id="ceo-bio">
  <div class="ceo-section-header">
    <div class="ceo-section-tag">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      بیوگرافی
    </div>
    <h2 class="ceo-section-title">سوابق و تجربیات</h2>
    <p class="ceo-section-subtitle">نگاهی به مسیر حرفه‌ای و دستاوردهای مدیرعامل مکسا</p>
  </div>

  <div class="ceo-bio-grid">
    <!-- Card 1: Education -->
    <div class="ceo-bio-card">
      <div class="ceo-bio-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
      </div>
      <h3>سوابق تحصیلی</h3>
      <ul>
        <li>فوق‌لیسا مدیریت بازرگانی — دانشگاه تهران</li>
        <li>کارشناسی ارشد مدیریت بهداشت — دانشگاه علوم پزشکی</li>
        <li>دوره‌های تخصصی مدیریت سازمان‌های مردم‌نهاد</li>
        <li>گواینامه مدیریت پروژه‌های بین‌المللی</li>
      </ul>
    </div>

    <!-- Card 2: Experience -->
    <div class="ceo-bio-card">
      <div class="ceo-bio-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      </div>
      <h3>سوابق مدیریتی</h3>
      <ul>
        <li>مدیرعامل مؤسسه نیکوکاری مکسا از سال ۱۳۹۵</li>
        <li>مشاور ارشد سازمان‌های مردم‌نهاد حوزه سلامت</li>
        <li>مدیر پروژه‌های بین‌المللی مراقبت تسکینی</li>
        <>هماهنگ‌کننده شبکه ملی مراکز سرطان</li>
      </ul>
    </div>

    <!-- Card 3: Achievements -->
    <div class="ceo-bio-card">
      <div class="ceo-bio-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
      </div>
      <h3>دستاوردها</h3>
      <ul>
        <li>تأسیس و راه‌اندازی ۷ شعبه مکسا در سراسر کشور</li>
        <li>طراحی و اجرای بیش از ۵۰ کارگاه آموزشی تخصصی</li>
        <li>کسب تندیس سازمان مردم‌نهاد برتر کشور</li>
        <li>همکاری با سازمان بهداشت جهانی در حوزه مراقبت تسکینی</li>
      </ul>
    </div>

    <!-- Card 4: Skills -->
    <div class="ceo-bio-card">
      <div class="ceo-bio-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
      <h3>حوزه‌های تخصصی</h3>
      <ul>
        <li>مدیریت راهبردی سازمان‌های غیرانتفاعی</li>
        <li>توسعه و مدیریت شبکه‌های ملی سلامت</li>
        <li>جذب منابع مالی و مشارکت‌های مردمی</li>
        <li>ایجاد ارتباط با نهادهای بین‌المللی</li>
      </ul>
    </div>
  </div>
</div>

<!-- ===== VISION ===== -->
<div class="ceo-section ceo-fade-up">
  <div class="ceo-vision">
    <div class="ceo-vision-inner">
      <div class="ceo-vision-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      </div>
      <div>
        <h3>چشم‌انداز و ارزش‌های ما</h3>
        <p>
          ما در مکسا باور داریم که مراقبت‌های حمایتی و تسکینی حق همه بیماران است. چشم‌انداز ما ایجاد شبکه‌ای جامع از مراقبت‌های انسان‌محور در سراسر ایران است؛ جایی که هیچ بیماری به دلیل محدودیت‌های مالی یا جغرافیایی از دسترسی به مراقبت‌های باکیفیت محروم نماند.
        </p>
        <div class="ceo-vision-tags">
          <span class="ceo-vision-tag">مراقبت انسان‌محور</span>
          <span class="ceo-vision-tag">دسترسی عادلانه</span>
          <span class="ceo-vision-tag">آموزش و پژوهش</span>
          <span class="ceo-vision-tag">همکاری بین‌رشته‌ای</span>
          <span class="ceo-vision-tag">ارتقای کیفیت زندگی</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== TIMELINE ===== -->
<div class="ceo-section ceo-fade-up">
  <div class="ceo-section-header">
    <div class="ceo-section-tag">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      مسیر پیشرفت
    </div>
    <h2 class="ceo-section-title">گاهشمار فعالیت‌ها</h2>
    <p class="ceo-section-subtitle">نقاط عطف مهم در مسیر مدیریت و توسعه مکسا</p>
  </div>

  <div class="ceo-message-card">
    <div class="ceo-timeline">
      <div class="ceo-timeline-item">
        <div class="ceo-timeline-dot"></div>
        <div class="ceo-timeline-year">۱۳۹۵</div>
        <h4>تأسیس مؤسسه مکسا</h4>
        <p>تأسیس مؤسسه نیکوکاری کنترل سرطان ایرانیان با هدف ارائه مراقبت‌های جامع حمایتی و تسکینی</p>
      </div>
      <div class="ceo-timeline-item">
        <div class="ceo-timeline-dot"></div>
        <div class="ceo-timeline-year">۱۳۹۷</div>
        <h4>افتتاح شعبه تهران</h4>
        <p>راه‌اندازی اولین مرکز تخصصی مکسا در تهران با تیم چندرشته‌ای درمان</p>
      </div>
      <div class="ceo-timeline-item">
        <div class="ceo-timeline-dot"></div>
        <div class="ceo-timeline-year">۱۳۹۹</div>
        <h4>گسترش شعب</h4>
        <p>افتتاح شعبه‌های اصفهان، مشهد و قم و آغاز خدمات‌رسانی در سطح ملی</p>
      </div>
      <div class="ceo-timeline-item">
        <div class="ceo-timeline-dot"></div>
        <div class="ceo-timeline-year">۱۴۰۱</div>
        <h4>همکاری بین‌المللی</h4>
        <p>برقراری همکاری با سازمان بهداشت جهانی و مراکز تخصصی مراقبت تسکینی بین‌المللی</p>
      </div>
      <div class="ceo-timeline-item">
        <div class="ceo-timeline-dot"></div>
        <div class="ceo-timeline-year">۱۴۰۳</div>
        <h4>توسعه و بلوغ سازمانی</h4>
        <p>افتتاح ۷ شعبه فعال در سراسر کشور، راه‌اندازی مرکز آموزش مهارتی و مرکز رویش استعدادها</p>
      </div>
    </div>
  </div>
</div>

<!-- ===== CONTACT ===== -->
<div class="ceo-section ceo-fade-up">
  <div class="ceo-section-header">
    <div class="ceo-section-tag">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      ارتباط با دفتر مدیرعامل
    </div>
    <h2 class="ceo-section-title">دفتر مدیرعامل</h2>
    <p class="ceo-section-subtitle">برای ارتباط با دفتر مدیرعامل مکسا از اطلاعات زیر استفاده کنید</p>
  </div>

  <div class="ceo-contact-grid">
    <div class="ceo-contact-card">
      <h3>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        اطلاعات تماس
      </h3>
      <ul class="ceo-contact-list">
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <span><strong>تلفن:</strong> ۰۲۱-۹۱۰۹۲۰۳۰</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <span><strong>ایمیل:</strong> info@macsa-charity.ir</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <span><strong>آدرس:</strong> تهران، خیابان ولیعصر، مؤسسه مکسا</span>
        </li>
      </ul>
    </div>

    <div class="ceo-contact-card">
      <h3>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        ساعات پاسخگویی
      </h3>
      <ul class="ceo-contact-list">
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span><strong>شنبه تا چهارشنبه:</strong> ۸ صبح الی ۱۷</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span><strong>پنجشنبه:</strong> ۸ صبح الی ۱۳</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span><strong>جمعه و ایام تعطیل:</strong> تعطیل</span>
        </li>
      </ul>
    </div>
  </div>
</div>

<!-- ===== SCROLL ANIMATION ===== -->
<script>
(function() {
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('ceo-visible');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.ceo-fade-up').forEach(function(el) {
    observer.observe(el);
  });
})();
</script>

</section>
