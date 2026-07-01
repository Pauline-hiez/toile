<?php

use App\Core\OrderStatus;

$pageTitle = 'Commande #' . $order['id'] . ' — Toile';

$extraHead = '<style>
    .timeline {
        display: flex;
        gap: 1rem;
        margin: 1.5rem 0;
    }

    .step {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
        border-radius: 4px;
        background: #eee;
        font-size: 0.85rem;
    }

    .step.done {
        background: #c3e6cb;
    }

    .step.active {
        background: #6f42c1;
        color: white;
        font-weight: bold;
    }
</style>';
?>

<h1>Commande #<?= $order['id'] ?></h1>

<p>
    <strong>Prestation :</strong> <?= htmlspecialchars($order['service_title'] ?? $order['title']) ?><br>
    <strong>Boutique :</strong> <a href="/boutiques/<?= htmlspecialchars($order['shop_slug']) ?>"><?= htmlspecialchars($order['shop_name']) ?></a><br>
    <strong>Client :</strong> <?= htmlspecialchars($order['client_name']) ?><br>
    <strong>Prix total :</strong> <?= number_format($order['total_price'] / 100, 2) ?> €<br>
    <strong>Statut :</strong> <?= htmlspecialchars(OrderStatus::label($order['status'])) ?>
</p>

<?php if (!in_array($order['status'], ['rejected', 'cancelled'], true)): ?>
    <div class="timeline">
        <?php foreach ($timelineSteps as $key => $label): ?>
            <?php
            $stepIndex = array_search($key, $stepKeys);
            $class = 'step';
            if ($key === $order['status']) $class .= ' active';
            elseif ($currentIndex !== false && $stepIndex < $currentIndex) $class .= ' done';
            ?>
            <div class="<?= $class ?>"><?= $label ?></div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p style="color: red;">
        Cette commande a été <?= $order['status'] === 'rejected' ? 'refusée' : 'annulée' ?>.
    </p>
<?php endif; ?>

<h2>Description du projet</h2>
<p><?= nl2br(htmlspecialchars($order['description'])) ?></p>

<?php if ($order['status'] === 'delivered' || $order['status'] === 'completed'): ?>
    <?php if (!empty($order['delivery_file'])): ?>
        <h2>Fichier livré</h2>
        <p>
            <a href="/uploads/deliveries/<?= htmlspecialchars($order['delivery_file']) ?>" target="_blank">
                Télécharger le fichier livré
            </a>
        </p>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($allowedStatuses)): ?>
    <h2>Actions disponibles</h2>

    <?php foreach ($allowedStatuses as $status): ?>
        <form method="POST" action="/commandes/<?= $order['id'] ?>/transition"
            enctype="multipart/form-data"
            style="display: inline-block; margin-right: 0.5rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
            <input type="hidden" name="status" value="<?= $status ?>">

            <?php if ($status === 'delivered'): ?>
                <div>
                    <label>Fichier à livrer :</label>
                    <input type="file" name="delivery_file" required>
                </div>
            <?php endif; ?>

            <button
                type="submit"
                onclick="return confirm('Confirmer : <?= htmlspecialchars(OrderStatus::label($status)) ?> ?');">
                <?= htmlspecialchars(OrderStatus::label($status)) ?>
            </button>
        </form>
    <?php endforeach; ?>
<?php endif; ?>

<p>
    <?php if ($actor === 'client'): ?>
        <a href="/mes-commandes">← Retour à mes commandes</a>
    <?php else: ?>
        <a href="/commandes-recues">← Retour aux commandes reçues</a>
    <?php endif; ?>
</p>
