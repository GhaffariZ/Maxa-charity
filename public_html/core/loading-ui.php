<?php
declare(strict_types=1);

function macsa_loader(string $label, string $variant = 'helix'): string
{
    $html = '<span class="macsa-loader" role="status" aria-label="'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'">';
    if ($variant === 'pulse') {
        $path = 'M2 34H28L36 26L44 42L54 8L66 56L76 28L84 34H118';
        $html .= '<svg class="macsa-loader__pulse" viewBox="0 0 120 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . '<path class="macsa-loader__track" d="'.$path.'"/><path class="macsa-loader__beat" pathLength="200" d="'.$path.'"/></svg>';
    } else {
        $html .= '<span class="macsa-loader__helix" aria-hidden="true">';
        for ($row = 0; $row < 7; $row++) {
            $html .= '<span class="macsa-loader__pair" style="--row:'.$row.'"><i></i><i></i></span>';
        }
        $html .= '</span>';
    }
    return $html.'</span>';
}

function macsa_dashboard_skeleton(): void
{
    ?>
    <div class="macsa-loading-layout" dir="rtl">
      <div class="macsa-loading-heading">
        <div class="macsa-loading-status">
          <?= macsa_loader('در حال دریافت اطلاعات صفحه') ?>
          <p class="macsa-loading-label" aria-hidden="true">در حال دریافت اطلاعات صفحه</p>
        </div>
        <div class="macsa-loading-lines" aria-hidden="true"><div class="macsa-skeleton macsa-loading-line"></div></div>
      </div>
      <div class="macsa-loading-stats" aria-hidden="true">
        <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="macsa-loading-card">
          <div class="macsa-skeleton macsa-loading-line"></div><div class="macsa-skeleton macsa-loading-number"></div><div class="macsa-skeleton macsa-loading-line macsa-loading-line--short"></div>
        </div>
        <?php endfor; ?>
      </div>
      <div class="macsa-loading-columns" aria-hidden="true">
        <div class="macsa-loading-card">
          <div class="macsa-skeleton macsa-loading-line macsa-loading-line--short"></div>
          <div class="macsa-loading-chart"><?= macsa_loader('', 'pulse') ?></div>
        </div>
        <div class="macsa-loading-card">
          <div class="macsa-skeleton macsa-loading-line"></div>
          <?php for ($i = 0; $i < 3; $i++): ?>
          <div class="macsa-loading-row">
            <div class="macsa-skeleton macsa-loading-avatar"></div>
            <div class="macsa-loading-lines"><div class="macsa-skeleton macsa-loading-line"></div><div class="macsa-skeleton macsa-loading-line macsa-loading-line--short"></div></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
    <?php
}
