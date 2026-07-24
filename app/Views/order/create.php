<?php
$pageTitle = 'Commander : ' . htmlspecialchars($service['title']) . ' — Toile';

// Pré-remplissage de l'adresse de facturation : les valeurs soumises en cas
// d'erreur (fournies par store()), sinon l'adresse par défaut du profil
// (fournie par create()). Robuste si non passé.
$billingPrefill = $billingPrefill ?? [];
$bp = fn(string $k) => htmlspecialchars($billingPrefill[$k] ?? '');
?>

<div class="relative overflow-hidden">
    <div class="max-w-[640px] mx-auto px-5 min-[641px]:px-10 py-6">
        <div class="relative z-0 mb-1">
            <img src="/assets/images/decor/envoyer-demande.png" alt="" class="hidden min-[1024px]:block absolute -top-8 -right-52 w-[240px] h-auto pointer-events-none select-none opacity-90 z-10">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="font-title text-title text-shine text-[1.5rem] min-[641px]:text-[1.8rem] font-semibold leading-tight">Commander</h1>
                <?php if (!empty($shop['accepts_quotes'])): ?>
                    <span class="<?= \App\Core\Badge::classes('info') ?>">Devis possible</span>
                <?php endif; ?>
            </div>
        </div>
        <p class="text-[0.85rem] text-muted mb-5">
            Boutique : <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="text-primary hover:underline"><?= htmlspecialchars($shop['name']) ?></a>
        </p>

        <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-4 mb-5 flex flex-col min-[560px]:flex-row gap-4">
            <?php if (!empty($service['image'])): ?>
                <img src="/uploads/services/<?= htmlspecialchars($service['image']) ?>" alt="" class="w-full min-[560px]:w-40 h-[160px] object-cover rounded-xl border border-border shrink-0">
            <?php else: ?>
                <div class="w-full min-[560px]:w-40 h-[160px] rounded-xl border border-border bg-bg flex items-center justify-center shrink-0">
                    <img src="/assets/images/icones/prestations.png" alt="" class="w-14 h-14 object-contain opacity-60">
                </div>
            <?php endif; ?>
            <div class="flex-1">
                <h2 class="font-cursive text-[1.15rem] font-semibold text-ink leading-tight mb-1"><?= htmlspecialchars($service['title']) ?></h2>
                <p class="text-[0.8rem] text-muted mb-2">
                    à partir de <strong class="text-ink"><?= number_format($service['base_price'] / 100, 2) ?> €</strong>
                    · délai estimé : <?= (int) $service['delivery_days'] ?> jours
                </p>
                <?php if (!empty($service['description'])): ?>
                    <p class="text-[0.8rem] text-ink leading-[1.5]"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" action="/commander" enctype="multipart/form-data" class="flex flex-col gap-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
            <input type="hidden" name="service_id" value="<?= $service['id'] ?>">

            <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-5 flex flex-col gap-4">
                <img src="/assets/images/decor/trousse.png" alt="" class="hidden min-[1024px]:block absolute -left-28 -bottom-10 w-[210px] h-auto pointer-events-none select-none opacity-90 -z-10">
                <img src="/assets/images/decor/tache7.png" alt="" class="hidden min-[1024px]:block absolute -top-28 -right-28 w-[260px] h-auto pointer-events-none select-none opacity-30 -z-10">
                <div>
                    <label for="title" class="block text-[0.85rem] font-semibold text-ink mb-1">Titre de ton projet</label>
                    <input type="text" id="title" name="title" placeholder="Ex : Portrait de mon personnage" required
                        class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                    <?php if (isset($errors['title'])): ?>
                        <p class="text-danger text-[0.78rem] mt-1"><?= htmlspecialchars($errors['title']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="description" class="block text-[0.85rem] font-semibold text-ink mb-1">Décris ton idée en détail</label>
                    <textarea id="description" name="description" rows="6" required
                        class="w-full bg-white border border-border rounded-2xl px-4 py-3 font-main text-[0.9rem] outline-none focus:border-primary resize-none"></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <p class="text-danger text-[0.78rem] mt-1"><?= htmlspecialchars($errors['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

        <?php if (!empty($basesGrouped)): ?>
            <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-5">
                <img src="/assets/images/decor/tache7.png" alt="" class="hidden min-[1024px]:block absolute -right-20 -bottom-16 w-[200px] h-auto pointer-events-none select-none opacity-40 z-10">
                <img src="/assets/images/decor/plante9.png" alt="" class="hidden min-[1024px]:block absolute -right-10 -bottom-10 w-[120px] h-auto pointer-events-none select-none opacity-90 z-10">
                <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-3">Précise ta demande</h2>
                <?php if (isset($errors['service_base'])): ?>
                    <p class="text-danger text-[0.78rem] mb-3"><?= htmlspecialchars($errors['service_base']) ?></p>
                <?php endif; ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($basesGrouped as $category => $categoryBases): ?>
                        <div>
                            <p class="text-[0.82rem] font-semibold text-ink mb-2"><?= htmlspecialchars($category) ?></p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($categoryBases as $base): ?>
                                    <label class="inline-flex items-center gap-2 bg-white border border-border rounded-full px-4 py-[0.4rem] text-[0.82rem] text-ink cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light has-[:checked]:text-primary transition-colors">
                                        <input type="radio" name="service_base[<?= htmlspecialchars($category) ?>]" value="<?= $base['id'] ?>" required class="accent-primary js-spark-input">
                                        <?= htmlspecialchars($base['label']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($options)): ?>
            <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-5">
                <img src="/assets/images/decor/tache8.png" alt="" class="hidden min-[1024px]:block absolute -left-32 -bottom-10 w-[190px] h-auto pointer-events-none select-none opacity-40 -z-10">
                <img src="/assets/images/decor/plante10.png" alt="" class="hidden min-[1024px]:block absolute -left-24 -bottom-6 w-[130px] h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">
                <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-3">Options</h2>
                <div class="flex flex-col gap-2">
                    <?php foreach ($options as $option): ?>
                        <label class="flex items-center justify-between gap-3 bg-white border border-border rounded-full px-4 py-[0.45rem] text-[0.85rem] text-ink cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light transition-colors">
                            <span class="flex items-center gap-2">
                                <input type="checkbox" name="options[]" value="<?= $option['id'] ?>" data-price="<?= $option['extra_price'] ?>" class="option-checkbox accent-primary js-spark-input">
                                <?= htmlspecialchars($option['label']) ?>
                            </span>
                            <span class="text-muted shrink-0">+<?= number_format($option['extra_price'] / 100, 2) ?> €</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-5 flex flex-col gap-4">
            <img src="/assets/images/decor/photo.png" alt="" class="hidden min-[1024px]:block absolute -right-32 -top-10 w-[210px] h-auto pointer-events-none select-none opacity-90 z-10">
            <div>
                <label for="reference" class="block text-[0.85rem] font-semibold text-ink mb-1">Fichier de référence (optionnel)</label>
                <input type="file" id="reference" name="reference" accept="image/jpeg,image/png,image/webp" class="text-[0.82rem] text-muted">
                <?php if (isset($errors['reference'])): ?>
                    <p class="text-danger text-[0.78rem] mt-1"><?= htmlspecialchars($errors['reference']) ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($shop['accepts_quotes'])): ?>
                <label class="inline-flex items-center gap-2 text-[0.85rem] text-ink cursor-pointer">
                    <input type="checkbox" name="is_quote" class="w-4 h-4 accent-primary cursor-pointer">
                    Je préfère demander un devis d'abord
                </label>
            <?php endif; ?>
        </div>

        <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-5 flex flex-col gap-4">
            <h2 class="font-cursive text-[1.1rem] font-semibold text-ink">Adresse de facturation</h2>
            <p class="text-[0.78rem] text-muted -mt-2">Obligatoire — elle figurera sur ta facture. Pré-remplie depuis ton profil si tu en as une.</p>
            <?php if (isset($errors['billing'])): ?>
                <p class="text-danger text-[0.78rem] -mt-2"><?= htmlspecialchars($errors['billing']) ?></p>
            <?php endif; ?>

            <div>
                <label for="billing_name" class="block text-[0.85rem] font-semibold text-ink mb-1">Nom / raison sociale</label>
                <input type="text" id="billing_name" name="billing_name" value="<?= $bp('billing_name') ?>" required
                    class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
            </div>

            <div>
                <label for="billing_address_line1" class="block text-[0.85rem] font-semibold text-ink mb-1">Adresse</label>
                <input type="text" id="billing_address_line1" name="billing_address_line1" value="<?= $bp('billing_address_line1') ?>" required
                    class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary mb-2">
                <input type="text" id="billing_address_line2" name="billing_address_line2" value="<?= $bp('billing_address_line2') ?>" placeholder="Complément d'adresse (optionnel)"
                    class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
            </div>

            <div class="flex flex-col min-[480px]:flex-row gap-4">
                <div class="flex-1">
                    <label for="billing_postal_code" class="block text-[0.85rem] font-semibold text-ink mb-1">Code postal</label>
                    <input type="text" id="billing_postal_code" name="billing_postal_code" value="<?= $bp('billing_postal_code') ?>" required
                        class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                </div>
                <div class="flex-[2]">
                    <label for="billing_city" class="block text-[0.85rem] font-semibold text-ink mb-1">Ville</label>
                    <input type="text" id="billing_city" name="billing_city" value="<?= $bp('billing_city') ?>" required
                        class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                </div>
            </div>

            <div>
                <label for="billing_country" class="block text-[0.85rem] font-semibold text-ink mb-1">Pays</label>
                <input type="text" id="billing_country" name="billing_country" value="<?= $bp('billing_country') ?: 'France' ?>" required
                    class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
            </div>
        </div>

        <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-5 flex flex-col gap-4">
            <h2 class="font-cursive text-[1.1rem] font-semibold text-ink">Livraison</h2>

            <label class="inline-flex items-center gap-2 text-[0.85rem] text-ink cursor-pointer">
                <input type="checkbox" id="wants-shipping" name="wants_shipping" value="1" class="w-4 h-4 accent-primary cursor-pointer">
                Je souhaite recevoir une version physique de ma création
            </label>
            <p class="text-[0.78rem] text-muted -mt-3">Si tu ne coches pas cette case, tu recevras ta création au format numérique.</p>
            <?php if (isset($errors['shipping'])): ?>
                <p class="text-danger text-[0.78rem] -mt-2"><?= htmlspecialchars($errors['shipping']) ?></p>
            <?php endif; ?>

            <div id="shipping-fields" class="hidden flex-col gap-4">
                <label class="inline-flex items-center gap-2 text-[0.85rem] text-ink cursor-pointer">
                    <input type="checkbox" id="shipping-same-as-billing" name="shipping_same_as_billing" value="1" checked class="w-4 h-4 accent-primary cursor-pointer">
                    Livrer à mon adresse de facturation
                </label>

                <div id="shipping-address-fields" class="hidden flex-col gap-4">
                    <div>
                        <label for="shipping_name" class="block text-[0.85rem] font-semibold text-ink mb-1">Nom complet</label>
                        <input type="text" id="shipping_name" name="shipping_name"
                            class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                    </div>

                    <div>
                        <label for="shipping_address_line1" class="block text-[0.85rem] font-semibold text-ink mb-1">Adresse</label>
                        <input type="text" id="shipping_address_line1" name="shipping_address_line1"
                            class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary mb-2">
                        <input type="text" id="shipping_address_line2" name="shipping_address_line2" placeholder="Complément d'adresse (optionnel)"
                            class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                    </div>

                    <div class="flex flex-col min-[480px]:flex-row gap-4">
                        <div class="flex-1">
                            <label for="shipping_postal_code" class="block text-[0.85rem] font-semibold text-ink mb-1">Code postal</label>
                            <input type="text" id="shipping_postal_code" name="shipping_postal_code"
                                class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                        </div>
                        <div class="flex-[2]">
                            <label for="shipping_city" class="block text-[0.85rem] font-semibold text-ink mb-1">Ville</label>
                            <input type="text" id="shipping_city" name="shipping_city"
                                class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                        </div>
                    </div>

                    <div>
                        <label for="shipping_country" class="block text-[0.85rem] font-semibold text-ink mb-1">Pays</label>
                        <input type="text" id="shipping_country" name="shipping_country" placeholder="France"
                            class="w-full bg-white border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 bg-primary-light rounded-xl px-5 py-4">
            <span class="text-[0.85rem] font-semibold text-primary">Total estimé</span>
            <span class="text-[1.3rem] font-cursive font-semibold text-primary"><span id="total-price"><?= number_format($service['base_price'] / 100, 2) ?></span> €</span>
        </div>

            <button type="submit" class="btn btn--primary self-center px-12">Envoyer ma demande</button>
        </form>

        <p class="text-center mt-5">
            <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="text-[0.85rem] text-primary hover:underline">← Retour à la boutique</a>
        </p>
    </div>
</div>

<script>
    const basePrice = <?= $service['base_price'] ?>;
    const checkboxes = document.querySelectorAll('.option-checkbox');
    const totalDisplay = document.getElementById('total-price');

    function updateTotal() {
        let total = basePrice;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                total += parseInt(cb.dataset.price);
            }
        });

        totalDisplay.textContent = (total / 100).toFixed(2);
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));

    const wantsShippingInput = document.getElementById('wants-shipping');
    const shippingFields = document.getElementById('shipping-fields');
    const sameAsBillingInput = document.getElementById('shipping-same-as-billing');
    const shippingAddressFields = document.getElementById('shipping-address-fields');

    function toggleBlock(el, show) {
        el.classList.toggle('hidden', !show);
        el.classList.toggle('flex', show);
    }

    // Deux niveaux : la case "version physique" ouvre le bloc livraison ;
    // dedans, "Livrer à mon adresse de facturation" (coché par défaut)
    // masque les champs — le serveur recopie alors l'adresse de facturation.
    // Les champs ne s'affichent que pour une adresse de livraison différente.
    function syncShipping() {
        toggleBlock(shippingFields, wantsShippingInput.checked);
        toggleBlock(shippingAddressFields, wantsShippingInput.checked && !sameAsBillingInput.checked);
    }

    wantsShippingInput.addEventListener('change', syncShipping);
    sameAsBillingInput.addEventListener('change', syncShipping);
</script>
<script src="/assets/js/input-sparks.js"></script>
