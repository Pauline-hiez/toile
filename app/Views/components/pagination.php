<?php
/**
 * Composant de pagination réutilisable sur tout le site (admin ET
 * pages publiques). Simple include (pas de scope isolé) : les
 * variables ci-dessous doivent déjà exister dans la vue appelante.
 *
 * @var int $page
 * @var int $totalPages
 * @var int $total
 * @var int $rangeStart
 * @var int $rangeEnd
 * @var array<int, int|string> $pageNumbers
 * @var callable $queryWithout  (array $overrides) => string
 * @var string|null $entityLabel  ex: 'utilisateurs', 'commandes', 'boutiques'
 */
$entityLabel = $entityLabel ?? 'résultats';
?>
<div class="pagination">
    <span>Affichage <?= $rangeStart ?> à <?= $rangeEnd ?> sur <?= number_format($total, 0, ',', ' ') ?> <?= htmlspecialchars($entityLabel) ?></span>

    <div class="pagination__controls">
        <a class="pagination__btn" href="<?= $queryWithout(['page' => max(1, $page - 1)]) ?>" aria-disabled="<?= $page <= 1 ? 'true' : 'false' ?>">◂ Précédent</a>

        <div class="pagination__pages">
            <?php foreach ($pageNumbers as $p): ?>
                <?php if ($p === '...'): ?>
                    <span>…</span>
                <?php elseif ($p === $page): ?>
                    <span class="active"><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= $queryWithout(['page' => $p]) ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <a class="pagination__btn" href="<?= $queryWithout(['page' => min($totalPages, $page + 1)]) ?>" aria-disabled="<?= $page >= $totalPages ? 'true' : 'false' ?>">Suivant ▸</a>
    </div>
</div>
