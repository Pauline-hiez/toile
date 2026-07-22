<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::settings()).
 *
 * @var array<string, string> $settings
 * @var array $plans
 * @var string $section
 * @var bool $success
 * @var array<int, array{name: string, image: string|null}> $homepageStyleCandidates Styles fixes + validés (voir Shop::STYLES / CategoryRequest::findApprovedStyleRows()).
 * @var array<int, string> $homepageStyleSelected Noms actuellement choisis pour la page d'accueil (max 5).
 */
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<?php

$tabs = [
    'general' => 'Général',
    'social' => 'Réseaux sociaux',
    'raffle' => 'Tirage au sort',
    'subscriptions' => 'Abonnements',
    'homepage_styles' => 'Styles à la une',
    'maintenance' => 'Maintenance',
];

// Onglet actif par défaut : celui visé par la redirection après
// enregistrement d'un formulaire (?section=...), sinon "Général".
$activeTab = array_key_exists($section, $tabs) ? $section : 'general';
?>

<?php if ($success): ?>
    <div class="flex items-center gap-4 flex-wrap bg-success-bg border border-success/25 text-success rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <span>Modifications enregistrées avec succès.</span>
    </div>
<?php endif; ?>

<div class="flex items-center gap-2 mb-6 flex-wrap">
    <?php foreach ($tabs as $key => $label): ?>
        <button type="button" data-settings-tab="<?= $key ?>"
            class="settings-tab-btn inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium transition-colors <?= $activeTab === $key ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">
            <?= htmlspecialchars($label) ?>
        </button>
    <?php endforeach; ?>
</div>

<div data-settings-panel="general" class="<?= $activeTab === 'general' ? '' : 'hidden' ?>">
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
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
</div>

<div data-settings-panel="social" class="<?= $activeTab === 'social' ? '' : 'hidden' ?>">
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
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
</div>

<div data-settings-panel="raffle" class="<?= $activeTab === 'raffle' ? '' : 'hidden' ?>">
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
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
</div>

<div data-settings-panel="subscriptions" class="<?= $activeTab === 'subscriptions' ? '' : 'hidden' ?>">
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
        <h2 class="text-base font-semibold mb-5 text-ink">Abonnements</h2>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">Prix affiché et caractéristiques de chaque formule.</p>

        <form method="POST" action="/admin/settings/subscriptions" style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

            <?php foreach ($plans as $plan): ?>
                <div style="flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 1rem;">
                    <strong><?= htmlspecialchars($plan['name']) ?></strong>

                    <div>
                        <label style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Prix mensuel (€)</label>
                        <?php if ($plan['name'] === 'Commission'): ?>
                            <input type="text" value="Gratuit" disabled
                                style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem; background: var(--color-bg); color: var(--color-text-muted);">
                        <?php else: ?>
                            <input type="number" name="plan[<?= (int) $plan['id'] ?>][price]" step="0.01" min="0"
                                value="<?= number_format($plan['price'] / 100, 2, '.', '') ?>"
                                style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
                        <?php endif; ?>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Commission (%)</label>
                        <input type="number" name="plan[<?= (int) $plan['id'] ?>][commission_rate]" step="0.01" min="0" max="100"
                            value="<?= rtrim(rtrim(number_format((float) $plan['commission_rate'], 2), '0'), '.') ?>"
                            style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Prestations max (9999 = illimité)</label>
                        <input type="number" name="plan[<?= (int) $plan['id'] ?>][max_services]" min="1"
                            value="<?= (int) $plan['max_services'] ?>"
                            style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Photos portfolio max (9999 = illimité)</label>
                        <input type="number" name="plan[<?= (int) $plan['id'] ?>][max_portfolio_images]" min="1"
                            value="<?= (int) $plan['max_portfolio_images'] ?>"
                            style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; margin-bottom: 0.3rem;">Options par prestation max (9999 = illimité)</label>
                        <input type="number" name="plan[<?= (int) $plan['id'] ?>][max_options_per_service]" min="0"
                            value="<?= (int) $plan['max_options_per_service'] ?>"
                            style="width: 100%; border: 1px solid var(--color-border); border-radius: 9999px; padding: 0.35rem 0.75rem;">
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="flex-basis: 100%;">
                <button type="submit" class="btn btn--primary">Enregistrer les abonnements</button>
            </div>
        </form>
    </div>
</div>

<div data-settings-panel="homepage_styles" class="<?= $activeTab === 'homepage_styles' ? '' : 'hidden' ?>">
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
        <div class="flex items-center gap-2 mb-2">
            <h2 class="text-base font-semibold text-ink">Styles à la une</h2>
            <button type="button" id="newCategoryOpenBtn" title="Ajouter un nouveau style ou type"
                class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-base leading-none border-0 cursor-pointer hover:bg-primary/90 transition-colors">+</button>
        </div>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">
            Choisis jusqu'à 5 styles à afficher dans la section "Explorez l'univers de la création" de la page d'accueil. Les autres styles restent accessibles depuis la recherche.
        </p>

        <?php
        $candidatesByName = [];
        foreach ($homepageStyleCandidates as $candidate) {
            $candidatesByName[$candidate['name']] = $candidate;
        }
        $availableCandidates = array_filter($homepageStyleCandidates, fn($c) => !in_array($c['name'], $homepageStyleSelected, true));
        ?>

        <form method="POST" action="/admin/settings/homepage-styles" id="homepageStylesForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
            <div data-hidden-inputs></div>

            <p data-homepage-styles-counter style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem;">
                <?= count($homepageStyleSelected) ?>/5 sélectionnés
            </p>
            <p style="font-size: 0.78rem; color: var(--color-text-muted); margin-bottom: 0.75rem;">Styles affichés, dans cet ordre — utilise les flèches pour réordonner, ✕ pour retirer.</p>

            <div data-selected-list style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.5rem;">
                <?php foreach ($homepageStyleSelected as $name): ?>
                    <?php if (!isset($candidatesByName[$name])) continue; ?>
                    <?php $candidate = $candidatesByName[$name]; ?>
                    <div data-selected-item data-name="<?= htmlspecialchars($name) ?>" data-image="<?= htmlspecialchars($candidate['image'] ?? '') ?>" data-request-id="<?= isset($candidate['requestId']) ? (int) $candidate['requestId'] : '' ?>" style="display: flex; align-items: center; gap: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.5rem 0.75rem; cursor: grab; touch-action: none;">
                        <span data-drag-handle title="Glisser pour réordonner" style="color: var(--color-text-muted); flex-shrink: 0;">⠿</span>
                        <span style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: var(--color-primary-light); overflow: hidden; flex-shrink: 0; pointer-events: none;">
                            <?php if ($candidate['image']): ?>
                                <img src="<?= htmlspecialchars($candidate['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            <?php endif; ?>
                        </span>
                        <span style="flex: 1; min-width: 0; font-size: 0.85rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; pointer-events: none;"><?= htmlspecialchars(ucfirst($name)) ?></span>
                        <?php if (isset($candidate['requestId'])): ?>
                            <button type="button" title="Modifier" data-edit-style-open
                                data-id="<?= (int) $candidate['requestId'] ?>" data-name="<?= htmlspecialchars($name) ?>" data-image="<?= htmlspecialchars($candidate['image'] ?? '') ?>"
                                style="width: 24px; height: 24px; border-radius: 50%; background: var(--color-bg); border: 1px solid var(--color-border); cursor: pointer; font-size: 0.7rem; flex-shrink: 0;">✎</button>
                        <?php endif; ?>
                        <button type="button" data-remove-selected title="Retirer" style="width: 24px; height: 24px; border-radius: 50%; background: var(--color-danger-bg); color: var(--color-danger); border: 1px solid var(--color-border); cursor: pointer; font-size: 0.7rem; flex-shrink: 0;">✕</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <p style="font-size: 0.82rem; font-weight: 600; margin-bottom: 0.75rem;">Autres styles disponibles</p>

            <div data-available-list style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <?php foreach ($availableCandidates as $candidate): ?>
                    <div data-available-item data-name="<?= htmlspecialchars($candidate['name']) ?>" data-image="<?= htmlspecialchars($candidate['image'] ?? '') ?>" data-request-id="<?= isset($candidate['requestId']) ? (int) $candidate['requestId'] : '' ?>" style="border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden;">
                        <div style="height: 90px; background: var(--color-primary-light); position: relative;">
                            <?php if ($candidate['image']): ?>
                                <img src="<?= htmlspecialchars($candidate['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            <?php endif; ?>

                            <?php if (isset($candidate['requestId'])): ?>
                                <div style="position: absolute; top: 6px; left: 6px; display: flex; gap: 4px;">
                                    <button type="button" title="Modifier" data-edit-style-open
                                        data-id="<?= (int) $candidate['requestId'] ?>" data-name="<?= htmlspecialchars($candidate['name']) ?>" data-image="<?= htmlspecialchars($candidate['image'] ?? '') ?>"
                                        style="width: 22px; height: 22px; border-radius: 50%; background: white; border: 1px solid var(--color-border); cursor: pointer; font-size: 0.7rem; line-height: 1;">✎</button>
                                    <button type="submit" form="deleteStyleForm<?= (int) $candidate['requestId'] ?>" title="Supprimer"
                                        onclick="return confirm('Supprimer le style « <?= htmlspecialchars(addslashes($candidate['name'])) ?> » ?');"
                                        style="width: 22px; height: 22px; border-radius: 50%; background: white; border: 1px solid var(--color-border); cursor: pointer; font-size: 0.7rem; line-height: 1;">🗑</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 0.5rem 0.7rem;">
                            <div style="font-size: 0.82rem; font-weight: 600; margin-bottom: 0.4rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars(ucfirst($candidate['name'])) ?></div>
                            <button type="button" data-add-selected style="width: 100%; font-size: 0.78rem; padding: 0.25rem 0; border-radius: var(--radius-sm); background: var(--color-bg); border: 1px solid var(--color-border); cursor: pointer;">+ Ajouter</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn--primary">Enregistrer la sélection</button>
        </form>

        <?php foreach ($homepageStyleCandidates as $candidate): ?>
            <?php if (isset($candidate['requestId'])): ?>
                <form id="deleteStyleForm<?= (int) $candidate['requestId'] ?>" method="POST" action="/admin/category-requests/<?= (int) $candidate['requestId'] ?>/delete" style="display: none;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                </form>
            <?php endif; ?>
        <?php endforeach; ?>

        <dialog id="editStyleModal" class="auth-modal">
            <button type="button" class="absolute top-3 right-4 text-title text-3xl leading-none z-20" data-edit-style-close aria-label="Fermer">&times;</button>

            <div class="relative bg-bg rounded-2xl border border-border shadow-lg px-6 py-8 min-[481px]:px-10 min-[481px]:py-10">
                <h2 class="font-title text-[1.6rem] text-title font-semibold text-center leading-none mb-5">Modifier le style</h2>

                <form id="editStyleForm" method="POST" action="" enctype="multipart/form-data" class="flex flex-col gap-4 max-w-[340px] mx-auto text-left">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

                    <div>
                        <label for="editStyleName" class="block font-semibold text-[0.9rem] mb-2">Nom</label>
                        <input type="text" id="editStyleName" name="name" required
                            class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
                    </div>

                    <div>
                        <label class="block font-semibold text-[0.9rem] mb-2">Visuel</label>
                        <button type="button" id="editStyleRecropBtn" class="btn btn--outline" style="font-size: 0.82rem; padding: 0.3rem 0.9rem;">Ajuster le cadrage de l'image actuelle</button>
                        <p class="text-[0.78rem] text-muted mt-2 mb-1">Ou remplace-la par un nouveau fichier :</p>
                        <input type="file" id="editStyleImage" name="image" accept="image/png,image/jpeg,image/webp" class="w-full text-[0.85rem]">
                        <img id="editStyleImagePreview" src="" alt="" class="hidden w-24 h-24 object-cover rounded-md border border-border mt-2">
                    </div>

                    <div class="text-center mt-1">
                        <button type="submit" class="btn btn--primary px-10">Enregistrer</button>
                    </div>
                </form>
            </div>
        </dialog>

        <dialog id="editStyleCropModal" class="auth-modal">
            <div class="bg-white rounded-md p-5 shadow-sm">
                <h3 class="text-base font-semibold text-ink mb-1">Recadre le visuel</h3>
                <p class="text-[0.8rem] text-muted mb-4">Déplace et zoome ton image pour bien centrer le sujet.</p>

                <div class="relative w-full h-[420px] bg-bg overflow-hidden mb-4">
                    <img id="editStyleCropImage" src="" alt="" class="block max-w-full">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" id="editStyleCropCancel" class="btn btn--outline">Annuler</button>
                    <button type="button" id="editStyleCropConfirm" class="btn btn--primary">Valider le cadrage</button>
                </div>
            </div>
        </dialog>

        <dialog id="newCategoryModal" class="auth-modal"<?= isset($_GET['category_error']) ? ' data-new-category-reopen' : '' ?>>
            <button type="button" class="absolute top-3 right-4 text-title text-3xl leading-none z-20" data-new-category-close aria-label="Fermer">&times;</button>

            <div class="relative bg-bg rounded-2xl border border-border shadow-lg px-6 py-8 min-[481px]:px-10 min-[481px]:py-10">
                <h2 class="font-title text-[1.6rem] text-title font-semibold text-center leading-none mb-5">Ajouter une catégorie</h2>

                <?php if (isset($_GET['category_error'])): ?>
                    <p class="text-danger text-[0.8rem] text-center mb-4">Vérifie les champs (nom, visuel pour un style).</p>
                <?php endif; ?>

                <form method="POST" action="/admin/category-requests" enctype="multipart/form-data" class="flex flex-col gap-4 max-w-[340px] mx-auto text-left">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

                    <div>
                        <label for="newCategoryType" class="block font-semibold text-[0.9rem] mb-2">Catégorie</label>
                        <select id="newCategoryType" name="category_type" data-new-category-type
                            class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
                            <option value="style">Nouveau style</option>
                            <option value="type">Nouveau type / spécialité</option>
                        </select>
                    </div>

                    <div>
                        <label for="newCategoryName" class="block font-semibold text-[0.9rem] mb-2">Nom</label>
                        <input type="text" id="newCategoryName" name="name" required
                            class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
                    </div>

                    <div data-new-category-image>
                        <label for="newCategoryImage" class="block font-semibold text-[0.9rem] mb-2">Visuel</label>
                        <input type="file" id="newCategoryImage" name="image" accept="image/png,image/jpeg,image/webp" class="w-full text-[0.85rem]">
                        <img id="newCategoryImagePreview" src="" alt="" class="hidden w-24 h-24 object-cover rounded-md border border-border mt-2">
                    </div>

                    <div class="text-center mt-1">
                        <button type="submit" class="btn btn--primary px-10">Ajouter</button>
                    </div>
                </form>
            </div>
        </dialog>

        <dialog id="newCategoryCropModal" class="auth-modal">
            <div class="bg-white rounded-md p-5 shadow-sm">
                <h3 class="text-base font-semibold text-ink mb-1">Recadre ton visuel</h3>
                <p class="text-[0.8rem] text-muted mb-4">Déplace et zoome ton image pour bien centrer le sujet.</p>

                <div class="relative w-full h-[420px] bg-bg overflow-hidden mb-4">
                    <img id="newCategoryCropImage" src="" alt="" class="block max-w-full">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" id="newCategoryCropCancel" class="btn btn--outline">Annuler</button>
                    <button type="button" id="newCategoryCropConfirm" class="btn btn--primary">Valider le cadrage</button>
                </div>
            </div>
        </dialog>
    </div>
</div>

<div data-settings-panel="maintenance" class="<?= $activeTab === 'maintenance' ? '' : 'hidden' ?>">
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
        <h2 class="text-base font-semibold mb-5 text-ink">Mode maintenance</h2>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">
            Bloque l'accès public au site (les administrateurs continuent d'y accéder pour le désactiver).
        </p>

        <form method="POST" action="/admin/settings/maintenance" style="display: flex; flex-direction: column; gap: 1rem; max-width: 480px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

            <?php
            $maintenanceDurationTotal = (int) ($settings['maintenance_duration_minutes'] ?? 0);
            $maintenanceDurationHours = intdiv($maintenanceDurationTotal, 60);
            $maintenanceDurationMinutes = $maintenanceDurationTotal % 60;
            $maintenanceStartsAt = $settings['maintenance_starts_at'] ?? '';
            $maintenanceHasStarted = $maintenanceStartsAt !== '' && strtotime($maintenanceStartsAt) <= time();
            ?>

            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                Activer le mode maintenance
            </label>

            <?php if (($settings['maintenance_mode'] ?? '0') === '1' && $maintenanceStartsAt !== ''): ?>
                <p style="font-size: 0.85rem; font-weight: 600; color: <?= $maintenanceHasStarted ? 'var(--color-danger)' : 'var(--color-primary)' ?>;">
                    <?= $maintenanceHasStarted
                        ? 'En cours depuis le ' . \App\Core\FrenchDate::format('d MMMM y à HH:mm', $maintenanceStartsAt)
                        : 'Programmée pour le ' . \App\Core\FrenchDate::format('d MMMM y à HH:mm', $maintenanceStartsAt) ?>
                </p>
            <?php endif; ?>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Durée de la maintenance</label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="number" name="maintenance_duration_hours" min="0" value="<?= $maintenanceDurationHours ?>"
                        style="width: 80px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 0.5rem 0.7rem; font-family: var(--font-main);">
                    <span style="color: var(--color-text-muted); font-size: 0.85rem;">heures</span>
                    <input type="number" name="maintenance_duration_minutes" min="0" max="59" value="<?= $maintenanceDurationMinutes ?>"
                        style="width: 80px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 0.5rem 0.7rem; font-family: var(--font-main);">
                    <span style="color: var(--color-text-muted); font-size: 0.85rem;">minutes</span>
                </div>
                <p style="color: var(--color-text-muted); font-size: 0.8rem; margin-top: 0.4rem;">
                    Affiche un compte à rebours en temps réel aux visiteurs. Laisse à 0 pour n'afficher aucun compte à rebours.
                </p>
            </div>

            <?php $maintenanceIsScheduled = ($settings['maintenance_trigger_mode'] ?? 'now') === 'scheduled'; ?>
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Déclenchement</label>
                <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 400;">
                        <input type="radio" name="maintenance_trigger" value="now" data-maintenance-trigger-radio <?= !$maintenanceIsScheduled ? 'checked' : '' ?>>
                        Lancer maintenant, dès l'enregistrement
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 400;">
                        <input type="radio" name="maintenance_trigger" value="scheduled" data-maintenance-trigger-radio <?= $maintenanceIsScheduled ? 'checked' : '' ?>>
                        Programmer pour plus tard
                    </label>
                </div>

                <div data-maintenance-schedule-field class="<?= $maintenanceIsScheduled ? '' : 'hidden' ?>" style="margin-top: 0.6rem;">
                    <input type="datetime-local" id="maintenance_starts_at" name="maintenance_starts_at"
                        value="<?= $maintenanceStartsAt !== '' ? htmlspecialchars(str_replace(' ', 'T', substr($maintenanceStartsAt, 0, 16))) : '' ?>"
                        style="border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 0.5rem 0.9rem; font-family: var(--font-main);">
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn--outline">Enregistrer le mode maintenance</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var buttons = document.querySelectorAll('[data-settings-tab]');
        var panels = document.querySelectorAll('[data-settings-panel]');

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.dataset.settingsTab;

                buttons.forEach(function (b) {
                    var isActive = b === btn;
                    b.classList.toggle('bg-primary', isActive);
                    b.classList.toggle('text-white', isActive);
                    b.classList.toggle('border-primary', isActive);
                    b.classList.toggle('bg-white', !isActive);
                    b.classList.toggle('text-ink', !isActive);
                    b.classList.toggle('border-border', !isActive);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.dataset.settingsPanel !== target);
                });
            });
        });
    })();

    (function () {
        var form = document.getElementById('homepageStylesForm');
        var selectedList = document.querySelector('[data-selected-list]');
        var availableList = document.querySelector('[data-available-list]');
        var hiddenInputsContainer = document.querySelector('[data-hidden-inputs]');
        var counter = document.querySelector('[data-homepage-styles-counter]');
        if (!form || !selectedList || !availableList || !hiddenInputsContainer) return;

        function selectedItems() {
            return Array.prototype.slice.call(selectedList.querySelectorAll('[data-selected-item]'));
        }

        // Régénère les <input type="hidden" name="homepage_styles[]"> à
        // partir de l'ordre actuel des cartes dans #data-selected-list —
        // c'est ÇA, et uniquement ça, qui part au serveur au clic sur
        // "Enregistrer la sélection".
        function syncHiddenInputs() {
            hiddenInputsContainer.innerHTML = '';
            selectedItems().forEach(function (item) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'homepage_styles[]';
                input.value = item.dataset.name;
                hiddenInputsContainer.appendChild(input);
            });

            if (counter) counter.textContent = selectedItems().length + '/5 sélectionnés';
            availableList.querySelectorAll('[data-add-selected]').forEach(function (btn) {
                btn.disabled = selectedItems().length >= 5;
            });
        }

        function buildSelectedItem(name, image, requestId) {
            var div = document.createElement('div');
            div.setAttribute('data-selected-item', '');
            div.dataset.name = name;
            div.dataset.image = image || '';
            div.dataset.requestId = requestId || '';
            div.style.cssText = 'display:flex;align-items:center;gap:0.75rem;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:0.5rem 0.75rem;cursor:grab;touch-action:none;';

            var handle = '<span data-drag-handle title="Glisser pour réordonner" style="color:var(--color-text-muted);flex-shrink:0;">⠿</span>';
            var thumb = '<span style="width:48px;height:48px;border-radius:var(--radius-sm);background:var(--color-primary-light);overflow:hidden;flex-shrink:0;pointer-events:none;">' +
                (image ? '<img src="' + image + '" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">' : '') + '</span>';
            var nameSpan = '<span style="flex:1;min-width:0;font-size:0.85rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;pointer-events:none;">' + (name.charAt(0).toUpperCase() + name.slice(1)) + '</span>';
            var editBtn = requestId ? '<button type="button" title="Modifier" data-edit-style-open data-id="' + requestId + '" data-name="' + name + '" data-image="' + image + '" style="width:24px;height:24px;border-radius:50%;background:var(--color-bg);border:1px solid var(--color-border);cursor:pointer;font-size:0.7rem;flex-shrink:0;">✎</button>' : '';
            var removeBtn = '<button type="button" data-remove-selected title="Retirer" style="width:24px;height:24px;border-radius:50%;background:var(--color-danger-bg);color:var(--color-danger);border:1px solid var(--color-border);cursor:pointer;font-size:0.7rem;flex-shrink:0;">✕</button>';

            div.innerHTML = handle + thumb + nameSpan + editBtn + removeBtn;
            return div;
        }

        function buildAvailableItem(name, image, requestId) {
            var div = document.createElement('div');
            div.setAttribute('data-available-item', '');
            div.dataset.name = name;
            div.dataset.image = image || '';
            div.dataset.requestId = requestId || '';
            div.style.cssText = 'border:1px solid var(--color-border);border-radius:var(--radius-md);overflow:hidden;';

            var img = '<div style="height:90px;background:var(--color-primary-light);position:relative;">' +
                (image ? '<img src="' + image + '" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">' : '') + '</div>';
            var label = '<div style="padding:0.5rem 0.7rem;"><div style="font-size:0.82rem;font-weight:600;margin-bottom:0.4rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (name.charAt(0).toUpperCase() + name.slice(1)) + '</div>' +
                '<button type="button" data-add-selected style="width:100%;font-size:0.78rem;padding:0.25rem 0;border-radius:var(--radius-sm);background:var(--color-bg);border:1px solid var(--color-border);cursor:pointer;">+ Ajouter</button></div>';

            div.innerHTML = img + label;
            return div;
        }

        selectedList.addEventListener('click', function (e) {
            var item = e.target.closest('[data-selected-item]');
            if (!item) return;

            if (e.target.matches('[data-remove-selected]')) {
                availableList.appendChild(buildAvailableItem(item.dataset.name, item.dataset.image, item.dataset.requestId));
                item.remove();
                syncHiddenInputs();
            }
        });

        // Glisser-déposer (Pointer Events, pas l'API drag and drop native)
        // pour réordonner la liste des styles affichés — la même
        // fonction syncHiddenInputs() que les autres actions (déjà
        // vérifiée fonctionnelle) régénère les champs cachés à la fin.
        (function () {
            var dragged = null;

            function onPointerMove(e) {
                if (!dragged) return;

                var items = Array.prototype.slice.call(selectedList.querySelectorAll('[data-selected-item]')).filter(function (i) {
                    return i !== dragged;
                });

                var closest = null;
                var closestDistance = Infinity;
                items.forEach(function (item) {
                    var box = item.getBoundingClientRect();
                    var centerY = box.top + box.height / 2;
                    var distance = Math.abs(e.clientY - centerY);
                    if (distance < closestDistance) {
                        closestDistance = distance;
                        closest = { item: item, after: e.clientY > centerY };
                    }
                });

                if (!closest) return;
                if (closest.after) {
                    closest.item.after(dragged);
                } else {
                    closest.item.before(dragged);
                }
            }

            function onPointerUp() {
                if (dragged) {
                    dragged.style.opacity = '';
                    syncHiddenInputs();
                }
                dragged = null;
                document.removeEventListener('pointermove', onPointerMove);
                document.removeEventListener('pointerup', onPointerUp);
            }

            selectedList.addEventListener('pointerdown', function (e) {
                if (!e.target.closest('[data-drag-handle]')) return;

                var item = e.target.closest('[data-selected-item]');
                if (!item) return;

                e.preventDefault();
                dragged = item;
                item.style.opacity = '0.4';
                document.addEventListener('pointermove', onPointerMove);
                document.addEventListener('pointerup', onPointerUp);
            });
        })();

        availableList.addEventListener('click', function (e) {
            if (!e.target.matches('[data-add-selected]')) return;
            if (selectedItems().length >= 5) return;

            var item = e.target.closest('[data-available-item]');
            if (!item) return;

            selectedList.appendChild(buildSelectedItem(item.dataset.name, item.dataset.image, item.dataset.requestId));
            item.remove();
            syncHiddenInputs();
        });

        // Diagnostic : montre exactement ce qui part au serveur au clic sur
        // "Enregistrer la sélection", pour vérifier que l'ordre visible à
        // l'écran correspond bien à ce qui est réellement soumis.
        form.addEventListener('submit', function () {
            var order = selectedItems().map(function (item) { return item.dataset.name; });
            console.log('[Styles à la une] Ordre envoyé au serveur :', order);
        });

        syncHiddenInputs();
    })();

    (function () {
        var dialog = document.getElementById('editStyleModal');
        if (!dialog) return;

        var form = document.getElementById('editStyleForm');
        var nameInput = document.getElementById('editStyleName');
        var closeBtn = dialog.querySelector('[data-edit-style-close]');
        var recropBtn = document.getElementById('editStyleRecropBtn');
        var currentImageUrl = '';

        document.querySelectorAll('[data-edit-style-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                form.action = '/admin/category-requests/' + trigger.dataset.id + '/edit';
                nameInput.value = trigger.dataset.name;
                currentImageUrl = trigger.dataset.image || '';
                if (recropBtn) recropBtn.style.display = currentImageUrl ? '' : 'none';
                dialog.showModal();
            });
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dialog.close();
            });
        }

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                dialog.close();
            }
        });

        if (recropBtn) {
            recropBtn.addEventListener('click', function () {
                if (currentImageUrl && window.editStyleCropTool) {
                    window.editStyleCropTool.openWithUrl(currentImageUrl);
                }
            });
        }
    })();
</script>

<script src="/assets/js/image-crop.js"></script>
<script>
    window.editStyleCropTool = setupImageCrop({
        fileInputId: 'editStyleImage',
        modalId: 'editStyleCropModal',
        imageId: 'editStyleCropImage',
        confirmId: 'editStyleCropConfirm',
        cancelId: 'editStyleCropCancel',
        previewId: 'editStyleImagePreview',
        aspectRatio: 4 / 3,
        outputWidth: 800,
        outputHeight: 600,
    });

    setupImageCrop({
        fileInputId: 'newCategoryImage',
        modalId: 'newCategoryCropModal',
        imageId: 'newCategoryCropImage',
        confirmId: 'newCategoryCropConfirm',
        cancelId: 'newCategoryCropCancel',
        previewId: 'newCategoryImagePreview',
        aspectRatio: 4 / 3,
        outputWidth: 800,
        outputHeight: 600,
    });
</script>

<script>
    (function () {
        var dialog = document.getElementById('newCategoryModal');
        var openBtn = document.getElementById('newCategoryOpenBtn');
        if (!dialog || !openBtn) return;

        var closeBtn = dialog.querySelector('[data-new-category-close]');
        var typeSelect = document.getElementById('newCategoryType');
        var imageField = document.querySelector('[data-new-category-image]');

        function toggleImageField() {
            if (!typeSelect || !imageField) return;
            imageField.style.display = typeSelect.value === 'style' ? '' : 'none';
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', toggleImageField);
            toggleImageField();
        }

        openBtn.addEventListener('click', function () {
            dialog.showModal();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dialog.close();
            });
        }

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                dialog.close();
            }
        });

        if (dialog.hasAttribute('data-new-category-reopen')) {
            dialog.showModal();
        }
    })();
</script>

<script>
    (function () {
        var radios = document.querySelectorAll('[data-maintenance-trigger-radio]');
        var scheduleField = document.querySelector('[data-maintenance-schedule-field]');
        if (!radios.length || !scheduleField) return;

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                scheduleField.classList.toggle('hidden', this.value !== 'scheduled');
            });
        });
    })();
</script>
