<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::settings()).
 *
 * @var array<string, string> $settings
 * @var string $section
 * @var bool $success
 */
?>

<?php if ($success): ?>
    <div class="flex items-center gap-4 flex-wrap bg-success-bg border border-success/25 text-success rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <span>Modifications enregistrées avec succès.</span>
    </div>
<?php endif; ?>

<div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col mb-6">
    <h2 class="text-base font-semibold mb-5 text-ink">Informations générales</h2>
    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">Définissez les informations de base du site.</p>

    <form method="POST" action="/admin/settings/general" enctype="multipart/form-data" style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <div style="flex: 2; min-width: 260px; display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <label for="site_name" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nom du site</label>
                <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'Toile') ?>" required
                    style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.4rem 0.9rem; font-family: var(--font-main);">
            </div>

            <div>
                <label for="site_description" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Description</label>
                <textarea id="site_description" name="site_description" rows="4"
                    style="width: 100%; border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 0.6rem 0.9rem; font-family: var(--font-main); resize: vertical;"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="contact_email" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Email de contact</label>
                <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>"
                    style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.4rem 0.9rem; font-family: var(--font-main);">
            </div>

            <div>
                <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
            </div>
        </div>

        <div style="flex: 1; min-width: 240px; display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <img
                    src="<?= !empty($settings['site_logo']) ? '/uploads/branding/' . htmlspecialchars($settings['site_logo']) : '/assets/images/site/logo-toile.png' ?>"
                    alt="Logo" style="width: 64px; height: 64px; object-fit: contain;">
                <div>
                    <label class="btn btn--primary" style="cursor: pointer;">
                        Changer le logo
                        <input type="file" name="site_logo" accept="image/png,image/jpeg,image/webp" style="display: none;">
                    </label>
                    <p style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.4rem;">
                        Formats acceptés : PNG, JPG, WEBP<br>Taille max : 2 Mo
                    </p>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <img
                    src="<?= !empty($settings['site_favicon']) ? '/uploads/branding/' . htmlspecialchars($settings['site_favicon']) : '/assets/images/site/favicon-logo.png' ?>"
                    alt="Favicon" style="width: 48px; height: 48px; object-fit: contain; border-radius: 50%;">
                <div>
                    <label class="btn btn--primary" style="cursor: pointer;">
                        Changer le favicon
                        <input type="file" name="site_favicon" accept="image/png,image/svg+xml,.ico" style="display: none;">
                    </label>
                    <p style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.4rem;">
                        Formats acceptés : PNG, SVG, ICO<br>Taille max : 512 Ko
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col mb-6">
    <h2 class="text-base font-semibold mb-5 text-ink">Réseaux sociaux</h2>
    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">Liens affichés dans le pied de page du site.</p>

    <form method="POST" action="/admin/settings/social" style="display: flex; flex-direction: column; gap: 1rem; max-width: 480px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <?php
        $socialFields = [
            'social_instagram' => 'Instagram',
            'social_facebook' => 'Facebook',
            'social_pinterest' => 'Pinterest',
            'social_tiktok' => 'TikTok',
        ];
        ?>
        <?php foreach ($socialFields as $key => $label): ?>
            <div>
                <label for="<?= $key ?>" style="display: block; font-weight: 600; margin-bottom: 0.4rem;"><?= $label ?></label>
                <input type="url" id="<?= $key ?>" name="<?= $key ?>" value="<?= htmlspecialchars($settings[$key] ?? '') ?>" placeholder="https://..."
                    style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.4rem 0.9rem; font-family: var(--font-main);">
            </div>
        <?php endforeach; ?>

        <div>
            <button type="submit" class="btn btn--primary">Enregistrer les réseaux sociaux</button>
        </div>
    </form>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col mb-6">
    <h2 class="text-base font-semibold mb-5 text-ink">Tirage au sort</h2>
    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">Prix des tickets et nombre de gagnants pour les deux tirages.</p>

    <form method="POST" action="/admin/settings/raffle" style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <div style="flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 1rem;">
            <strong>Vitrine boutiques (mensuel)</strong>
            <div>
                <label for="raffle_price" style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Prix du ticket (€)</label>
                <input type="number" id="raffle_price" name="raffle_price" step="0.01" min="0"
                    value="<?= number_format((int) ($settings['raffle_price'] ?? 300) / 100, 2, '.', '') ?>"
                    style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
            </div>
            <div>
                <label for="raffle_max_winners" style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Nombre de gagnants</label>
                <input type="number" id="raffle_max_winners" name="raffle_max_winners" min="1"
                    value="<?= (int) ($settings['raffle_max_winners'] ?? 10) ?>"
                    style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
            </div>
        </div>

        <div style="flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 1rem;">
            <strong>Page d'accueil (hebdomadaire)</strong>
            <div>
                <label for="raffle_homepage_price" style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Prix du ticket (€)</label>
                <input type="number" id="raffle_homepage_price" name="raffle_homepage_price" step="0.01" min="0"
                    value="<?= number_format((int) ($settings['raffle_homepage_price'] ?? 500) / 100, 2, '.', '') ?>"
                    style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
            </div>
            <div>
                <label for="raffle_homepage_winners" style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Nombre de gagnants</label>
                <input type="number" id="raffle_homepage_winners" name="raffle_homepage_winners" min="1"
                    value="<?= (int) ($settings['raffle_homepage_winners'] ?? 5) ?>"
                    style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
            </div>
        </div>

        <div style="flex-basis: 100%;">
            <button type="submit" class="btn btn--primary">Enregistrer les tirages</button>
        </div>
    </form>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
    <h2 class="text-base font-semibold mb-5 text-ink">Mode maintenance</h2>
    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">
        Bloque l'accès public au site (les administrateurs continuent d'y accéder pour le désactiver).
    </p>

    <form method="POST" action="/admin/settings/maintenance" style="display: flex; flex-direction: column; gap: 1rem; max-width: 480px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <label style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
            Activer le mode maintenance
        </label>

        <div>
            <label for="maintenance_message" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Message affiché aux visiteurs</label>
            <textarea id="maintenance_message" name="maintenance_message" rows="3"
                style="width: 100%; border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 0.6rem 0.9rem; font-family: var(--font-main); resize: vertical;"
            ><?= htmlspecialchars($settings['maintenance_message'] ?? "Le site est actuellement en maintenance, merci de revenir un peu plus tard.") ?></textarea>
        </div>

        <div>
            <button type="submit" class="btn btn--outline">Enregistrer le mode maintenance</button>
        </div>
    </form>
</div>
