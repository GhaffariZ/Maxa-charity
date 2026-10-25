<?php
require_once __DIR__ . '/core/site-locale.php';
/* ============================================================================
 *  صفحه عمومی سفارش استند و کارت دیجیتال مکسا
 *  از الگوی استاندارد کامپوننت‌های سایت پیروی می‌کند: هدر، کامپوننت استند، فوتر
 * ========================================================================== */

$pageTitle = site_text('سفارش استند و کارت دیجیتال | خیریه مکسا');

require_once __DIR__ . '/core/component-renderer.php';
render_dashboard_component('header', get_defined_vars());
render_dashboard_component('stand-order', get_defined_vars());
render_dashboard_component('footer', get_defined_vars());
?>
