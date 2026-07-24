<?php
/**
 * Footer partagé (public + admin). Variable définie par
 * layouts/default.php ou layouts/admin.php avant l'inclusion de ce
 * partial (require, pas de scope isolé).
 *
 * @var \App\Models\Setting $settingModel
 */
$footerLogo = ($logo = $settingModel->get('site_logo')) ? '/uploads/branding/' . htmlspecialchars($logo) : '/assets/images/site/logo-toile.png';
$socialLinks = [
    'instagram' => $settingModel->get('social_instagram'),
    'facebook' => $settingModel->get('social_facebook'),
    'pinterest' => $settingModel->get('social_pinterest'),
    'tiktok' => $settingModel->get('social_tiktok'),
];
?>
<?php if (\App\Core\Demo::isEnabled()): ?>
    <div class="bg-title text-white text-center text-[0.8rem] px-4 py-2 leading-snug">
        🎨 Version de démonstration — les paiements, l'envoi d'emails et la connexion Google sont désactivés.
    </div>
<?php endif; ?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__logo">
            <a href="/">
                <img src="<?= $footerLogo ?>" alt="<?= htmlspecialchars($settingModel->get('site_name', 'Toile')) ?>">
            </a>
            <p class="site-footer__tagline">Le marketplace des artistes</p>
        </div>

        <div class="site-footer__col">
            <h4>À propos</h4>
            <a href="/a-propos">Qui sommes-nous ?</a>
            <a href="/become-artist">Devenir Artiste</a>
            <a href="/contact" data-contact-open>Contact</a>
            <a href="/aide">Aide et FAQ</a>
        </div>

        <div class="site-footer__col">
            <h4>Suivez-nous</h4>
            <div class="site-footer__social">
                <a href="<?= htmlspecialchars($socialLinks['instagram'] ?: '#') ?>" aria-label="Instagram">
                    <img src="/assets/images/reseaux/instagram.png" alt="Instagram">
                </a>
                <a href="<?= htmlspecialchars($socialLinks['facebook'] ?: '#') ?>" aria-label="Facebook">
                    <img src="/assets/images/reseaux/facebook.png" alt="Facebook">
                </a>
                <a href="<?= htmlspecialchars($socialLinks['pinterest'] ?: '#') ?>" aria-label="Pinterest">
                    <img src="/assets/images/reseaux/pinterest.png" alt="Pinterest">
                </a>
                <a href="<?= htmlspecialchars($socialLinks['tiktok'] ?: '#') ?>" aria-label="TikTok">
                    <img src="/assets/images/reseaux/tiktok.png" alt="TikTok" class="site-footer__social-tiktok">
                </a>
            </div>
        </div>
    </div>

    <div class="site-footer__bottom">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($settingModel->get('site_name', 'Toile')) ?> — La marketplace de commissions artistiques.</p>

        <nav class="site-footer__legal">
            <a href="/cgu">Conditions d'utilisation</a>
            <a href="/cgv">Conditions générales de vente</a>
            <a href="/confidentialite">Politique de confidentialité</a>
            <a href="/reglement-interieur">Règlement intérieur</a>
        </nav>
    </div>
</footer>
