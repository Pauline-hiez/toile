<?php

use App\Core\OrderStatus;

/**
 * Vue partagée client / artiste (voir $actor : 'client'|'artist'|'admin'),
 * injectée par OrderController::show(). Priorité de mise en page côté
 * artiste (voir plan) — le stepper/les cartes s'inspirent de la maquette
 * "Suivi de commande" (vue client) sans la reproduire à l'identique.
 *
 * @var array $order
 * @var array $selectedBases
 * @var array $timelineSteps
 * @var array $stepKeys
 * @var int|false $currentIndex
 * @var array $allowedStatuses
 * @var string $actor
 * @var array|null $existingReview
 * @var array $messages
 */
$pageTitle = 'Commande #' . $order['id'] . ' — Toile';
$statusInfo = \App\Models\Order::statusLabels()[$order['status']] ?? ['label' => $order['status'], 'class' => \App\Core\Badge::classes('neutral')];
$isQuotePhase = in_array($order['status'], ['quote_requested', 'price_proposed'], true);
$isDeadEnd = in_array($order['status'], ['rejected', 'cancelled'], true);

// Icônes des étapes du stepper — reprises du dashboard artiste, à défaut
// d'icône dédiée à "Livrée" on réutilise celle "en cours".
$stepIcons = [
    'pending' => 'commandes-attente.png',
    'accepted' => 'commandes.png',
    'in_progress' => 'commandes-cours.png',
    'delivered' => 'commandes-cours.png',
    'completed' => 'commandes-terminees.png',
];
?>

<div class="relative overflow-hidden">
    <div class="max-w-[900px] mx-auto px-5 min-[641px]:px-10 py-6">
        <div class="relative z-0 mb-1">
            <img src="/assets/images/decor/tache7.png" alt="" class="hidden min-[1024px]:block absolute -top-10 -right-36 w-[200px] h-auto pointer-events-none select-none opacity-30 z-10">
            <img src="/assets/images/decor/palette.png" alt="" class="hidden min-[1024px]:block absolute -top-6 -right-24 w-[220px] h-auto pointer-events-none select-none opacity-90 z-10">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="font-title text-title text-shine text-[1.5rem] min-[641px]:text-[1.8rem] font-semibold leading-tight">Commande #<?= (int) $order['id'] ?></h1>
                <span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span>
            </div>
        </div>
    <p class="text-[0.82rem] text-muted mb-5">Passée le <?= \App\Core\FrenchDate::format('d MMM y', $order['created_at']) ?></p>

    <?php if (isset($_GET['reported'])): ?>
        <div class="bg-success-bg border border-success/25 text-success rounded-md px-4 py-[0.7rem] mb-5 text-[0.8rem]">
            Merci, ton signalement a bien été envoyé à notre équipe.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['payment']) && $_GET['payment'] === 'failed'): ?>
        <div class="bg-danger-bg border border-danger/25 text-danger rounded-md px-4 py-[0.7rem] mb-5 text-[0.8rem]">
            Le paiement n'a pas pu être confirmé. Réessaie depuis le bouton ci-dessous.
        </div>
    <?php endif; ?>

    <?php if ($isDeadEnd): ?>
        <div class="flex items-center gap-3 bg-danger-bg border border-danger/25 text-danger rounded-md px-4 py-[0.8rem] mb-6 text-[0.85rem]">
            <img src="/assets/images/icones/commandes-annulees.png" alt="" class="w-9 h-9 object-contain shrink-0">
            Cette commande a été <?= $order['status'] === 'rejected' ? 'refusée' : 'annulée' ?>.
        </div>
    <?php elseif ($isQuotePhase): ?>
        <div class="flex items-center gap-3 bg-warning-bg border border-warning/25 text-warning rounded-md px-4 py-[0.8rem] mb-6 text-[0.85rem]">
            <img src="/assets/images/icones/commandes-attente.png" alt="" class="w-9 h-9 object-contain shrink-0">
            <span>
                <?php if ($order['status'] === 'quote_requested'): ?>
                    <?= $actor === 'artist'
                        ? 'Ce client attend une proposition de prix de ta part.'
                        : "Ta demande a été envoyée, en attente d'une proposition de prix de la part de l'artiste." ?>
                <?php else: ?>
                    <?= $actor === 'artist'
                        ? 'Tu as proposé ' . number_format($order['total_price'] / 100, 2) . ' € — en attente de la réponse du client.'
                        : "L'artiste propose un prix de " . number_format($order['total_price'] / 100, 2) . ' € pour ta demande.' ?>
                <?php endif; ?>
            </span>
        </div>
    <?php else: ?>
        <div class="flex items-start overflow-x-auto pb-2 mb-6 -mx-1 px-1">
            <?php foreach ($timelineSteps as $key => $label): ?>
                <?php
                $stepIndex = array_search($key, $stepKeys);
                $isActive = $key === $order['status'];
                $isDone = $currentIndex !== false && $stepIndex < $currentIndex;
                $ringClass = $isActive ? 'ring-2 ring-title' : ($isDone ? 'ring-2 ring-success' : '');
                ?>
                <div class="flex flex-col items-center shrink-0 min-w-[130px]">
                    <div class="flex items-center w-full">
                        <span class="<?= $stepIndex === 0 ? 'flex-1' : 'flex-1 border-t border-border' ?>"></span>
                        <span class="relative inline-block w-16 h-16 shrink-0">
                            <span class="block relative w-16 h-16 rounded-full bg-white overflow-hidden <?= $ringClass ?>">
                                <img src="/assets/images/icones/<?= $stepIcons[$key] ?? 'commandes.png' ?>" alt=""
                                    style="position:absolute; left:50%; top:-50px; height:140px; width:auto; max-width:none; transform:translateX(-50%);"
                                    class="<?= $isActive || $isDone ? '' : 'opacity-40 grayscale' ?>">
                            </span>
                            <?php if ($isDone): ?>
                                <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-success text-white text-[0.65rem] font-semibold flex items-center justify-center border-2 border-white">✓</span>
                            <?php endif; ?>
                        </span>
                        <span class="<?= $stepIndex === count($stepKeys) - 1 ? 'flex-1' : 'flex-1 border-t border-border' ?>"></span>
                    </div>
                    <p class="text-[0.75rem] font-semibold text-center mt-2 <?= $isActive ? 'text-title' : 'text-muted' ?>"><?= htmlspecialchars($label) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    // delivery_file sert à la fois pour l'image de référence du client
    // (uploads/references/, avant livraison) et le fichier livré par
    // l'artiste (uploads/deliveries/, une fois le statut passé à
    // 'delivered'/'completed') — même colonne, deux dossiers possibles.
    $orderImageFolder = in_array($order['status'], ['delivered', 'completed'], true) ? 'deliveries' : 'references';
    ?>
    <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-4 mb-5 flex flex-col min-[640px]:flex-row gap-4">
        <img src="/assets/images/decor/tache8.png" alt="" class="hidden min-[1024px]:block absolute -right-20 -bottom-16 w-[220px] h-auto pointer-events-none select-none opacity-40 z-10">
        <img src="/assets/images/decor/plante3.png" alt="" class="hidden min-[1024px]:block absolute -right-8 -bottom-11 w-[120px] h-auto pointer-events-none select-none opacity-90 z-10">
        <?php if (!empty($order['delivery_file'])): ?>
            <img src="/uploads/<?= $orderImageFolder ?>/<?= htmlspecialchars($order['delivery_file']) ?>" alt="" class="w-full min-[640px]:w-56 h-[220px] object-cover rounded-xl border border-border shrink-0">
        <?php else: ?>
            <div class="w-full min-[640px]:w-56 h-[220px] rounded-xl border border-border bg-bg flex items-center justify-center shrink-0">
                <img src="/assets/images/icones/commandes.png" alt="" class="w-16 h-16 object-contain opacity-60">
            </div>
        <?php endif; ?>
        <div class="flex-1">
            <h2 class="font-cursive text-[1.1rem] font-semibold text-ink leading-tight mb-1"><?= htmlspecialchars($order['service_title'] ?? $order['title']) ?></h2>
            <?php if ($actor === 'artist'): ?>
                <p class="text-[0.82rem] text-muted mb-2">Commandée par <?= htmlspecialchars($order['client_name']) ?></p>
            <?php else: ?>
                <p class="text-[0.82rem] text-muted mb-2">
                    Boutique : <a href="/boutiques/<?= htmlspecialchars($order['shop_slug']) ?>" class="text-primary hover:underline"><?= htmlspecialchars($order['shop_name']) ?></a>
                </p>
            <?php endif; ?>
            <div class="pt-2 border-t border-border">
                <h3 class="text-[0.8rem] font-semibold text-muted mb-1">Description du projet</h3>
                <p class="text-[0.85rem] text-ink leading-[1.6]"><?= nl2br(htmlspecialchars($order['description'])) ?></p>
            </div>
        </div>
    </div>

    <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-4 mb-6">
        <img src="/assets/images/decor/plante10.png" alt="" class="hidden min-[1024px]:block absolute -left-[145px] top-6 w-[180px] h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">
        <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-3">Détails</h2>

            <div class="flex flex-col gap-2 text-[0.85rem] mb-4">
                <div class="flex justify-between gap-3">
                    <span class="text-muted"><?= $order['service_id'] === null ? 'Sujet' : 'Prestation' ?></span>
                    <span class="text-ink font-medium text-right"><?= htmlspecialchars($order['service_title'] ?? $order['title']) ?></span>
                </div>
                <?php foreach ($selectedBases as $selectedBase): ?>
                    <div class="flex justify-between gap-3">
                        <span class="text-muted"><?= htmlspecialchars($selectedBase['category']) ?></span>
                        <span class="text-ink font-medium text-right"><?= htmlspecialchars($selectedBase['label']) ?></span>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($order['shipping_address_line1'])): ?>
                    <div class="flex justify-between gap-3">
                        <span class="text-muted">Livraison</span>
                        <span class="text-ink font-medium text-right">
                            <?php if (!empty($order['shipping_name'])): ?>
                                <?= htmlspecialchars($order['shipping_name']) ?><br>
                            <?php endif; ?>
                            <?= htmlspecialchars($order['shipping_address_line1']) ?><br>
                            <?php if (!empty($order['shipping_address_line2'])): ?>
                                <?= htmlspecialchars($order['shipping_address_line2']) ?><br>
                            <?php endif; ?>
                            <?= htmlspecialchars(trim($order['shipping_postal_code'] . ' ' . $order['shipping_city'], ' ')) ?>
                            <?php if (!empty($order['shipping_country'])): ?>
                                <br><?= htmlspecialchars($order['shipping_country']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center justify-between gap-3 flex-wrap bg-primary-light rounded-xl px-4 py-3 mb-4">
                <span class="text-[0.8rem] font-semibold text-primary">Prix total</span>
                <span class="text-[1rem] min-[481px]:text-[1.2rem] font-cursive font-semibold text-primary text-right"><?= $order['total_price'] > 0 ? number_format($order['total_price'] / 100, 2) . ' €' : 'À convenir avec l\'artiste' ?></span>
            </div>

            <?php
            // Texte des boutons d'action : un verbe ("Refuser"), pas le
            // libellé du statut qui est un état ("Refusée") — utilisé
            // seul, ce dernier prêtait à confusion sur ce que fait le bouton.
            // "in_progress" a un libellé différent selon le contexte : depuis
            // "accepted" c'est l'action normale (Démarrer), depuis "delivered"
            // c'est une annulation de la livraison (Reprendre) — la même
            // transition ne doit pas être présentée pareil dans les deux cas.
            $actionVerbs = [
                'accepted' => 'Accepter',
                'rejected' => 'Refuser cette commande',
                'cancelled' => 'Annuler cette commande',
                'in_progress' => $order['status'] === 'delivered' ? 'Reprendre la création (annuler la livraison)' : 'Démarrer la création',
                'completed' => 'Marquer comme terminée',
            ];
            // Depuis "delivered", repasser en "in_progress" annule la
            // livraison — action rare, à ne pas présenter comme le bouton
            // principal (sinon on croit que c'est l'étape suivante normale).
            $secondaryStatuses = $order['status'] === 'delivered'
                ? ['rejected', 'cancelled', 'in_progress']
                : ['rejected', 'cancelled'];
            $primaryStatuses = array_diff($allowedStatuses, $secondaryStatuses);
            ?>

            <?php if ($order['status'] === 'delivered'): ?>
                <div class="bg-info-bg border border-info/25 text-info rounded-md px-4 py-[0.7rem] mb-4 text-[0.8rem]">
                    <?= $actor === 'artist'
                        ? 'Fichier livré — en attente de la confirmation de réception par le client.'
                        : 'Le fichier a été livré. Vérifie-le puis confirme la réception ci-dessous.' ?>
                </div>
            <?php endif; ?>

            <?php if ($actor === 'client' && $order['status'] === 'price_proposed'): ?>
                <a href="/commandes/<?= $order['id'] ?>/payer-devis" class="btn btn--primary w-full text-center block mb-2">
                    Payer et accepter ce prix — <?= number_format($order['total_price'] / 100, 2) ?> €
                </a>
            <?php endif; ?>

            <?php foreach ($primaryStatuses as $status): ?>
                <form method="POST" action="/commandes/<?= $order['id'] ?>/transition" enctype="multipart/form-data" class="flex flex-col gap-2 mb-2">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <input type="hidden" name="status" value="<?= $status ?>">

                    <?php if ($status === 'delivered'): ?>
                        <label class="text-[0.8rem] text-muted">Fichier à livrer</label>
                        <input type="file" name="delivery_file" required class="text-[0.85rem]">
                        <button type="submit" class="btn btn--primary">Marquer comme livrée</button>
                    <?php elseif ($status === 'price_proposed'): ?>
                        <label class="text-[0.8rem] text-muted">Prix proposé (€)</label>
                        <input type="number" name="proposed_price" step="0.01" min="0.01" required
                            class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                        <label class="text-[0.8rem] text-muted mt-1">Message pour le client (optionnel)</label>
                        <textarea name="message" rows="3" placeholder="Ex : Voici mon prix pour ce projet, n'hésite pas si tu as des questions !"
                            class="w-full border border-border rounded-2xl px-4 py-2 font-main text-[0.85rem] outline-none focus:border-primary resize-none"></textarea>
                        <button type="submit" class="btn btn--primary">Proposer ce prix</button>
                    <?php else: ?>
                        <button type="submit"
                            onclick="return confirm('Confirmer : <?= htmlspecialchars(OrderStatus::label($status)) ?> ?');"
                            class="btn btn--primary">
                            <?= htmlspecialchars($actionVerbs[$status] ?? OrderStatus::label($status)) ?>
                        </button>
                    <?php endif; ?>
                </form>
            <?php endforeach; ?>

            <?php foreach ($allowedStatuses as $status): ?>
                <?php if (!in_array($status, $secondaryStatuses, true)) continue; ?>
                <form method="POST" action="/commandes/<?= $order['id'] ?>/transition" class="<?= empty($primaryStatuses) ? '' : 'mt-2 pt-2 border-t border-border' ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <input type="hidden" name="status" value="<?= $status ?>">
                    <button type="submit"
                        onclick="return confirm('Confirmer : <?= htmlspecialchars(OrderStatus::label($status)) ?> ?');"
                        class="text-[0.8rem] text-muted bg-transparent border-0 cursor-pointer hover:text-danger transition-colors underline">
                        <?= htmlspecialchars($actionVerbs[$status] ?? OrderStatus::label($status) . ' cette commande') ?>
                    </button>
                </form>
            <?php endforeach; ?>
    </div>

    <?php if (in_array($order['status'], ['delivered', 'completed'], true) && !empty($order['delivery_file'])): ?>
        <div class="bg-white border border-border rounded-2xl shadow-sm p-4 mb-6">
            <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-2">Fichier livré</h2>
            <a href="/uploads/deliveries/<?= htmlspecialchars($order['delivery_file']) ?>" target="_blank" class="btn btn--primary inline-block">
                Télécharger le fichier livré
            </a>
        </div>
    <?php endif; ?>

    <?php if ($order['status'] === 'completed' && $actor === 'client'): ?>
        <div class="bg-white border border-border rounded-2xl shadow-sm p-4 mb-6">
            <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-3">Mon avis</h2>

            <?php if ($existingReview !== null): ?>
                <div class="flex items-center gap-1 mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?= $i <= $existingReview['rating'] ? 'text-title' : 'text-border' ?>">★</span>
                    <?php endfor; ?>
                </div>
                <?php if (!empty($existingReview['comment'])): ?>
                    <p class="text-[0.85rem] text-ink leading-[1.5] mb-2"><?= nl2br(htmlspecialchars($existingReview['comment'])) ?></p>
                <?php endif; ?>
                <p class="text-[0.78rem] text-muted italic">Avis déjà soumis — merci !</p>
            <?php else: ?>
                <form method="POST" action="/commandes/<?= $order['id'] ?>/review" class="flex flex-col gap-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

                    <div>
                        <label class="block text-[0.8rem] text-muted mb-1">Note</label>
                        <div class="flex gap-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="cursor-pointer text-[1.2rem] text-title">
                                    <input type="radio" name="rating" value="<?= $i ?>" required class="sr-only peer">
                                    <span class="peer-checked:opacity-100 opacity-40">★</span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div>
                        <label for="comment" class="block text-[0.8rem] text-muted mb-1">Commentaire (optionnel)</label>
                        <textarea id="comment" name="comment" rows="3" class="w-full border border-border rounded-2xl px-4 py-2 font-main text-[0.85rem] outline-none focus:border-primary resize-none"></textarea>
                    </div>

                    <button type="submit" class="btn btn--primary self-start">Laisser mon avis</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($actor === 'artist' && $existingReview !== null): ?>
        <div class="bg-white border border-border rounded-2xl shadow-sm p-4 mb-6">
            <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-3">Avis du client</h2>
            <div class="flex items-center gap-1 mb-2">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $existingReview['rating'] ? 'text-title' : 'text-border' ?>">★</span>
                <?php endfor; ?>
            </div>
            <?php if (!empty($existingReview['comment'])): ?>
                <p class="text-[0.85rem] text-ink leading-[1.5] mb-3"><?= nl2br(htmlspecialchars($existingReview['comment'])) ?></p>
            <?php endif; ?>

            <button
                type="button"
                class="text-[0.75rem] text-muted bg-transparent border-0 cursor-pointer hover:text-danger transition-colors"
                data-report-trigger
                data-report-type="review"
                data-report-id="<?= $existingReview['id'] ?>"
                data-report-reason="commentaire_inapproprie"
                data-report-redirect="/commandes/<?= $order['id'] ?>?reported=1">
                🚩 Signaler cet avis
            </button>
        </div>
    <?php endif; ?>

    <?php require __DIR__ . '/../components/report-modal.php'; ?>
    <script src="/assets/js/report-modal.js"></script>

    <div class="relative z-0 bg-white border border-border rounded-2xl shadow-sm p-4 mb-6">
        <img src="/assets/images/decor/carnet.png" alt="" class="hidden min-[1024px]:block absolute -right-36 -bottom-16 w-[260px] h-auto pointer-events-none select-none opacity-90 -z-10">
        <h2 id="messages" class="font-cursive text-[1.1rem] font-semibold text-ink mb-3">Messages</h2>

        <?php if (empty($messages)): ?>
            <p class="text-[0.82rem] text-muted mb-3">Aucun message pour l'instant.</p>
        <?php else: ?>
            <div class="flex flex-col gap-2 mb-3">
                <?php foreach ($messages as $message): ?>
                    <?php $isMine = $message['sender_id'] === $_SESSION['user_id']; ?>
                    <div class="max-w-[80%] rounded-2xl px-3 py-2 <?= $isMine ? 'self-end bg-primary-light text-right' : 'self-start bg-bg border border-border text-left' ?>">
                        <div class="flex items-center gap-2 <?= $isMine ? 'justify-end' : 'justify-start' ?>">
                            <strong class="text-[0.8rem] text-ink"><?= htmlspecialchars($message['sender_name']) ?></strong>
                            <span class="text-[0.7rem] text-muted"><?= \App\Core\FrenchDate::format('d MMM y à HH:mm', $message['created_at']) ?></span>
                        </div>
                        <p class="text-[0.85rem] text-ink mt-1"><?= nl2br(htmlspecialchars($message['content'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($actor === 'artist' && $order['status'] === 'quote_requested'): ?>
            <p class="text-[0.78rem] text-muted italic">Ton message pour le client peut être ajouté directement avec ta proposition de prix ci-dessus.</p>
        <?php elseif (!in_array($order['status'], ['completed', 'cancelled', 'rejected'], true)): ?>
            <form method="POST" action="/commandes/<?= $order['id'] ?>/messages" class="flex flex-col gap-2">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                <textarea
                    name="content"
                    rows="3"
                    placeholder="Écrire un message..."
                    required
                    class="w-full border border-border rounded-2xl px-4 py-2 font-main text-[0.85rem] outline-none focus:border-primary resize-none"></textarea>
                <button type="submit" class="btn btn--primary self-start">Envoyer la proposition</button>
            </form>
        <?php endif; ?>
    </div>

        <?php if ($actor === 'client'): ?>
            <a href="/mes-commandes" class="text-[0.85rem] text-primary hover:underline">← Retour à mes commandes</a>
        <?php elseif ($actor === 'artist'): ?>
            <a href="/commandes-recues" class="text-[0.85rem] text-primary hover:underline">← Retour aux commandes reçues</a>
        <?php else: ?>
            <a href="/admin/orders" class="text-[0.85rem] text-primary hover:underline">← Retour aux commandes</a>
        <?php endif; ?>

    </div>
</div>
