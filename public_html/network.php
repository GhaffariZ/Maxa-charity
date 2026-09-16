<?php
// صفحه‌ی عمومی شبکه همکاران مکسا
$pageTitle = 'شبکه همکاران مکسا';
require_once __DIR__ . '/core/component-renderer.php';
render_dashboard_component('header', get_defined_vars());
render_dashboard_component('personal-resume-list', get_defined_vars());
render_dashboard_component('footer', get_defined_vars());
