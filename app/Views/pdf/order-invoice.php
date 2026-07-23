<?php
/**
 * Gabarit de facture de commande, rendu en chaîne par
 * InvoiceController::orderInvoice() puis converti en PDF par
 * App\Core\PdfService (Dompdf) — pas de layout (rendu avec $layout =
 * false), CSS limitée au support de Dompdf (pas de flexbox/grid fiable,
 * mise en page en display:table). Palette reprise de public/assets/css/style.css
 * (--color-primary, --color-title...) recopiée en dur : Dompdf ne résout
 * pas les variables CSS de façon fiable.
 *
 * @var array $order Voir Order::findByIdWithDetails().
 * @var array|null $artist Propriétaire de la boutique (App\Models\User::findById()).
 * @var array<string, string> $company Réglages de facturation (Paramètres admin).
 */
$netAmount = $order['total_price'] - $order['commission_amount'];
// Version redimensionnée (450x300, ~65 Ko) : le fichier source fait 2,4 Mo
// (1536x1024) et Dompdf embarque l'image telle quelle sans la
// recompresser à la taille d'affichage — un PDF de facture gonflait sinon
// à plus de 2 Mo pour un logo affiché en 42px de haut.
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

    .totals { width: 100%; margin-top: 4px; }
    .totals td { padding: 5px 12px; }
    .totals td.label { text-align: right; color: #6b7280; }
    .totals td.value { text-align: right; width: 120px; }
    .totals tr.net td { font-weight: bold; font-size: 14px; color: #7a9e7e; background: #e8f0ea; border-radius: 6px; padding-top: 9px; padding-bottom: 9px; }

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
                <h1>Facture</h1>
                <p class="ref">Réf. FACT-CMD-<?= (int) $order['id'] ?></p>
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
                    <span class="name"><?= htmlspecialchars($order['shop_name']) ?></span><br>
                    <?php if ($artist !== null): ?>
                        <span class="muted"><?= htmlspecialchars($artist['username']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col">
                <div class="section-box">
                    <h3>Client</h3>
                    <span class="name"><?= htmlspecialchars($order['client_name']) ?></span>
                    <?php if (!empty($order['shipping_address_line1'])): ?>
                        <br><span class="muted">
                            <?= htmlspecialchars($order['shipping_address_line1']) ?><br>
                            <?php if (!empty($order['shipping_address_line2'])): ?>
                                <?= htmlspecialchars($order['shipping_address_line2']) ?><br>
                            <?php endif; ?>
                            <?= htmlspecialchars(trim($order['shipping_postal_code'] . ' ' . $order['shipping_city'], ' ')) ?>
                            <?php if (!empty($order['shipping_country'])): ?>
                                <br><?= htmlspecialchars($order['shipping_country']) ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <table class="lines">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Date</th>
                    <th class="amount">Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($order['service_title'] ?? $order['title']) ?></td>
                    <td><?= \App\Core\FrenchDate::format('d MMMM y', $order['created_at']) ?></td>
                    <td class="amount"><?= number_format($order['total_price'] / 100, 2) ?> €</td>
                </tr>
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="label">Montant total payé par le client</td>
                <td class="value"><?= number_format($order['total_price'] / 100, 2) ?> €</td>
            </tr>
            <tr>
                <td class="label">Commission plateforme (<?= number_format((float) $order['commission_rate'], 2) ?> %)</td>
                <td class="value">- <?= number_format($order['commission_amount'] / 100, 2) ?> €</td>
            </tr>
            <tr class="net">
                <td class="label">Net perçu par l'artiste</td>
                <td class="value"><?= number_format($netAmount / 100, 2) ?> €</td>
            </tr>
        </table>

        <div class="footer">
            Commande #<?= (int) $order['id'] ?> — Toile, le marketplace des artistes — document généré automatiquement, sans signature manuscrite requise.
        </div>
    </div>
</body>
</html>
