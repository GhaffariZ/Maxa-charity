<?php

/**
 * Layout Footer - Multi-language support
 */

$locale = CURRENT_LOCALE ?? getLocale();
$direction = CURRENT_DIRECTION ?? getDirection($locale);
$alternateUrls = getAllLocaleUrls();

?>
    <!-- Footer -->
    <footer class="site-footer" role="contentinfo" dir="<?= htmlspecialchars($direction) ?>">
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    <!-- About -->
                    <div class="footer-column footer-about">
                        <div class="footer-logo">
                            <img src="/error pages/LOGO-MAXA.png" alt="<?= htmlspecialchars(__('meta.site_name')) ?>" width="120">
                        </div>
                        <p class="footer-description">
                            <?= __('footer.about_us') ?>
                        </p>
                        <div class="footer-social">
                            <a href="https://instagram.com/mahak_charity" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a href="https://twitter.com/mahak_charity" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg>
                            </a>
                            <a href="https://linkedin.com/company/mahak" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                            <a href="https://aparat.com/mahak_charity" target="_blank" rel="noopener noreferrer" aria-label="Aparat">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.5-11.5H7V7h9.5v1.5z"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="footer-column">
                        <h3 class="footer-title"><?= __('footer.quick_links') ?></h3>
                        <nav class="footer-nav" aria-label="<?= __('footer.quick_links') ?>">
                            <ul>
                                <li><a href="<?= buildLocalizedUrl($locale, '') ?>"><?= __('nav.home') ?></a></li>
                                <li><a href="<?= buildLocalizedUrl($locale, 'about') ?>"><?= __('nav.about') ?></a></li>
                                <li><a href="<?= buildLocalizedUrl($locale, 'campaigns') ?>"><?= __('nav.campaigns') ?></a></li>
                                <li><a href="<?= buildLocalizedUrl($locale, 'news') ?>"><?= __('nav.news') ?></a></li>
                                <li><a href="<?= buildLocalizedUrl($locale, 'branches') ?>"><?= __('nav.branches') ?></a></li>
                                <li><a href="<?= buildLocalizedUrl($locale, 'team') ?>"><?= __('nav.team') ?></a></li>
                                <li><a href="<?= buildLocalizedUrl($locale, 'transparency') ?>"><?= __('nav.transparency') ?></a></li>
                                <li><a href="<?= buildLocalizedUrl($locale, 'contact') ?>"><?= __('nav.contact') ?></a></li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Contact Info -->
                    <div class="footer-column">
                        <h3 class="footer-title"><?= __('footer.contact_info') ?></h3>
                        <address class="footer-address">
                            <p><strong><?= __('footer.central_office') ?></strong></p>
                            <p>Tehran, Iran</p>
                            <p><a href="tel:+982112345678">+98 21 1234 5678</a></p>
                            <p><a href="mailto:info@mahak-charity.org">info@mahak-charity.org</a></p>
                            <p><a href="<?= buildLocalizedUrl($locale, 'branches') ?>"><?= __('branch.branches_list') ?></a></p>
                        </address>
                    </div>

                    <!-- Newsletter -->
                    <div class="footer-column">
                        <h3 class="footer-title"><?= __('footer.newsletter') ?></h3>
                        <p><?= __('footer.newsletter') ?></p>
                        <form class="footer-newsletter" action="/newsletter-subscribe.php" method="POST">
                            <input type="hidden" name="locale" value="<?= htmlspecialchars($locale) ?>">
                            <div class="newsletter-input-group">
                                <input type="email" name="email" placeholder="<?= __('footer.newsletter_placeholder') ?>" required aria-label="<?= __('footer.newsletter_placeholder') ?>">
                                <button type="submit" class="btn btn-primary"><?= __('footer.subscribe') ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <p class="copyright">
                        &copy; <?= date('Y') ?> <?= htmlspecialchars(__('meta.site_name')) ?>. <?= __('footer.rights_reserved') ?>
                    </p>
                    <nav class="footer-legal" aria-label="Legal links">
                        <ul>
                            <li><a href="<?= buildLocalizedUrl($locale, 'privacy') ?>"><?= __('footer.privacy_policy') ?></a></li>
                            <li><a href="<?= buildLocalizedUrl($locale, 'terms') ?>"><?= __('footer.terms_of_service') ?></a></li>
                            <li><a href="<?= buildLocalizedUrl($locale, 'transparency') ?>"><?= __('footer.transparency_report') ?></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </footer>

    <!-- Hreflang alternate links (for crawlers that don't read head) -->
    <div style="display:none;" data-hreflang>
        <?php foreach ($alternateUrls as $altLocale => $altUrl): ?>
            <?php if ($altLocale !== 'x-default'): ?>
        <link rel="alternate" hreflang="<?= htmlspecialchars($altLocale) ?>" href="<?= htmlspecialchars($altUrl) ?>">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Scripts -->
    <script>
        // Locale and direction for client-side use
        window.MAHAK_LOCALE = '<?= htmlspecialchars($locale) ?>';
        window.MAHAK_DIRECTION = '<?= htmlspecialchars($direction) ?>';
        window.MAHAK_BASE_URL = '<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '')) ?>';
    </script>
    
    <!-- Main JS -->
    <script src="/assets/js/main.js" defer></script>
    
    <!-- Language switcher enhancement -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth transition for language switcher
            const langSwitcher = document.querySelector('.lang-switcher');
            if (langSwitcher) {
                langSwitcher.style.transition = 'opacity 0.2s ease';
            }
        });
    </script>
</body>
</html>