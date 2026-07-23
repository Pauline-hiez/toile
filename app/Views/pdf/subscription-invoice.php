<?php
/**
 * Gabarit de facture d'abonnement, rendu en chaîne par
 * InvoiceController::subscriptionInvoice() puis converti en PDF (voir
 * order-invoice.php pour les notes sur les contraintes CSS de Dompdf et
 * la palette recopiée en dur).
 *
 * @var array $invoice Voir SubscriptionInvoice::findByIdWithShop().
 * @var array<string, string> $company Réglages de facturation (Paramètres admin).
 */
// Version redimensionnée (450x300, ~65 Ko) — voir order-invoice.php pour
// le détail (le fichier source de 2,4 Mo gonflait le PDF outre mesure).
$logoPath = dirname(__DIR__, 3) . '/public/assets/images/site/logo-invoice.png';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 0; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #2d2d2d; margin: 0; }
    .topbar { background: #7a9e7e; height: 8px; }
    .page { padding: 28px 36px 20px 36px; }

    .header { display: table; width: 100%; margin-bottom: 26px; }
    .header .col { display: table-cell; vertical-align: top; width: 50%; }
    .header .col.right { text-align: right; }
    .logo { height: 42px; margin-bottom: 10px; }
    h1 { font-size: 22px; margin: 0 0 4px 0; color: #7a9e7e; text-transform: uppercase; letter-spacing: 0.06em; }
    .ref { color: #a89a82; font-size: 10.5px; font-weight: bold; }
    .muted { color: #6b7280; }

    .company-box { display: inline-block; background: #f2efe9; border-radius: 8px; padding: 10px 14px; text-align: left; font-size: 10px; line-height: 1.5; }

    .section { display: table; width: 100%; margin-bottom: 20px; }
    .section .col { display: table-cell; vertical-align: top; width: 50%; padding-right: 14px; }
    .section-box { background: #f2efe9; border-left: 3px solid #7a9e7e; border-radius: 0 6px 6px 0; padding: 10px 14px; }
    .section-box h3 { font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #a89a82; margin: 0 0 5px 0; }
    .section-box .name { font-size: 12px; font-weight: bold; color: #2d2d2d; }

    table.lines { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    table.lines th { background: #7a9e7e; color: #ffffff; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.03em; padding: 9px 12px; text-align: left; }
    table.lines td { border: 1px solid #e5ddd0; border-top: none; padding: 10px 12px; text-align: left; }
    table.lines td.amount, table.lines th.amount { text-align: right; }

    .footer { margin-top: 36px; padding-top: 10px; border-top: 2px solid #7a9e7e; font-size: 9px; color: #6b7280; }
</style>
</head>
<body>
    <div class="topbar"></div>
    <div class="page">
        <div class="header">
            <div class="col">
                <?php if (file_exists($logoPath)): ?>
                    <img src="<?= $logoPath ?>" class="logo" alt="Toile">
                <?php endif; ?>
                <h1>Facture d'abonnement</h1>
                <p class="ref">Réf. FACT-ABO-<?= (int) $invoice['id'] ?></p>
            </div>
            <div class="col right">
                <?php if (!empty($company['company_name']) || !empty($company['company_address']) || !empty($company['company_siret']) || !empty($company['company_vat'])): ?>
                    <div class="company-box">
                        <?php if (!empty($company['company_name'])): ?>
                            <strong><?= htmlspecialchars($company['company_name']) ?></strong><br>
                        <?php endif; ?>
                        <?php if (!empty($company['company_address'])): ?>
                            <span class="muted"><?= nl2br(htmlspecialchars($company['company_address'])) ?></span><br>
                        <?php endif; ?>
                        <?php if (!empty($company['company_siret'])): ?>
                            <span class="muted">SIRET : <?= htmlspecialchars($company['company_siret']) ?></span><br>
                        <?php endif; ?>
                        <?php if (!empty($company['company_vat'])): ?>
                            <span class="muted">TVA : <?= htmlspecialchars($company['company_vat']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <div class="col">
                <div class="section-box">
                    <h3>Boutique</h3>
                    <span class="name"><?= htmlspecialchars($invoice['shop_name']) ?></span><br>
                    <span class="muted"><?= htmlspecialchars($invoice['owner_username']) ?></span>
                </div>
            </div>
            <div class="col">
                <div class="section-box">
                    <h3>Période facturée</h3>
                    <span class="name">
                        <?= \App\Core\FrenchDate::format('d MMMM y', $invoice['period_start']) ?>
                        au
                        <?= \App\Core\FrenchDate::format('d MMMM y', $invoice['period_end']) ?>
                    </span>
                </div>
            </div>
        </div>

        <table class="lines">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Date de paiement</th>
                    <th class="amount">Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Abonnement <?= htmlspecialchars($invoice['plan_name']) ?></td>
                    <td><?= \App\Core\FrenchDate::format('d MMMM y', $invoice['paid_at']) ?></td>
                    <td class="amount"><?= number_format($invoice['amount'] / 100, 2) ?> €</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Référence de paiement Stripe : <?= htmlspecialchars($invoice['stripe_invoice_id']) ?> — Toile, le marketplace des artistes — document généré automatiquement, sans signature manuscrite requise.
        </div>
    </div>
</body>
</html>
