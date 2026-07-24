<?php
/**
 * Variables injectées par App\Core\Renderer::render() via extract($data)
 * (voir AdminController::subscriptionInvoices()).
 *
 * @var array $subscription Voir ShopSubscription::findByIdWithShop().
 * @var array $invoices Voir SubscriptionInvoice::findByShopId().
 */
?>

<div class="mb-6">
    <a href="/admin/subscriptions" class="text-primary text-[0.85rem] font-medium hover:underline">← Retour aux abonnements</a>
</div>

<div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <?php if (empty($invoices)): ?>
        <p class="text-muted text-[0.85rem] text-center p-6">Aucune facture pour cette boutique pour le moment.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="data-table max-[560px]:min-w-[480px]">
                <thead>
                    <tr>
                        <th>Formule</th>
                        <th>Période</th>
                        <th>Payée le</th>
                        <th>Montant</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?= htmlspecialchars($invoice['plan_name']) ?></td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $invoice['period_start']) ?> — <?= \App\Core\FrenchDate::format('d MMM y', $invoice['period_end']) ?></td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $invoice['paid_at']) ?></td>
                            <td><?= number_format($invoice['amount'] / 100, 2, ',', ' ') ?> €</td>
                            <td><a href="/factures/abonnement/<?= (int) $invoice['id'] ?>" class="text-primary font-medium hover:underline">Télécharger (PDF)</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
