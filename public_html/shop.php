<?php
declare(strict_types=1);

require_once __DIR__ . '/core/site-locale.php';
require_once __DIR__ . '/core/component-renderer.php';
require_once __DIR__ . '/core/shop-db.php';

// Format price in Tomans with Persian numerals
if (!function_exists('shop_format_toman')) {
    function shop_format_toman(float|int|string $price): string {
        $num = (int)$price;
        $formatted = number_format($num);
        return strtr($formatted, [
            '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',','=>'٬'
        ]);
    }
}

// Persian digits converter
if (!function_exists('fa_digits')) {
    function fa_digits($s): string {
        return strtr((string)$s, [
            '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹','.'=>'٫'
        ]);
    }
}

// Establish database connection if not provided
if (!isset($pdo) || !($pdo instanceof PDO)) {
    try {
        $dbPath = __DIR__ . '/core/database.php';
        if (file_exists($dbPath)) {
            require_once $dbPath;
        }
    } catch (Throwable $e) {}
}

$categories = [];
$products = [];

if (isset($pdo) && ($pdo instanceof PDO)) {
    try {
        shop_ensure_schema($pdo);
        $categories = shop_get_categories($pdo);
        $products = shop_get_products($pdo);
    } catch (Throwable $e) {
        $categories = [];
        $products = [];
    }
}

$pageTitle = site_text('فروشگاه خیریه | مکسا');
render_dashboard_component('header', get_defined_vars());
?>

<div class="shop-page-wrapper">
  <style>
    :root {
      --shop-primary: #faa61a;
      --shop-primary-hover: #e99508;
      --shop-primary-subtle: #fef3c7;
      --shop-dark: #0f172a;
      --shop-slate-800: #1e293b;
      --shop-slate-600: #475569;
      --shop-slate-500: #64748b;
      --shop-slate-200: #e2e8f0;
      --shop-slate-100: #f1f5f9;
      --shop-slate-50: #f8fafc;
      --shop-green: #10b981;
      --shop-green-subtle: #d1fae5;
      --shop-red: #ef4444;
      --shop-red-subtle: #fee2e2;
      --shop-radius-lg: 20px;
      --shop-radius-md: 14px;
      --shop-radius-sm: 8px;
      --shop-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
      --shop-shadow-hover: 0 20px 30px -10px rgba(15, 23, 42, 0.12), 0 10px 10px -5px rgba(15, 23, 42, 0.04);
    }

    .shop-page-wrapper {
      direction: var(--site-direction, rtl);
      font-family: 'Vazirmatn', sans-serif;
      color: var(--shop-dark);
      background: #fafaf9;
      min-height: 80vh;
      padding-bottom: 80px;
      position: relative;
    }

    .shop-container {
      width: min(var(--cta-container, 1400px), 100%);
      margin-inline: auto;
      padding-inline: 20px;
      box-sizing: border-box;
    }

    /* ===== HERO BANNER ===== */
    .shop-hero {
      position: relative;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
      border-radius: var(--shop-radius-lg);
      padding: 56px 40px;
      margin-top: 24px;
      margin-bottom: 40px;
      overflow: hidden;
      color: #ffffff;
      box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.25);
    }

    .shop-hero::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -20%;
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, rgba(250, 166, 26, 0.18) 0%, rgba(250, 166, 26, 0) 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .shop-hero::after {
      content: '';
      position: absolute;
      bottom: -60%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(250, 166, 26, 0.12) 0%, rgba(250, 166, 26, 0) 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .shop-hero-content {
      position: relative;
      z-index: 2;
      max-width: 820px;
    }

    .shop-hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(250, 166, 26, 0.2);
      border: 1px solid rgba(250, 166, 26, 0.4);
      color: #fbbf24;
      padding: 6px 16px;
      border-radius: 999px;
      font-size: 13.5px;
      font-weight: 700;
      margin-bottom: 20px;
      backdrop-filter: blur(8px);
    }

    .shop-hero-title {
      font-size: clamp(26px, 4vw, 42px);
      font-weight: 900;
      line-height: 1.35;
      margin: 0 0 16px;
      color: #ffffff;
    }

    .shop-hero-title span {
      color: var(--shop-primary);
    }

    .shop-hero-text {
      font-size: clamp(15px, 1.8vw, 17px);
      line-height: 1.85;
      color: #cbd5e1;
      margin: 0 0 28px;
    }

    .shop-hero-features {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
    }

    .shop-hero-feat-item {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 12px;
      padding: 8px 16px;
      font-size: 13.5px;
      color: #f1f5f9;
      backdrop-filter: blur(4px);
    }

    .shop-hero-feat-item svg {
      color: var(--shop-primary);
      flex-shrink: 0;
    }

    /* ===== FILTER & CONTROLS BAR ===== */
    .shop-controls {
      background: #ffffff;
      border: 1px solid var(--shop-slate-200);
      border-radius: var(--shop-radius-md);
      padding: 16px 20px;
      margin-bottom: 32px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .shop-cat-pills {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 8px;
    }

    .shop-cat-btn {
      border: 1px solid var(--shop-slate-200);
      background: var(--shop-slate-50);
      color: var(--shop-slate-600);
      font-family: inherit;
      font-size: 13.5px;
      font-weight: 700;
      padding: 8px 16px;
      border-radius: 999px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .shop-cat-btn:hover {
      background: #ffffff;
      border-color: #cbd5e1;
      color: var(--shop-dark);
    }

    .shop-cat-btn.active {
      background: var(--shop-primary);
      border-color: var(--shop-primary);
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(250, 166, 26, 0.3);
    }

    .shop-cat-count {
      font-size: 11px;
      background: rgba(0, 0, 0, 0.08);
      border-radius: 999px;
      padding: 2px 6px;
    }

    .shop-search-box {
      position: relative;
      flex: 1 1 260px;
      max-width: 380px;
    }

    .shop-search-input {
      width: 100%;
      box-sizing: border-box;
      padding: 10px 42px 10px 16px;
      border-radius: 999px;
      border: 1px solid var(--shop-slate-200);
      background: var(--shop-slate-50);
      font-family: inherit;
      font-size: 13.5px;
      color: var(--shop-dark);
      transition: all 0.2s ease;
      outline: none;
    }

    .shop-search-input:focus {
      border-color: var(--shop-primary);
      background: #ffffff;
      box-shadow: 0 0 0 3px rgba(250, 166, 26, 0.15);
    }

    .shop-search-icon {
      position: absolute;
      top: 50%;
      right: 14px;
      transform: translateY(-50%);
      color: var(--shop-slate-500);
      pointer-events: none;
      display: flex;
    }

    /* ===== PRODUCTS GRID ===== */
    .shop-results-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      font-size: 14px;
      color: var(--shop-slate-500);
    }

    .shop-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 28px;
    }

    .shop-card {
      background: #ffffff;
      border: 1px solid var(--shop-slate-200);
      border-radius: var(--shop-radius-md);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: var(--shop-shadow);
      transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
      position: relative;
    }

    .shop-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shop-shadow-hover);
      border-color: #cbd5e1;
    }

    .shop-card-media {
      position: relative;
      width: 100%;
      aspect-ratio: 1/1;
      background: var(--shop-slate-100);
      overflow: hidden;
    }

    .shop-card-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
      display: block;
    }

    .shop-card:hover .shop-card-img {
      transform: scale(1.05);
    }

    .shop-card-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #94a3b8;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    .shop-badge-cat {
      position: absolute;
      top: 14px;
      right: 14px;
      background: rgba(255, 255, 255, 0.94);
      backdrop-filter: blur(8px);
      color: var(--shop-slate-800);
      font-size: 12px;
      font-weight: 700;
      padding: 4px 12px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      z-index: 2;
    }

    .shop-badge-stock {
      position: absolute;
      top: 14px;
      left: 14px;
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 8px;
      z-index: 2;
    }

    .shop-badge-stock.in-stock {
      background: var(--shop-green-subtle);
      color: #065f46;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .shop-badge-stock.out-of-stock {
      background: var(--shop-red-subtle);
      color: #991b1b;
      border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .shop-card-body {
      padding: 20px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }

    .shop-card-title {
      font-size: 17px;
      font-weight: 800;
      line-height: 1.45;
      margin: 0 0 10px;
      color: var(--shop-dark);
    }

    .shop-card-desc {
      font-size: 13px;
      color: var(--shop-slate-500);
      line-height: 1.7;
      margin: 0 0 16px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      flex: 1;
    }

    .shop-card-price-row {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      gap: 8px;
      padding-top: 14px;
      border-top: 1px dashed var(--shop-slate-200);
      margin-bottom: 16px;
    }

    .shop-price-label {
      font-size: 12px;
      color: var(--shop-slate-500);
    }

    .shop-price-amount {
      font-size: 19px;
      font-weight: 900;
      color: var(--shop-dark);
    }

    .shop-price-currency {
      font-size: 11.5px;
      color: var(--shop-slate-500);
      font-weight: 500;
      margin-right: 4px;
    }

    .shop-card-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .shop-btn-quickview {
      border: 1px solid var(--shop-slate-200);
      background: var(--shop-slate-50);
      color: var(--shop-slate-800);
      font-family: inherit;
      font-size: 13px;
      font-weight: 700;
      padding: 10px 12px;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .shop-btn-quickview:hover {
      background: #ffffff;
      border-color: #cbd5e1;
      color: var(--shop-dark);
    }

    .shop-btn-add {
      border: 1px solid var(--shop-primary);
      background: var(--shop-primary);
      color: #ffffff;
      font-family: inherit;
      font-size: 13px;
      font-weight: 700;
      padding: 10px 12px;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      box-shadow: 0 4px 10px rgba(250, 166, 26, 0.25);
    }

    .shop-btn-add:hover:not(:disabled) {
      background: var(--shop-primary-hover);
      border-color: var(--shop-primary-hover);
      transform: translateY(-1px);
      box-shadow: 0 6px 14px rgba(250, 166, 26, 0.35);
    }

    .shop-btn-add:disabled {
      background: #e2e8f0;
      border-color: #cbd5e1;
      color: #94a3b8;
      cursor: not-allowed;
      box-shadow: none;
    }

    /* ===== EMPTY / NO RESULTS ===== */
    .shop-no-results {
      grid-column: 1 / -1;
      background: #ffffff;
      border: 2px dashed var(--shop-slate-200);
      border-radius: var(--shop-radius-md);
      padding: 60px 20px;
      text-align: center;
      color: var(--shop-slate-500);
    }

    .shop-no-results svg {
      color: #94a3b8;
      margin-bottom: 16px;
    }

    /* ===== QUICK-VIEW MODAL ===== */
    .shop-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(4px);
      z-index: 99995;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.25s ease, visibility 0.25s ease;
    }

    .shop-modal-backdrop.open {
      opacity: 1;
      visibility: visible;
    }

    .shop-modal-panel {
      background: #ffffff;
      width: min(840px, 100%);
      max-height: 90vh;
      border-radius: var(--shop-radius-lg);
      box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
      overflow-y: auto;
      transform: scale(0.95);
      transition: transform 0.25s ease;
      position: relative;
      display: grid;
      grid-template-columns: 1fr 1.15fr;
      direction: var(--site-direction, rtl);
    }

    .shop-modal-backdrop.open .shop-modal-panel {
      transform: scale(1);
    }

    @media (max-width: 768px) {
      .shop-modal-panel {
        grid-template-columns: 1fr;
      }
    }

    .shop-modal-close {
      position: absolute;
      top: 16px;
      left: 16px;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid var(--shop-slate-200);
      background: #ffffff;
      color: var(--shop-slate-600);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      z-index: 10;
    }

    .shop-modal-close:hover {
      background: var(--shop-slate-100);
      color: var(--shop-dark);
    }

    .shop-modal-gallery {
      background: var(--shop-slate-100);
      padding: 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .shop-modal-main-img {
      width: 100%;
      aspect-ratio: 1/1;
      max-height: 360px;
      object-fit: cover;
      border-radius: var(--shop-radius-md);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      background: #ffffff;
    }

    .shop-modal-content {
      padding: 32px 28px;
      display: flex;
      flex-direction: column;
    }

    .shop-modal-cat {
      display: inline-block;
      font-size: 12px;
      font-weight: 700;
      color: var(--shop-primary-hover);
      margin-bottom: 8px;
    }

    .shop-modal-title {
      font-size: 22px;
      font-weight: 900;
      margin: 0 0 14px;
      color: var(--shop-dark);
      line-height: 1.4;
    }

    .shop-modal-price {
      font-size: 24px;
      font-weight: 900;
      color: var(--shop-dark);
      margin-bottom: 18px;
      display: flex;
      align-items: baseline;
      gap: 6px;
    }

    .shop-modal-desc {
      font-size: 14.5px;
      line-height: 1.8;
      color: var(--shop-slate-600);
      margin: 0 0 24px;
      white-space: pre-line;
      flex: 1;
    }

    .shop-modal-actions {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-top: auto;
    }

    .shop-qty-stepper {
      display: inline-flex;
      align-items: center;
      border: 1px solid var(--shop-slate-200);
      border-radius: 10px;
      overflow: hidden;
      background: var(--shop-slate-50);
    }

    .shop-qty-btn {
      width: 38px;
      height: 40px;
      border: none;
      background: transparent;
      color: var(--shop-dark);
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.15s;
    }

    .shop-qty-btn:hover {
      background: #e2e8f0;
    }

    .shop-qty-input {
      width: 44px;
      height: 40px;
      border: none;
      background: #ffffff;
      text-align: center;
      font-family: inherit;
      font-size: 14px;
      font-weight: 800;
      color: var(--shop-dark);
      outline: none;
    }

    .shop-modal-add-btn {
      flex: 1;
      height: 42px;
      border: none;
      background: var(--shop-primary);
      color: #ffffff;
      font-family: inherit;
      font-size: 14px;
      font-weight: 800;
      border-radius: 10px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 6px 16px rgba(250, 166, 26, 0.3);
      transition: all 0.2s ease;
    }

    .shop-modal-add-btn:hover:not(:disabled) {
      background: var(--shop-primary-hover);
      transform: translateY(-1px);
    }

    .shop-modal-add-btn:disabled {
      background: #e2e8f0;
      color: #94a3b8;
      cursor: not-allowed;
      box-shadow: none;
    }

    /* ===== FLOATING CART TRIGGER BADGE ===== */
    .shop-floating-cart-btn {
      position: fixed;
      bottom: 28px;
      left: 28px;
      z-index: 99990;
      background: var(--shop-primary);
      color: #ffffff;
      border: none;
      border-radius: 999px;
      padding: 14px 22px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: 0 12px 28px rgba(250, 166, 26, 0.45);
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s, background 0.2s;
    }

    .shop-floating-cart-btn:hover {
      background: var(--shop-primary-hover);
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 16px 36px rgba(250, 166, 26, 0.55);
    }

    .shop-floating-cart-icon {
      position: relative;
      display: flex;
    }

    .shop-floating-cart-count {
      position: absolute;
      top: -8px;
      left: -8px;
      background: #0f172a;
      color: #ffffff;
      font-size: 11px;
      font-weight: 800;
      min-width: 20px;
      height: 20px;
      border-radius: 999px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 4px;
      border: 2px solid #ffffff;
    }

    .shop-floating-cart-label {
      font-family: inherit;
      font-size: 14px;
      font-weight: 800;
    }

    /* ===== SLIDE-IN DRAWER CART ===== */
    .shop-drawer-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.5);
      backdrop-filter: blur(4px);
      z-index: 99998;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .shop-drawer-backdrop.open {
      opacity: 1;
      visibility: visible;
    }

    .shop-drawer {
      position: fixed;
      top: 0;
      right: -420px;
      width: min(420px, 92vw);
      height: 100%;
      height: 100vh;
      height: 100dvh;
      background: #ffffff;
      z-index: 99999;
      box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
      display: flex;
      flex-direction: column;
      transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1);
      direction: var(--site-direction, rtl);
      font-family: 'Vazirmatn', sans-serif;
    }

    .shop-drawer.open {
      right: 0;
    }

    .shop-drawer-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--shop-slate-200);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #ffffff;
    }

    .shop-drawer-title-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .shop-drawer-title {
      font-size: 18px;
      font-weight: 900;
      margin: 0;
      color: var(--shop-dark);
    }

    .shop-drawer-badge {
      background: var(--shop-primary-subtle);
      color: #92400e;
      font-size: 12px;
      font-weight: 800;
      padding: 2px 8px;
      border-radius: 999px;
    }

    .shop-drawer-close {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid var(--shop-slate-200);
      background: var(--shop-slate-50);
      color: var(--shop-slate-600);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .shop-drawer-close:hover {
      background: var(--shop-slate-100);
      color: var(--shop-dark);
    }

    .shop-drawer-body {
      flex: 1;
      overflow-y: auto;
      padding: 20px 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .shop-cart-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      height: 100%;
      padding: 40px 10px;
      color: var(--shop-slate-500);
    }

    .shop-cart-empty-icon {
      width: 64px;
      height: 64px;
      color: #cbd5e1;
      margin-bottom: 16px;
    }

    .shop-cart-empty-title {
      font-size: 16px;
      font-weight: 800;
      color: var(--shop-dark);
      margin: 0 0 8px;
    }

    .shop-cart-empty-desc {
      font-size: 13.5px;
      margin: 0 0 20px;
      line-height: 1.6;
    }

    .shop-cart-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px;
      border: 1px solid var(--shop-slate-200);
      border-radius: 12px;
      background: #ffffff;
      transition: border-color 0.2s;
    }

    .shop-cart-item:hover {
      border-color: #cbd5e1;
    }

    .shop-cart-item-img {
      width: 64px;
      height: 64px;
      border-radius: 8px;
      object-fit: cover;
      background: var(--shop-slate-100);
      flex-shrink: 0;
    }

    .shop-cart-item-info {
      flex: 1;
      min-width: 0;
    }

    .shop-cart-item-title {
      font-size: 14px;
      font-weight: 800;
      color: var(--shop-dark);
      margin: 0 0 4px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .shop-cart-item-price {
      font-size: 13px;
      font-weight: 700;
      color: var(--shop-slate-600);
      margin: 0 0 8px;
    }

    .shop-cart-item-controls {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }

    .shop-cart-stepper {
      display: inline-flex;
      align-items: center;
      border: 1px solid var(--shop-slate-200);
      border-radius: 6px;
      background: var(--shop-slate-50);
      height: 28px;
    }

    .shop-cart-stepper button {
      width: 26px;
      height: 100%;
      border: none;
      background: transparent;
      cursor: pointer;
      font-size: 14px;
      font-weight: 700;
      color: var(--shop-dark);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .shop-cart-stepper button:hover {
      background: #e2e8f0;
    }

    .shop-cart-stepper span {
      width: 28px;
      text-align: center;
      font-size: 12.5px;
      font-weight: 800;
    }

    .shop-cart-item-remove {
      background: transparent;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      padding: 4px;
      display: flex;
      border-radius: 4px;
      transition: color 0.15s;
    }

    .shop-cart-item-remove:hover {
      color: var(--shop-red);
    }

    .shop-drawer-footer {
      padding: 20px 24px;
      border-top: 1px solid var(--shop-slate-200);
      background: var(--shop-slate-50);
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .shop-drawer-total-row {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
    }

    .shop-drawer-total-label {
      font-size: 14px;
      font-weight: 700;
      color: var(--shop-slate-600);
    }

    .shop-drawer-total-val {
      font-size: 20px;
      font-weight: 900;
      color: var(--shop-dark);
    }

    /* Notice box for post-shipping */
    .shop-shipping-notice {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background: #fef3c7;
      border: 1px solid #fde68a;
      border-radius: 10px;
      padding: 10px 12px;
      color: #92400e;
      font-size: 12.5px;
      line-height: 1.6;
      font-weight: 600;
    }

    .shop-shipping-notice svg {
      flex-shrink: 0;
      margin-top: 2px;
      color: #b45309;
    }

    .shop-drawer-checkout-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: var(--shop-primary);
      color: #ffffff;
      text-decoration: none;
      font-family: inherit;
      font-size: 15px;
      font-weight: 800;
      padding: 14px;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(250, 166, 26, 0.35);
      transition: all 0.2s ease;
      border: none;
      cursor: pointer;
    }

    .shop-drawer-checkout-btn:hover:not(.disabled) {
      background: var(--shop-primary-hover);
      transform: translateY(-1px);
      box-shadow: 0 8px 22px rgba(250, 166, 26, 0.45);
      color: #ffffff;
    }

    .shop-drawer-checkout-btn.disabled {
      background: #cbd5e1;
      cursor: not-allowed;
      box-shadow: none;
      color: #64748b;
      pointer-events: none;
    }
  </style>

  <div class="shop-container">
    <!-- HERO BANNER -->
    <header class="shop-hero">
      <div class="shop-hero-content">
        <div class="shop-hero-badge">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
          <span><?= site_e('فروشگاه خیریه مکسا') ?></span>
        </div>
        <h1 class="shop-hero-title"><?= site_e('محصولات مهربانی؛') ?> <span><?= site_e('خرید در خدمت درمان بیماران مبتلا به سرطان') ?></span></h1>
        <p class="shop-hero-text">
          <?= site_e('با خرید هر محصول از این فروشگاه، تمامی عواید آن به صورت صددرصدی صرف تأمین هزینه‌های دارو، درمان و ارائه مراقبت‌های تسکینی رایگان به بیماران مبتلا به سرطان و خانواده‌های آنان می‌شود.') ?>
        </p>
        <div class="shop-hero-features">
          <div class="shop-hero-feat-item">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span><?= site_e('۱۰۰٪ عواید وقف درمان رایگان بیماران') ?></span>
          </div>
          <div class="shop-hero-feat-item">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <span><?= site_e('ارسال به سراسر کشور (پس‌کرایه)') ?></span>
          </div>
          <div class="shop-hero-feat-item">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span><?= site_e('صنایع دستی کارگاه‌های توانمندسازی') ?></span>
          </div>
        </div>
      </div>
    </header>

    <!-- FILTER & CONTROLS -->
    <div class="shop-controls">
      <div class="shop-cat-pills" id="shopCatPills" role="tablist" aria-label="<?= site_e('دسته‌بندی‌های محصولات') ?>">
        <button type="button" class="shop-cat-btn active" data-cat="all" role="tab" aria-selected="true">
          <span><?= site_e('همه محصولات') ?></span>
          <span class="shop-cat-count" id="count-all"><?= fa_digits(count($products)) ?></span>
        </button>
        <?php foreach ($categories as $cat): ?>
          <?php
            $catId = (int)$cat['id'];
            $catTitle = htmlspecialchars($cat['title'] ?? '');
            $matchingCount = 0;
            foreach ($products as $p) {
              if ((int)($p['category_id'] ?? 0) === $catId) {
                $matchingCount++;
              }
            }
          ?>
          <button type="button" class="shop-cat-btn" data-cat="<?= $catId ?>" role="tab" aria-selected="false">
            <span><?= $catTitle ?></span>
            <span class="shop-cat-count" id="count-<?= $catId ?>"><?= fa_digits($matchingCount) ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="shop-search-box">
        <span class="shop-search-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </span>
        <input type="search" id="shopSearchInput" class="shop-search-input" placeholder="<?= site_e('جستجوی محصولات...') ?>" aria-label="<?= site_e('جستجوی محصولات') ?>">
      </div>
    </div>

    <!-- RESULTS INFO -->
    <div class="shop-results-meta">
      <span id="shopResultsSummary"><?= site_e('نمایش') ?> <?= fa_digits(count($products)) ?> <?= site_e('محصول') ?></span>
    </div>

    <!-- PRODUCTS GRID -->
    <main class="shop-grid" id="shopGrid">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
          <?php
            $pId = (int)$p['id'];
            $pCatId = (int)($p['category_id'] ?? 0);
            $pTitle = htmlspecialchars($p['title'] ?? '');
            $pDesc = htmlspecialchars($p['description'] ?? '');
            $pCat = htmlspecialchars($p['category_title'] ?? 'فروشگاه');
            $pPrice = (float)($p['price'] ?? 0);
            $pStock = (int)($p['stock'] ?? 0);
            $pStatus = (string)($p['status'] ?? 'active');
            $pImg = !empty($p['image_url']) ? htmlspecialchars($p['image_url']) : '';
            $isAvailable = ($pStock > 0 && $pStatus === 'active');
          ?>
          <article class="shop-card" id="prod-<?= $pId ?>" data-id="<?= $pId ?>" data-cat="<?= $pCatId ?>" data-title="<?= mb_strtolower($pTitle) ?>" data-desc="<?= mb_strtolower($pDesc) ?>">
            <div class="shop-card-media">
              <?php if ($pImg): ?>
                <img src="<?= $pImg ?>" alt="<?= $pTitle ?>" class="shop-card-img" loading="lazy">
              <?php else: ?>
                <div class="shop-card-placeholder">
                  <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                  </svg>
                </div>
              <?php endif; ?>

              <span class="shop-badge-cat"><?= $pCat ?></span>

              <?php if ($isAvailable): ?>
                <span class="shop-badge-stock in-stock"><?= site_e('موجود') ?></span>
              <?php else: ?>
                <span class="shop-badge-stock out-of-stock"><?= site_e('ناموجود') ?></span>
              <?php endif; ?>
            </div>

            <div class="shop-card-body">
              <h2 class="shop-card-title"><?= $pTitle ?></h2>
              <p class="shop-card-desc"><?= $pDesc ?: site_e('بدون توضیحات اضافی.') ?></p>

              <div class="shop-card-price-row">
                <span class="shop-price-label"><?= site_e('قیمت:') ?></span>
                <div>
                  <span class="shop-price-amount"><?= shop_format_toman($pPrice) ?></span>
                  <span class="shop-price-currency"><?= site_e('تومان') ?></span>
                </div>
              </div>

              <div class="shop-card-actions">
                <button type="button" class="shop-btn-quickview js-quickview-btn" data-id="<?= $pId ?>">
                  <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <span><?= site_e('مشاهده جزئیات') ?></span>
                </button>

                <button type="button" class="shop-btn-add js-add-to-cart-btn" data-id="<?= $pId ?>" <?= !$isAvailable ? 'disabled' : '' ?>>
                  <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                  </svg>
                  <span><?= $isAvailable ? site_e('افزودن به سبد') : site_e('ناموجود') ?></span>
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
        <div id="shopFilterEmptyState" class="shop-no-results" style="display: none; grid-column: 1 / -1;">
          <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <p style="font-size: 16px; font-weight: 700; margin: 0;"><?= site_e('هیچ محصولی با این مشخصات یافت نشد') ?></p>
        </div>
      <?php else: ?>
        <div class="shop-no-results">
          <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M8 12h8"></path>
          </svg>
          <p style="font-size: 16px; font-weight: 700;"><?= site_e('محصولی برای نمایش در دسترس نیست.') ?></p>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- QUICK-VIEW MODAL -->
  <div class="shop-modal-backdrop" id="shopQuickViewModal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="shop-modal-panel">
      <button type="button" class="shop-modal-close" id="shopModalClose" aria-label="<?= site_e('بستن پنجره') ?>">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>

      <div class="shop-modal-gallery">
        <img src="" alt="" id="modalImg" class="shop-modal-main-img">
      </div>

      <div class="shop-modal-content">
        <span class="shop-modal-cat" id="modalCat"></span>
        <h2 class="shop-modal-title" id="modalTitle"></h2>
        <div class="shop-modal-price">
          <span id="modalPrice"></span>
          <span style="font-size: 14px; font-weight: 500; color: var(--shop-slate-500);"><?= site_e('تومان') ?></span>
        </div>
        <p class="shop-modal-desc" id="modalDesc"></p>

        <div class="shop-modal-actions">
          <div class="shop-qty-stepper">
            <button type="button" class="shop-qty-btn" id="modalQtyMinus">-</button>
            <input type="number" id="modalQtyInput" class="shop-qty-input" value="1" min="1" max="99" readonly>
            <button type="button" class="shop-qty-btn" id="modalQtyPlus">+</button>
          </div>
          <button type="button" class="shop-modal-add-btn" id="modalAddBtn">
            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span><?= site_e('افزودن به سبد خرید') ?></span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- FLOATING CART BUTTON -->
  <button type="button" class="shop-floating-cart-btn" id="shopFloatingCartBtn" aria-label="<?= site_e('مشاهده سبد خرید') ?>">
    <div class="shop-floating-cart-icon">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
      </svg>
      <span class="shop-floating-cart-count" id="cartBadgeCount">۰</span>
    </div>
    <span class="shop-floating-cart-label"><?= site_e('سبد خرید') ?></span>
  </button>

  <!-- SLIDE-IN DRAWER CART -->
  <div class="shop-drawer-backdrop" id="shopDrawerBackdrop" aria-hidden="true"></div>
  <aside class="shop-drawer" id="shopDrawer" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?= site_e('سبد خرید') ?>">
    <div class="shop-drawer-header">
      <div class="shop-drawer-title-wrap">
        <h2 class="shop-drawer-title"><?= site_e('سبد خرید شما') ?></h2>
        <span class="shop-drawer-badge" id="drawerItemCount">۰ قلم</span>
      </div>
      <button type="button" class="shop-drawer-close" id="shopDrawerClose" aria-label="<?= site_e('بستن سبد خرید') ?>">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <div class="shop-drawer-body" id="drawerCartItems">
      <!-- Items dynamically populated via window.MaxaShopCart.render() -->
    </div>

    <div class="shop-drawer-footer">
      <div class="shop-drawer-total-row">
        <span class="shop-drawer-total-label"><?= site_e('مجموع سبد خرید:') ?></span>
        <span class="shop-drawer-total-val" id="drawerTotalPrice">۰ تومان</span>
      </div>

      <!-- Notice box for post-shipping -->
      <div class="shop-shipping-notice">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="3" width="15" height="13"></rect>
          <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
          <circle cx="5.5" cy="18.5" r="2.5"></circle>
          <circle cx="18.5" cy="18.5" r="2.5"></circle>
        </svg>
        <span><?= site_e('هزینه ارسال به صورت پس‌کرایه است و زمان تحویل توسط مامور پست دریافت می‌شود') ?></span>
      </div>

      <a href="/shop-checkout.php" class="shop-drawer-checkout-btn" id="drawerCheckoutBtn">
        <span><?= site_e('تکمیل خرید و پرداخت') ?></span>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
      </a>
    </div>
  </aside>
</div>

<!-- Embedded JSON Catalog for Snappy Client-Side Modal & Filtering -->
<script type="application/json" id="shop-catalog-data">
<?= json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>
</script>

<script>
(function() {
  'use strict';

  // --- Utility functions ---
  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function toPersianDigits(num) {
    var str = String(num);
    var p = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return str.replace(/[0-9]/g, function(d) { return p[+d]; });
  }

  function formatToman(price) {
    var val = Math.round(Number(price) || 0);
    var formatted = val.toLocaleString('en-US');
    return toPersianDigits(formatted.replace(/,/g, '٬'));
  }

  // Load products data
  var rawDataEl = document.getElementById('shop-catalog-data');
  var catalog = [];
  try {
    if (rawDataEl && rawDataEl.textContent) {
      catalog = JSON.parse(rawDataEl.textContent);
    }
  } catch(e) {
    catalog = [];
  }

  var catalogMap = {};
  catalog.forEach(function(item) {
    catalogMap[item.id] = item;
  });

  // --- Cart Management API (window.MaxaShopCart) ---
  var CART_STORAGE_KEY = 'maxa_shop_cart';

  var Cart = {
    items: [],

    init: function() {
      try {
        var saved = localStorage.getItem(CART_STORAGE_KEY);
        if (saved) {
          this.items = JSON.parse(saved);
          if (!Array.isArray(this.items)) this.items = [];
        }
      } catch(e) {
        this.items = [];
      }
      this.render();
    },

    save: function() {
      try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(this.items));
      } catch(e) {}
      this.render();
    },

    add: function(product, qty) {
      qty = parseInt(qty, 10) || 1;
      if (qty <= 0) return;

      var pId = parseInt(product.id, 10);
      var maxStock = (product.stock !== undefined && product.stock !== null && !isNaN(parseInt(product.stock, 10))) ? parseInt(product.stock, 10) : 999;
      if (maxStock <= 0) return;

      var existing = this.items.find(function(it) { return it.id === pId; });

      if (existing) {
        var newQty = existing.quantity + qty;
        if (newQty > maxStock) newQty = maxStock;
        existing.quantity = newQty;
      } else {
        var initialQty = qty > maxStock ? maxStock : qty;
        if (initialQty > 0) {
          this.items.push({
            id: pId,
            title: product.title || '',
            price: parseFloat(product.price) || 0,
            image_url: product.image_url || '',
            quantity: initialQty,
            stock: maxStock
          });
        }
      }

      this.save();
      this.openDrawer();
    },

    remove: function(productId) {
      var pId = parseInt(productId, 10);
      this.items = this.items.filter(function(it) { return it.id !== pId; });
      this.save();
    },

    updateQty: function(productId, deltaOrQty, isDelta) {
      if (typeof isDelta === 'undefined') isDelta = true;
      var pId = parseInt(productId, 10);
      var item = this.items.find(function(it) { return it.id === pId; });
      if (!item) return;

      var maxStock = (item.stock !== undefined && item.stock !== null && !isNaN(parseInt(item.stock, 10))) ? parseInt(item.stock, 10) : 999;
      if (maxStock <= 0) {
        this.remove(productId);
        return;
      }

      var newQty = isDelta ? item.quantity + deltaOrQty : parseInt(deltaOrQty, 10);
      if (newQty <= 0) {
        this.remove(productId);
        return;
      }
      if (newQty > maxStock) {
        newQty = maxStock;
      }
      item.quantity = newQty;
      this.save();
    },

    getCount: function() {
      return this.items.reduce(function(sum, it) { return sum + (it.quantity || 1); }, 0);
    },

    getTotal: function() {
      return this.items.reduce(function(sum, it) { return sum + ((parseFloat(it.price) || 0) * (it.quantity || 1)); }, 0);
    },

    clear: function() {
      this.items = [];
      this.save();
    },

    openDrawer: function() {
      var drawer = document.getElementById('shopDrawer');
      var backdrop = document.getElementById('shopDrawerBackdrop');
      if (drawer) {
        drawer.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');
      }
      if (backdrop) {
        backdrop.classList.add('open');
        backdrop.setAttribute('aria-hidden', 'false');
      }
      document.body.style.overflow = 'hidden';
    },

    closeDrawer: function() {
      var drawer = document.getElementById('shopDrawer');
      var backdrop = document.getElementById('shopDrawerBackdrop');
      if (drawer) {
        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
      }
      if (backdrop) {
        backdrop.classList.remove('open');
        backdrop.setAttribute('aria-hidden', 'true');
      }
      document.body.style.overflow = '';
    },

    render: function() {
      var totalCount = this.getCount();
      var totalPrice = this.getTotal();

      // Update floating badges
      var badgeCountEl = document.getElementById('cartBadgeCount');
      if (badgeCountEl) {
        badgeCountEl.textContent = toPersianDigits(totalCount);
      }

      var drawerCountEl = document.getElementById('drawerItemCount');
      if (drawerCountEl) {
        drawerCountEl.textContent = toPersianDigits(totalCount) + ' قلم';
      }

      var drawerTotalEl = document.getElementById('drawerTotalPrice');
      if (drawerTotalEl) {
        drawerTotalEl.textContent = formatToman(totalPrice) + ' تومان';
      }

      var checkoutBtn = document.getElementById('drawerCheckoutBtn');
      if (checkoutBtn) {
        if (totalCount === 0) {
          checkoutBtn.classList.add('disabled');
          checkoutBtn.setAttribute('aria-disabled', 'true');
        } else {
          checkoutBtn.classList.remove('disabled');
          checkoutBtn.removeAttribute('aria-disabled');
        }
      }

      var container = document.getElementById('drawerCartItems');
      if (!container) return;

      if (this.items.length === 0) {
        container.innerHTML = '<div class="shop-cart-empty">' +
          '<svg class="shop-cart-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>' +
            '<line x1="3" y1="6" x2="21" y2="6"></line>' +
            '<path d="M16 10a4 4 0 0 1-8 0"></path>' +
          '</svg>' +
          '<h3 class="shop-cart-empty-title"><?= site_e('سبد خرید شما خالی است') ?></h3>' +
          '<p class="shop-cart-empty-desc"><?= site_e('می‌توانید محصولات نیکوکاری را به سبد خرید اضافه کنید.') ?></p>' +
        '</div>';
        return;
      }

      var html = '';
      this.items.forEach(function(item) {
        var itemTotal = (parseFloat(item.price) || 0) * (item.quantity || 1);
        var safeTitle = escapeHtml(item.title);
        var rawImg = item.image_url ? String(item.image_url).trim() : '';
        var safeImg = rawImg && !/^\s*javascript:/i.test(rawImg) ? escapeHtml(rawImg) : '/dashboard/assets/img/product/product-1.jpg';
        html += '<div class="shop-cart-item" data-id="' + item.id + '">' +
          '<img src="' + safeImg + '" alt="' + safeTitle + '" class="shop-cart-item-img">' +
          '<div class="shop-cart-item-info">' +
            '<h4 class="shop-cart-item-title">' + safeTitle + '</h4>' +
            '<div class="shop-cart-item-price">' + formatToman(item.price) + ' تومان</div>' +
            '<div class="shop-cart-item-controls">' +
              '<div class="shop-cart-stepper">' +
                '<button type="button" class="js-cart-minus" data-id="' + item.id + '">-</button>' +
                '<span>' + toPersianDigits(item.quantity) + '</span>' +
                '<button type="button" class="js-cart-plus" data-id="' + item.id + '">+</button>' +
              '</div>' +
              '<button type="button" class="shop-cart-item-remove js-cart-remove" data-id="' + item.id + '" title="<?= site_e('حذف از سبد') ?>">' +
                '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                  '<polyline points="3 6 5 6 21 6"></polyline>' +
                  '<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>' +
                '</svg>' +
              '</button>' +
            '</div>' +
          '</div>' +
        '</div>';
      });

      container.innerHTML = html;
    }
  };

  window.MaxaShopCart = Cart;

  // --- Attach Event Listeners ---
  document.addEventListener('DOMContentLoaded', function() {
    Cart.init();

    // Drawer openers and closers
    var floatingBtn = document.getElementById('shopFloatingCartBtn');
    if (floatingBtn) {
      floatingBtn.addEventListener('click', function() { Cart.openDrawer(); });
    }

    var drawerCloseBtn = document.getElementById('shopDrawerClose');
    if (drawerCloseBtn) {
      drawerCloseBtn.addEventListener('click', function() { Cart.closeDrawer(); });
    }

    var drawerBackdrop = document.getElementById('shopDrawerBackdrop');
    if (drawerBackdrop) {
      drawerBackdrop.addEventListener('click', function() { Cart.closeDrawer(); });
    }

    // Cart items delegation (+, -, remove)
    var cartItemsContainer = document.getElementById('drawerCartItems');
    if (cartItemsContainer) {
      cartItemsContainer.addEventListener('click', function(e) {
        var plusBtn = e.target.closest('.js-cart-plus');
        if (plusBtn) {
          var id = parseInt(plusBtn.dataset.id, 10);
          Cart.updateQty(id, 1, true);
          return;
        }

        var minusBtn = e.target.closest('.js-cart-minus');
        if (minusBtn) {
          var id = parseInt(minusBtn.dataset.id, 10);
          Cart.updateQty(id, -1, true);
          return;
        }

        var removeBtn = e.target.closest('.js-cart-remove');
        if (removeBtn) {
          var id = parseInt(removeBtn.dataset.id, 10);
          Cart.remove(id);
          return;
        }
      });
    }

    // Add to cart buttons in grid
    document.querySelectorAll('.js-add-to-cart-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var pId = parseInt(this.dataset.id, 10);
        var product = catalogMap[pId];
        if (product) {
          Cart.add(product, 1);
        }
      });
    });

    // Quick-View Modal logic
    var modal = document.getElementById('shopQuickViewModal');
    var modalClose = document.getElementById('shopModalClose');
    var activeModalProduct = null;
    var modalQtyInput = document.getElementById('modalQtyInput');

    function openModal(prod) {
      activeModalProduct = prod;
      if (!prod || !modal) return;

      document.getElementById('modalTitle').textContent = prod.title || '';
      document.getElementById('modalCat').textContent = prod.category_title || 'فروشگاه خیریه';
      document.getElementById('modalPrice').textContent = formatToman(prod.price);
      document.getElementById('modalDesc').textContent = prod.description || 'بدون توضیحات اضافی.';

      var img = document.getElementById('modalImg');
      if (img) {
        img.src = prod.image_url || '/dashboard/assets/img/product/product-1.jpg';
        img.alt = prod.title || '';
      }

      if (modalQtyInput) modalQtyInput.value = '1';

      var addBtn = document.getElementById('modalAddBtn');
      if (addBtn) {
        var isAvail = (parseInt(prod.stock, 10) > 0 && prod.status === 'active');
        addBtn.disabled = !isAvail;
        addBtn.querySelector('span').textContent = isAvail ? 'افزودن به سبد خرید' : 'ناموجود';
      }

      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      if (!modal) return;
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      activeModalProduct = null;
    }

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
      });
    }

    // Modal Qty buttons
    var modalPlus = document.getElementById('modalQtyPlus');
    var modalMinus = document.getElementById('modalQtyMinus');
    if (modalPlus && modalQtyInput) {
      modalPlus.addEventListener('click', function() {
        var cur = parseInt(modalQtyInput.value, 10) || 1;
        var max = (activeModalProduct && activeModalProduct.stock !== undefined && activeModalProduct.stock !== null && !isNaN(parseInt(activeModalProduct.stock, 10))) ? parseInt(activeModalProduct.stock, 10) : 99;
        if (cur < max) modalQtyInput.value = cur + 1;
      });
    }
    if (modalMinus && modalQtyInput) {
      modalMinus.addEventListener('click', function() {
        var cur = parseInt(modalQtyInput.value, 10) || 1;
        if (cur > 1) modalQtyInput.value = cur - 1;
      });
    }

    var modalAddBtn = document.getElementById('modalAddBtn');
    if (modalAddBtn) {
      modalAddBtn.addEventListener('click', function() {
        if (!activeModalProduct) return;
        var prodToAdd = activeModalProduct;
        var qty = parseInt(modalQtyInput.value, 10) || 1;
        closeModal();
        Cart.add(prodToAdd, qty);
      });
    }

    document.querySelectorAll('.js-quickview-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var pId = parseInt(this.dataset.id, 10);
        var product = catalogMap[pId];
        if (product) openModal(product);
      });
    });

    // Filtering & Live Search
    var catButtons = document.querySelectorAll('.shop-cat-btn');
    var searchInput = document.getElementById('shopSearchInput');
    var cards = document.querySelectorAll('.shop-card');
    var summaryEl = document.getElementById('shopResultsSummary');
    var currentCat = 'all';

    function filterCards() {
      var query = (searchInput ? searchInput.value : '').trim().toLowerCase();
      var visibleCount = 0;

      cards.forEach(function(card) {
        var cardCat = card.dataset.cat;
        var cardTitle = card.dataset.title || '';
        var cardDesc = card.dataset.desc || '';

        var matchesCat = (currentCat === 'all' || cardCat === currentCat);
        var matchesSearch = (!query || cardTitle.indexOf(query) !== -1 || cardDesc.indexOf(query) !== -1);

        if (matchesCat && matchesSearch) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      if (summaryEl) {
        summaryEl.textContent = 'نمایش ' + toPersianDigits(visibleCount) + ' محصول';
      }

      var emptyState = document.getElementById('shopFilterEmptyState');
      if (emptyState) {
        emptyState.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
      }
    }

    catButtons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        catButtons.forEach(function(b) {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        this.classList.add('active');
        this.setAttribute('aria-selected', 'true');
        currentCat = this.dataset.cat;
        filterCards();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', filterCards);
    }

    // Keyboard navigation (Escape key closes drawer & modal)
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        Cart.closeDrawer();
        closeModal();
      }
    });

    // Check URL anchor for product deep-link (e.g. #prod-5)
    if (window.location.hash) {
      var match = window.location.hash.match(/#prod-(\d+)/);
      if (match) {
        var directId = parseInt(match[1], 10);
        var prod = catalogMap[directId];
        if (prod) {
          setTimeout(function() { openModal(prod); }, 300);
        }
      }
    }
  });
})();
</script>

<?php
render_dashboard_component('footer', get_defined_vars());
