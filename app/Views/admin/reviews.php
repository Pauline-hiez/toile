<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::reviews()).
 *
 * @var array $reviews
 */
?>

<div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <?php if (empty($reviews)): ?>
        <p class="text-muted text-[0.85rem] text-center p-6">Aucun avis pour le moment.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2]">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Boutique</th>
                        <th>Client</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?= htmlspecialchars($review['order_title']) ?></td>
                            <td><?= htmlspecialchars($review['shop_name']) ?></td>
                            <td><?= htmlspecialchars($review['client_name']) ?></td>
                            <td>
                                <span class="inline-flex items-center gap-[2px]">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg viewBox="0 0 24 24" fill="<?= $i <= $review['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5" class="w-[13px] h-[13px] <?= $i <= $review['rating'] ? 'text-title' : 'text-border' ?>">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"></polygon>
                                        </svg>
                                    <?php endfor; ?>
                                </span>
                            </td>
                            <td class="max-w-[280px]">
                                <?php if (!empty($review['comment'])): ?>
                                    <span class="text-[0.82rem] text-ink"><?= htmlspecialchars(mb_substr($review['comment'], 0, 80)) ?><?= mb_strlen($review['comment']) > 80 ? '…' : '' ?></span>
                                <?php else: ?>
                                    <em class="text-muted">Aucun commentaire</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="/admin/reviews/<?= $review['id'] ?>/delete" class="[&_button]:bg-transparent [&_button]:border-0 [&_button]:cursor-pointer [&_button]:p-1 [&_button]:rounded-sm [&_button]:text-muted [&_button]:transition-colors [&_button]:flex [&_button]:items-center [&_button:hover]:text-danger [&_button:hover]:bg-danger-bg [&_img]:w-4 [&_img]:h-4 [&_img]:object-contain">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                    <button type="submit" title="Supprimer l'avis" onclick="return confirm('Supprimer cet avis ?');">
                                        <img src="/assets/images/icones/supprimer.png" alt="Supprimer">
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
