import base64
import os
import subprocess

def get_base64_file(path):
    with open(path, 'rb') as f:
        return base64.b64encode(f.read()).decode('utf-8')

logo_b64 = get_base64_file('D:/Maxa-charity/public_html/dashboard/components/header/images/1.png')
vazir_regular_b64 = get_base64_file('D:/Maxa-charity/public_html/webfont/Vazirmatn-Regular.woff2')
vazir_bold_b64 = get_base64_file('D:/Maxa-charity/public_html/webfont/Vazirmatn-Bold.woff2')

html_content = f"""<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>راهنمای جامع ابعاد و استانداردهای طراحی بنرهای اسلایدر هدر مکسا</title>
  <style>
    @font-face {{
      font-family: 'Vazirmatn';
      src: url('data:font/woff2;base64,{vazir_regular_b64}') format('woff2');
      font-weight: 400;
      font-style: normal;
    }}
    @font-face {{
      font-family: 'Vazirmatn';
      src: url('data:font/woff2;base64,{vazir_bold_b64}') format('woff2');
      font-weight: 700;
      font-style: normal;
    }}

    @page {{
      size: A4 portrait;
      margin: 10mm 12mm 12mm 12mm;
      @bottom-right {{
        content: "صفحه " counter(page);
        font-family: 'Vazirmatn', sans-serif;
        font-size: 8.5pt;
        color: #94a3b8;
      }}
    }}

    * {{
      box-sizing: border-box;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }}

    body {{
      font-family: 'Vazirmatn', 'Segoe UI', Tahoma, sans-serif;
      margin: 0;
      padding: 0;
      color: #1e293b;
      background: #ffffff;
      font-size: 9.5pt;
      line-height: 1.6;
    }}

    .page {{
      padding: 0;
      position: relative;
    }}

    .page-break {{
      page-break-before: always;
      padding-top: 4mm;
    }}

    .header-bar {{
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #008f8a;
      padding-bottom: 8px;
      margin-bottom: 12px;
    }}

    .header-left {{
      text-align: left;
      direction: ltr;
    }}

    .header-left .doc-id {{
      font-size: 8pt;
      font-weight: 700;
      color: #008f8a;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }}

    .header-left .doc-date {{
      font-size: 7.5pt;
      color: #64748b;
    }}

    .header-brand {{
      display: flex;
      align-items: center;
      gap: 12px;
    }}

    .header-logo {{
      height: 38px;
      width: auto;
      object-fit: contain;
    }}

    .badge-status {{
      background: #ecfdf5;
      color: #059669;
      border: 1px solid #a7f3d0;
      padding: 2px 8px;
      border-radius: 999px;
      font-size: 7.5pt;
      font-weight: 700;
    }}

    .doc-title-block {{
      background: linear-gradient(135deg, #05a8a2 0%, #008f8a 60%, #00605d 100%);
      color: #ffffff;
      padding: 14px 18px;
      border-radius: 8px;
      margin-bottom: 14px;
      position: relative;
      overflow: hidden;
    }}

    .doc-title-block h1 {{
      margin: 0 0 4px 0;
      font-size: 15pt;
      font-weight: 700;
      line-height: 1.35;
    }}

    .doc-title-block .subtitle {{
      margin: 0;
      font-size: 9pt;
      opacity: 0.92;
      direction: ltr;
      text-align: right;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }}

    .meta-pills {{
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 8px;
      font-size: 8pt;
    }}

    .meta-pill {{
      background: rgba(255, 255, 255, 0.16);
      border: 1px solid rgba(255, 255, 255, 0.25);
      padding: 2px 8px;
      border-radius: 4px;
      color: #ffffff;
    }}

    .section-title {{
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 11pt;
      font-weight: 700;
      color: #00605d;
      margin: 14px 0 8px 0;
      border-right: 4px solid #faa61a;
      padding-right: 8px;
    }}

    .section-title .en-sub {{
      font-size: 8.5pt;
      color: #64748b;
      font-weight: 400;
      margin-right: auto;
      direction: ltr;
    }}

    p {{
      margin: 0 0 8px 0;
      text-align: justify;
    }}

    /* Executive Callout */
    .callout-box {{
      background: #fffbeb;
      border: 1.5px solid #fef3c7;
      border-right: 4px solid #faa61a;
      padding: 9px 12px;
      border-radius: 6px;
      margin-bottom: 12px;
      font-size: 8.8pt;
    }}

    .callout-box strong {{
      color: #b45309;
    }}

    /* Table Styles */
    table.spec-table {{
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0 14px 0;
      font-size: 8.2pt;
      background: #ffffff;
      border-radius: 6px;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      border: 1px solid #e2e8f0;
    }}

    table.spec-table th {{
      background: #0f172a;
      color: #ffffff;
      padding: 7px 9px;
      font-weight: 700;
      text-align: center;
      border: 1px solid #334155;
    }}

    table.spec-table td {{
      padding: 6.5px 8px;
      border: 1px solid #e2e8f0;
      text-align: center;
      vertical-align: middle;
    }}

    table.spec-table tr:nth-child(even) {{
      background: #f8fafc;
    }}

    table.spec-table tr.highlight-row {{
      background: #f0fdfa;
      font-weight: 700;
    }}

    table.spec-table td.target-size {{
      color: #008f8a;
      font-weight: 700;
      direction: ltr;
      font-family: monospace;
      font-size: 8.8pt;
    }}

    .tag {{
      display: inline-block;
      padding: 1px 6px;
      border-radius: 4px;
      font-size: 7.2pt;
      font-weight: 700;
    }}
    .tag-primary {{ background: #e0f2fe; color: #0369a1; }}
    .tag-amber {{ background: #fef3c7; color: #92400e; }}
    .tag-green {{ background: #dcfce7; color: #15803d; }}

    /* Diagram Styles */
    .diagram-container {{
      background: #0f172a;
      border: 2px solid #334155;
      border-radius: 8px;
      padding: 10px;
      color: #ffffff;
      margin: 10px 0 14px 0;
      position: relative;
    }}

    .diagram-header {{
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 8pt;
      color: #94a3b8;
      margin-bottom: 6px;
      direction: ltr;
    }}

    .mockup-canvas {{
      width: 100%;
      height: 140px;
      border: 1.5px dashed #475569;
      border-radius: 6px;
      position: relative;
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
      overflow: hidden;
      display: flex;
    }}

    .zone-navbar {{
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 26px;
      background: rgba(250, 166, 26, 0.25);
      border-bottom: 1px dashed #faa61a;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 12px;
      font-size: 7pt;
      color: #fde68a;
      z-index: 5;
    }}

    .zone-focal {{
      width: 55%;
      height: 100%;
      background: rgba(16, 185, 129, 0.12);
      border-left: 2px dashed #10b981;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 28px 12px 14px 12px;
      text-align: center;
      position: relative;
    }}

    .zone-focal .zone-title {{
      color: #34d399;
      font-weight: 700;
      font-size: 8.5pt;
      margin-bottom: 2px;
    }}

    .zone-focal .zone-desc {{
      color: #94a3b8;
      font-size: 7pt;
      line-height: 1.35;
    }}

    .zone-focal-circle {{
      width: 60px;
      height: 60px;
      border: 1.5px solid #10b981;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: rgba(16, 185, 129, 0.2);
      color: #a7f3d0;
      font-size: 7pt;
      margin-top: 4px;
    }}

    .zone-text {{
      width: 45%;
      height: 100%;
      background: rgba(239, 68, 68, 0.12);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 28px 12px 14px 12px;
      text-align: center;
      position: relative;
    }}

    .zone-text .zone-title {{
      color: #fca5a5;
      font-weight: 700;
      font-size: 8.5pt;
      margin-bottom: 2px;
    }}

    .zone-text .mock-text-line {{
      height: 6px;
      border-radius: 3px;
      background: rgba(255, 255, 255, 0.7);
      margin: 3px 0;
    }}

    .zone-text .mock-btn {{
      margin-top: 5px;
      padding: 2px 10px;
      background: #faa61a;
      color: #111;
      font-size: 6.5pt;
      font-weight: 700;
      border-radius: 4px;
    }}

    .zone-dots {{
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 16px;
      background: rgba(0, 143, 138, 0.35);
      border-top: 1px dashed #008f8a;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      font-size: 6.5pt;
      color: #5eead4;
      z-index: 5;
    }}

    .mock-dot {{
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: #5eead4;
      display: inline-block;
    }}

    /* Rules Grid */
    .rules-grid {{
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin: 8px 0 12px 0;
    }}

    .rule-card {{
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-top: 3px solid #008f8a;
      border-radius: 6px;
      padding: 8px 10px;
      font-size: 8.2pt;
    }}

    .rule-card.warning-card {{
      border-top-color: #ef4444;
      background: #fffafa;
    }}

    .rule-card.gold-card {{
      border-top-color: #faa61a;
      background: #fffdf5;
    }}

    .rule-card h4 {{
      margin: 0 0 4px 0;
      font-size: 8.8pt;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 6px;
    }}

    .rule-card p {{
      margin: 0;
      color: #475569;
      font-size: 8pt;
      line-height: 1.45;
    }}

    /* Checklist */
    .checklist {{
      background: #f0fdfa;
      border: 1.5px solid #ccfbf1;
      border-radius: 6px;
      padding: 10px 14px;
      margin-top: 8px;
    }}

    .checklist-item {{
      display: flex;
      align-items: flex-start;
      gap: 8px;
      margin-bottom: 6px;
      font-size: 8.2pt;
      color: #134e4a;
    }}

    .checklist-item:last-child {{
      margin-bottom: 0;
    }}

    .check-box {{
      width: 13px;
      height: 13px;
      border: 1.5px solid #008f8a;
      border-radius: 3px;
      display: inline-block;
      margin-top: 2px;
      flex-shrink: 0;
      background: #ffffff;
    }}

    .footer-note {{
      border-top: 1px solid #cbd5e1;
      padding-top: 6px;
      margin-top: 10px;
      font-size: 7.5pt;
      color: #64748b;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }}
  </style>
</head>
<body>

  <!-- ==================== PAGE 1 ==================== -->
  <div class="page">
    
    <!-- Header Bar -->
    <div class="header-bar">
      <div class="header-brand">
        <img class="header-logo" src="data:image/png;base64,{logo_b64}" alt="MACSA Logo">
        <div>
          <span class="badge-status">دستورالعمل رسمی نسخه ۲.۰</span>
        </div>
      </div>
      <div class="header-left">
        <div class="doc-id">MACSA-SPEC-UI-2026-HERO</div>
        <div class="doc-date">تاریخ تصویب: آبان ۱۴۰۵ / نوامبر ۲۰۲۶</div>
      </div>
    </div>

    <!-- Title Block -->
    <div class="doc-title-block">
      <h1>راهنمای ابعاد و استانداردهای طراحی بنر اسلایدر هدر (Hero Section)</h1>
      <p class="subtitle">Hero Slider Technical Resolution & Art Direction Specification Guide</p>
      <div class="meta-pills">
        <span class="meta-pill">واحد مخاطب: تیم گرافیک، عکاسی و رابط کاربری (UI/UX)</span>
        <span class="meta-pill">ارتفاع نمایشی جدید: <strong>420px</strong> (فوق‌عریض / کامپکت)</span>
        <span class="meta-pill">هدف: نمایش کامل نوار اهدا و کارت‌های خدمات در نگاه اول (Above the Fold)</span>
      </div>
    </div>

    <!-- Section 1: Executive Summary -->
    <div class="section-title">
      <span>۱. مقدمه و منطق فنی تغییر ارتفاع هدر</span>
      <span class="en-sub">1. Executive Overview & Technical Rationale</span>
    </div>
    
    <div class="callout-box">
      <strong>هدف استراتژیک تغییر ابعاد:</strong>
      در نگارش قبلی، ارتفاع هدر ۶۴۰ پیکسل بود که در لپ‌تاپ‌های معمول (۱۳۶۶×۷۶۸ و نمایشگرهای ۱۰۸۰p با مقیاس ۱۲۵٪)، بخش نوار سبز اهدای آنلاین و ۴ باکس اقدام اصلی (بسته‌های نیکوکاری، روش‌های حمایت، تشکیل پرونده مجازی، استند و کارت تسلیت) به طور کامل زیر خط اسکرول (Below the Fold) قرار می‌گرفت. در سیستم جدید، ارتفاع هدر به <strong>۴۲۰ پیکسل</strong> کاهش یافته تا کلیه المان‌های تبدیل کاربر بلافاصله <strong>در نگاه اول بدون نیاز به اسکرول</strong> دیده شوند.
    </div>

    <!-- Section 2: Master Table -->
    <div class="section-title">
      <span>۲. جدول جامع مشخصات فنی، رزولوشن‌ها و ابعاد آرت‌بورد</span>
      <span class="en-sub">2. Master Resolution & Artboard Matrix</span>
    </div>

    <table class="spec-table">
      <thead>
        <tr>
          <th>دستگاه و Breakpoint</th>
          <th>نسبت ابعاد</th>
          <th>اندازه نمایش وب (CSS)</th>
          <th>سایز خروجی استاندارد (1x)</th>
          <th>سایز آرت‌بورد فیگما / فتوشاپ (2x Retina)</th>
          <th>فرمت اصلی</th>
          <th>سقف حجم</th>
        </tr>
      </thead>
      <tbody>
        <tr class="highlight-row">
          <td>
            <strong>دسکتاپ اصلی (Master)</strong><br>
            <span class="tag tag-primary">مرجع طراحی</span>
          </td>
          <td>~4.5 : 1<br>(فوق عریض)</td>
          <td class="target-size">1920 × 420 px</td>
          <td class="target-size">1920 × 420 px</td>
          <td class="target-size" style="color:#b45309; background:#fef3c7;"><strong>3840 × 840 px</strong></td>
          <td>WebP (Lossy 85%)</td>
          <td>&lt; 220 KB</td>
        </tr>
        <tr>
          <td>
            <strong>لپ‌تاپ و صفحات متوسط</strong><br>
            <span class="tag tag-amber">Auto Scale</span>
          </td>
          <td>~3.4 : 1</td>
          <td class="target-size">1440 × 420 px</td>
          <td class="target-size">1440 × 420 px</td>
          <td class="target-size">2880 × 840 px</td>
          <td>WebP / JPG</td>
          <td>&lt; 180 KB</td>
        </tr>
        <tr>
          <td>
            <strong>تبلت (Tablet)</strong><br>
            <span class="tag tag-green">Responsive</span>
          </td>
          <td>~2.4 : 1</td>
          <td class="target-size">960 × 400 px</td>
          <td class="target-size">960 × 400 px</td>
          <td class="target-size">1920 × 800 px</td>
          <td>WebP / JPG</td>
          <td>&lt; 150 KB</td>
        </tr>
        <tr>
          <td>
            <strong>موبایل (Mobile)</strong><br>
            <span class="tag tag-primary">Center Focus</span>
          </td>
          <td>~1.1 : 1 تا 1.9 : 1</td>
          <td class="target-size">380 × 380 px تا 420 × 380 px</td>
          <td class="target-size">420 × 380 px</td>
          <td class="target-size" style="color:#b45309;"><strong>840 × 760 px</strong></td>
          <td>WebP / JPG</td>
          <td>&lt; 110 KB</td>
        </tr>
      </tbody>
    </table>

    <!-- Section 3: Safe Zone Diagram -->
    <div class="section-title">
      <span>۳. معماری لایه‌ها و دیاگرام حریم امن بصری (Safe Zone Map)</span>
      <span class="en-sub">3. Canvas Zoning & Overlay Architecture</span>
    </div>

    <div class="diagram-container">
      <div class="diagram-header">
        <span>MASTER ARTBOARD: 3840 × 840 PX (EXPORT 2X) &bull; DISPLAY: 1920 × 420 PX</span>
        <span>RTL COMPOSITION GUIDE</span>
      </div>

      <div class="mockup-canvas">
        <!-- Top Navbar Area -->
        <div class="zone-navbar">
          <span>حریم نوار ناوبری بالا (ارتفاع ۷۸ پیکسل) — نیمه‌شفاف زرد/تیره — از قرار دادن سوژه اصلی یا نوشته در این نوار پرهیز شود</span>
          <span style="direction:ltr;">TOP BAR: 78PX</span>
        </div>

        <!-- Left 55%: Visual Focal Safe Zone -->
        <div class="zone-focal">
          <div class="zone-title">حریم امن فوکوس بصری و سوژه (۵۵٪ سمت چپ)</div>
          <div class="zone-desc">محل قرارگیری چهره بیماران، کادر درمان، تصویرسازی، ابزارها و سوژه اصلی عکاسی</div>
          <div class="zone-focal-circle">Focal Point<br>سوژه اصلی</div>
        </div>

        <!-- Right 45%: Overlay Text Area -->
        <div class="zone-text">
          <div class="zone-title">حریم پوشش متن و دکمه (۴۵٪ سمت راست)</div>
          <div style="width: 80%; margin: 3px auto;">
            <div class="mock-text-line" style="width: 90%;"></div>
            <div class="mock-text-line" style="width: 70%;"></div>
            <div class="mock-text-line" style="width: 85%; opacity: 0.5;"></div>
            <div class="mock-btn">دکمه روش‌های حمایت ←</div>
          </div>
          <div style="font-size: 6.5pt; color: #fca5a5; margin-top: 4px;">پس‌زمینه باید تیره، خلوت و یکدست باشد</div>
        </div>

        <!-- Bottom Dots Area -->
        <div class="zone-dots">
          <span>محل دات‌های ناوبری اسلایدر و اتصال به نوار سبز اهدای آنلاین</span>
          <span class="mock-dot"></span>
          <span class="mock-dot" style="width:14px; border-radius:4px; background:#faa61a;"></span>
          <span class="mock-dot"></span>
        </div>
      </div>
    </div>

    <div class="footer-note">
      <span>مؤسسه نیکوکاری کنترل سرطان ایرانیان (مکسا) &bull; اداره کل فناوری اطلاعات و تحول دیجیتال</span>
      <span>صفحه ۱ از ۲</span>
    </div>

  </div>

  <!-- ==================== PAGE 2 ==================== -->
  <div class="page page-break">

    <!-- Header Bar -->
    <div class="header-bar">
      <div class="header-brand">
        <img class="header-logo" src="data:image/png;base64,{logo_b64}" alt="MACSA Logo">
        <div>
          <span class="badge-status">دستورالعمل رسمی نسخه ۲.۰</span>
        </div>
      </div>
      <div class="header-left">
        <div class="doc-id">MACSA-SPEC-UI-2026-HERO</div>
        <div class="doc-date">صفحه دوم: استانداردهای طراحی و خروجی</div>
      </div>
    </div>

    <!-- Section 4: Golden Rules -->
    <div class="section-title">
      <span>۴. اصول و قوانین حیاتی طراحی برای تیم گرافیک (Art Direction Rules)</span>
      <span class="en-sub">4. Art Direction & Composition Guidelines</span>
    </div>

    <div class="rules-grid">
      <div class="rule-card">
        <h4><span>&#10003;</span> قانون طلایی نیمه چپ (Golden Left-Half Rule)</h4>
        <p>به دلیل ساختار راست‌چین (RTL) سایت مکسا، عنوان و توضیحات و دکمه اسلاید در سمت راست قرار دارند. بنابراین <strong>سوژه اصلی، چهره انسان یا دستاوردهای تصویری حتماً باید در ۵۵٪ سمت چپ کادر</strong> متمرکز باشند.</p>
      </div>

      <div class="rule-card warning-card">
        <h4><span>&#9888;</span> عدم اعمال لایه تیره یا گرادیانت دستی</h4>
        <p>کد فرانت‌اِند وب‌سایت به طور اتوماتیک یک لایه گرادیانت ملایم تیره جهت خوانایی متن بر روی اسلایدها می‌اندازد. <strong>از تیره کردن دستی و کدر نمودن کل عکس در فتوشاپ پرهیز کنید</strong> تا شفافیت و کنتراست تصویر حفظ شود.</p>
      </div>

      <div class="rule-card gold-card">
        <h4><span>&#9679;</span> آرامش و یکدستی پس‌زمینه در سمت راست</h4>
        <p>نوشته‌های عنوان و پاراگراف‌ها با رنگ سفید نمایش داده می‌شوند. در نتیجه نیمه سمت راست تصویر نباید حاوی لکه‌های سفید خالص، المان‌های شلوغ یا الگوهای نوری شدید باشد که خوانایی متن را کاهش دهد.</p>
      </div>

      <div class="rule-card">
        <h4><span>&#8644;</span> رفتار ریسپانسیو و کراپ در موبایل (Center Crop)</h4>
        <p>در موبایل، تصویر با خاصیت <code>background-size: cover; background-position: center;</code> نمایش داده می‌شود. به همین دلیل لبه‌های بیرونی چپ و راست کراپ می‌شوند؛ از قرار دادن جزئیات حیاتی در ۱۰٪ لبه‌های کادر دسکتاپ بپرهیزید.</p>
      </div>
    </div>

    <!-- Section 5: Technical Specifications -->
    <div class="section-title">
      <span>۵. استانداردهای فنی خروجی فایل و نام‌گذاری (Export Specs)</span>
      <span class="en-sub">5. Export Specs & Technical Asset Delivery</span>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
      <div style="background: #f1f5f9; border-radius: 6px; padding: 8px 10px; font-size: 8.2pt;">
        <strong style="color: #0f172a; display: block; margin-bottom: 4px;">مشخصات فنی فرمت و رنگ:</strong>
        <ul style="margin: 0; padding-right: 18px; line-height: 1.5; color: #334155;">
          <li><strong>فرمت ارجح:</strong> <code>WebP</code> (فشرده‌سازی با کیفیت ۸۵٪ تا ۸۸٪)</li>
          <li><strong>فرمت پشتیبان:</strong> <code>JPEG Progressive</code> (کیفیت ۸۰٪ تا ۸۵٪)</li>
          <li><strong>فضای رنگی:</strong> الزماً <code>sRGB</code> (از CMYK یا AdobeRGB خودداری فرمایید)</li>
          <li><strong>سقف حجم:</strong> کمتر از <strong>۲۰۰ کیلوبایت</strong> برای هر اسلاید جهت بهینه‌سازی سرعت LCP در اینترنت داخلی ایران</li>
        </ul>
      </div>

      <div style="background: #f1f5f9; border-radius: 6px; padding: 8px 10px; font-size: 8.2pt;">
        <strong style="color: #0f172a; display: block; margin-bottom: 4px;">پالت رنگی سازمانی مکسا جهت هماهنگی بنرها:</strong>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; margin-top: 4px;">
          <div style="background:#faa61a; color:#111; padding: 3px 6px; border-radius: 4px; font-weight:700; font-size:7.5pt; text-align:center;">
            زرد سازمانی: #FAA61A
          </div>
          <div style="background:#e99508; color:#fff; padding: 3px 6px; border-radius: 4px; font-weight:700; font-size:7.5pt; text-align:center;">
            نارنجی تیره: #E99508
          </div>
          <div style="background:#008f8a; color:#fff; padding: 3px 6px; border-radius: 4px; font-weight:700; font-size:7.5pt; text-align:center;">
            سبز کله‌غازی: #008F8A
          </div>
          <div style="background:#00605d; color:#fff; padding: 3px 6px; border-radius: 4px; font-weight:700; font-size:7.5pt; text-align:center;">
            سبز تیره: #00605D
          </div>
        </div>
        <p style="margin-top: 5px; font-size: 7.5pt; color: #64748b;">قالب نام‌گذاری پیشنهادی: <code>hero_slide_[عنوان_موضوع]_[سال-ماه].webp</code></p>
      </div>
    </div>

    <!-- Section 6: Checklist -->
    <div class="section-title">
      <span>۶. چک‌لیست تأیید و کنترل کیفی نهایی طراح (Pre-Flight Checklist)</span>
      <span class="en-sub">6. Quality Control & Handover Checklist</span>
    </div>

    <div class="checklist">
      <div class="checklist-item">
        <span class="check-box"></span>
        <span>ابعاد آرت‌بورد در فیگما یا فتوشاپ دقیقاً برابر با <strong>۳۸۴۰ در ۸۴۰ پیکسل</strong> (یا ۱۹۲۰×۴۲۰ برای حالت 1x) تنظیم شده است.</span>
      </div>
      <div class="checklist-item">
        <span class="check-box"></span>
        <span>سوژه اصلی، نگاه چشم‌ها و المان‌های مهم کامپوزیشن در <strong>۵۵٪ سمت چپ بنر</strong> قرار گرفته‌اند.</span>
      </div>
      <div class="checklist-item">
        <span class="check-box"></span>
        <span>سمت راست تصویر (محل قرارگیری عنوان، توضیحات و دکمه) آرام و یکدست بوده و متن سفید به راحتی خوانده می‌شود.</span>
      </div>
      <div class="checklist-item">
        <span class="check-box"></span>
        <span>در فاصله ۷۸ پیکسل از بالای بنر و ۳۰ پیکسل از پایین بنر، هیچ نوشته یا المان حیاتی قرار نگرفته است.</span>
      </div>
      <div class="checklist-item">
        <span class="check-box"></span>
        <span>تصویر با فضای رنگی <strong>sRGB</strong> استخراج شده و رنگ‌ها بر روی مرورگرهای وب کدر نمی‌شوند.</span>
      </div>
      <div class="checklist-item">
        <span class="check-box"></span>
        <span>حجم خروجی نهایی ترجیحاً با فرمت WebP کمتر از ۲۰۰ کیلوبایت کنترل و بهینه‌سازی شده است.</span>
      </div>
    </div>

    <!-- Footer Note -->
    <div class="footer-note" style="margin-top: 14px;">
      <span>سامانه جامع خدمات حمایتی و تسکینی مکسا &bull; طراحی و بهینه‌سازی فرانت‌اِند</span>
      <span>MACSA Web Standards &bull; Version 2.0 (November 2026) &bull; صفحه ۲ از ۲</span>
    </div>

  </div>

</body>
</html>
"""

html_path = "D:/Maxa-charity/docs/MACSA_Hero_Banner_Design_Guide.html"
with open(html_path, "w", encoding="utf-8") as f:
    f.write(html_content)

print(f"HTML guide generated at: {html_path} ({len(html_content)} bytes)")
