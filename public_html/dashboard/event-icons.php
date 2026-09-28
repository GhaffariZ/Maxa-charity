<?php
/**
 * Iconoir Icon Library for Maxa Event Management
 * Pure SVG vectors extracted directly from Iconoir (https://iconoir.com)
 */
declare(strict_types=1);

function hq_iconoir(string $name, string $class = '', int $size = 18): string {
    static $icons = null;
    if ($icons === null) {
        $icons = [
            'tabs' => '<svg class="iconoir" aria-hidden="true" viewBox="0 0 24 24" stroke-width="1.5" fill="none">
<path d="M22 8H15.5M9 4V8H15.5M15.5 8V4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2 17.7143V6.28571C2 5.02335 2.99492 4 4.22222 4H19.7778C21.0051 4 22 5.02335 22 6.28571V17.7143C22 18.9767 21.0051 20 19.7778 20H4.22222C2.99492 20 2 18.9767 2 17.7143Z" stroke="currentColor" stroke-width="1.5"/>
</svg>',
            'wizard' => '<svg class="iconoir" aria-hidden="true" viewBox="0 0 24 24" stroke-width="1.5" fill="none">
<path d="M3 5L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M20.5 7L20.5 3L19 4.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M21 14L19 14L20.9047 11.0371C20.9669 10.9403 21.0021 10.8268 20.9771 10.7145C20.9193 10.4557 20.716 10 20 10C19 10 19 10.8889 19 10.8889C19 10.8889 19 10.8889 19 10.8889L19 11.1111" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M19.5 19L20 19C20.5523 19 21 19.4477 21 20V20C21 20.5523 20.5523 21 20 21L19 21" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M19 17L21 17L19.5 19" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M3 12L15 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M3 19L15 19" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'sidebar' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M20.4 3H3.6C3.26863 3 3 3.26863 3 3.6V20.4C3 20.7314 3.26863 21 3.6 21H20.4C20.7314 21 21 20.7314 21 20.4V3.6C21 3.26863 20.7314 3 20.4 3Z" stroke="currentColor" stroke-width="1.5"/>
<path d="M14.25 9.75V21" stroke="currentColor" stroke-width="1.5"/>
<path d="M21 9.75H14.25H3" stroke="currentColor" stroke-width="1.5"/>
</svg>',
            'full' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M6 6C6 5.20435 6.31607 4.44129 6.87868 3.87868C7.44129 3.31607 8.20435 3 9 3H12V9H9C8.20435 9 7.44129 8.68393 6.87868 8.12132C6.31607 7.55871 6 6.79565 6 6Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 3H15C15.394 3 15.7841 3.0776 16.1481 3.22836C16.512 3.37913 16.8427 3.6001 17.1213 3.87868C17.3999 4.15726 17.6209 4.48797 17.7716 4.85195C17.9224 5.21593 18 5.60603 18 6C18 6.39397 17.9224 6.78407 17.7716 7.14805C17.6209 7.51203 17.3999 7.84274 17.1213 8.12132C16.8427 8.3999 16.512 8.62087 16.1481 8.77164C15.7841 8.9224 15.394 9 15 9H12V3Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 12C12 11.606 12.0776 11.2159 12.2284 10.8519C12.3791 10.488 12.6001 10.1573 12.8787 9.87868C13.1573 9.6001 13.488 9.37913 13.8519 9.22836C14.2159 9.0776 14.606 9 15 9C15.394 9 15.7841 9.0776 16.1481 9.22836C16.512 9.37913 16.8427 9.6001 17.1213 9.87868C17.3999 10.1573 17.6209 10.488 17.7716 10.8519C17.9224 11.2159 18 11.606 18 12C18 12.394 17.9224 12.7841 17.7716 13.1481C17.6209 13.512 17.3999 13.8427 17.1213 14.1213C16.8427 14.3999 16.512 14.6209 16.1481 14.7716C15.7841 14.9224 15.394 15 15 15C14.606 15 14.2159 14.9224 13.8519 14.7716C13.488 14.6209 13.1573 14.3999 12.8787 14.1213C12.6001 13.8427 12.3791 13.512 12.2284 13.1481C12.0776 12.7841 12 12.394 12 12V12Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6 18C6 17.2044 6.31607 16.4413 6.87868 15.8787C7.44129 15.3161 8.20435 15 9 15H12V18C12 18.7956 11.6839 19.5587 11.1213 20.1213C10.5587 20.6839 9.79565 21 9 21C8.20435 21 7.44129 20.6839 6.87868 20.1213C6.31607 19.5587 6 18.7956 6 18Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6 12C6 11.2044 6.31607 10.4413 6.87868 9.87868C7.44129 9.31607 8.20435 9 9 9H12V15H9C8.20435 15 7.44129 14.6839 6.87868 14.1213C6.31607 13.5587 6 12.7956 6 12Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'base' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M15 4V2M15 4V6M15 4H10.5M3 10V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V10H3Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M3 10V6C3 4.89543 3.89543 4 5 4H7" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M7 2V6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M21 10V6C21 4.89543 20.1046 4 19 4H18.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'media' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M22 12.6V20.4C22 20.7314 21.7314 21 21.4 21H13.6C13.2686 21 13 20.7314 13 20.4V12.6C13 12.2686 13.2686 12 13.6 12H21.4C21.7314 12 22 12.2686 22 12.6Z" stroke="currentColor"  stroke-linecap="round" stroke-linejoin="round"/>
<path d="M19.5 14.51L19.51 14.4989" stroke="currentColor"  stroke-linecap="round" stroke-linejoin="round"/>
<path d="M13 18.2L16.5 17L22 19" stroke="currentColor"  stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2 10V3.6C2 3.26863 2.26863 3 2.6 3H8.77805C8.92127 3 9.05977 3.05124 9.16852 3.14445L12.3315 5.85555C12.4402 5.94876 12.5787 6 12.722 6H21.4C21.7314 6 22 6.26863 22 6.6V9M2 10V18.4C2 18.7314 2.26863 19 2.6 19H10M2 10H10" stroke="currentColor"  stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'people' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M1 20V19C1 15.134 4.13401 12 8 12V12C11.866 12 15 15.134 15 19V20" stroke="currentColor" stroke-linecap="round"/>
<path d="M13 14V14C13 11.2386 15.2386 9 18 9V9C20.7614 9 23 11.2386 23 14V14.5" stroke="currentColor" stroke-linecap="round"/>
<path d="M8 12C10.2091 12 12 10.2091 12 8C12 5.79086 10.2091 4 8 4C5.79086 4 4 5.79086 4 8C4 10.2091 5.79086 12 8 12Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18 9C19.6569 9 21 7.65685 21 6C21 4.34315 19.6569 3 18 3C16.3431 3 15 4.34315 15 6C15 7.65685 16.3431 9 18 9Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'news' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M14 14V6M14 14L20.1023 17.487C20.5023 17.7156 21 17.4268 21 16.9661V3.03391C21 2.57321 20.5023 2.28439 20.1023 2.51296L14 6M14 14H7C4.79086 14 3 12.2091 3 10V10C3 7.79086 4.79086 6 7 6H14" stroke="currentColor" stroke-width="1.5"/>
<path d="M7.75716 19.3001L7 14H11L11.6772 18.7401C11.8476 19.9329 10.922 21 9.71716 21C8.73186 21 7.8965 20.2755 7.75716 19.3001Z" stroke="currentColor" stroke-width="1.5"/>
</svg>',
            'info' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M12 11.5V16.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 7.51L12.01 7.49889" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'check' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M7 12.5L10 15.5L17 8.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'circle' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'expand' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M9 9L4 4M4 4V8M4 4H8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M15 9L20 4M20 4V8M20 4H16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9 15L4 20M4 20V16M4 20H8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M15 15L20 20M20 20V16M20 20H16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'collapse' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M20 20L15 15M15 15V19M15 15H19" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M4 20L9 15M9 15V19M9 15H5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M20 4L15 9M15 9V5M15 9H19" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M4 4L9 9M9 9V5M9 9H5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'prev' => '<svg class="iconoir" aria-hidden="true" viewBox="0 0 24 24" stroke-width="1.5" fill="none">
<path d="M3 12L21 12M21 12L12.5 3.5M21 12L12.5 20.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'next' => '<svg class="iconoir" aria-hidden="true" viewBox="0 0 24 24" stroke-width="1.5" fill="none">
<path d="M21 12L3 12M3 12L11.5 3.5M3 12L11.5 20.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'cancel' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M6.75827 17.2426L12.0009 12M17.2435 6.75736L12.0009 12M12.0009 12L6.75827 6.75736M12.0009 12L17.2435 17.2426" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'preview' => '<svg class="iconoir" aria-hidden="true" viewBox="0 0 24 24" stroke-width="1.5" fill="none">
<path d="M3 13C6.6 5 17.4 5 21 13" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 17C10.3431 17 9 15.6569 9 14C9 12.3431 10.3431 11 12 11C13.6569 11 15 12.3431 15 14C15 15.6569 13.6569 17 12 17Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            'save' => '<svg class="iconoir" aria-hidden="true" stroke-width="1.5" viewBox="0 0 24 24" fill="none">
<path d="M3 19V5C3 3.89543 3.89543 3 5 3H16.1716C16.702 3 17.2107 3.21071 17.5858 3.58579L20.4142 6.41421C20.7893 6.78929 21 7.29799 21 7.82843V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19Z" stroke="currentColor" stroke-width="1.5"/>
<path d="M8.6 9H15.4C15.7314 9 16 8.73137 16 8.4V3.6C16 3.26863 15.7314 3 15.4 3H8.6C8.26863 3 8 3.26863 8 3.6V8.4C8 8.73137 8.26863 9 8.6 9Z" stroke="currentColor" stroke-width="1.5"/>
<path d="M6 13.6V21H18V13.6C18 13.2686 17.7314 13 17.4 13H6.6C6.26863 13 6 13.2686 6 13.6Z" stroke="currentColor" stroke-width="1.5"/>
</svg>',
        ];
    }
    if (!isset($icons[$name])) return '';
    $svg = $icons[$name];
    $style = 'width:' . $size . 'px; height:' . $size . 'px; vertical-align:middle; flex-shrink:0;';
    if ($class !== '') {
        $svg = str_replace('class="iconoir"', 'class="iconoir ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"', $svg);
    }
    return str_replace('<svg ', '<svg style="' . $style . '" ', $svg);
}