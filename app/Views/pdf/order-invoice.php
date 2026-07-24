<?php
/**
 * Facture de commande (conforme franchise en base de TVA), mise en page
 * inspirée d'une maquette fournie par le client. Rendue en chaîne par
 * InvoiceController::orderInvoice() puis convertie en PDF par Dompdf (pas de
 * layout ; CSS limitée au support de Dompdf, mise en page en display:table,
 * palette recopiée en dur).
 *
 * Mentions obligatoires : n° de facture séquentiel (série FAC), dates,
 * identité + adresse de l'émetteur et de l'acheteur, détail, total, et
 * mention « TVA non applicable, art. 293 B du CGI » (franchise en base).
 *
 * @var array $order   Voir Order::findByIdWithDetails() (billing_*, invoice_number, client_email...).
 * @var array|null $artist  Propriétaire de la boutique (User::findById()).
 * @var array<string, string> $company  Réglages (Paramètres admin) : company_*, contact_email...
 */
$netAmount = $order['total_price'] - $order['commission_amount'];
$logoPath = dirname(__DIR__, 3) . '/public/assets/images/site/logo-invoice.png';

$invoiceNumber = $order['invoice_number'] ?: ('FAC-' . $order['id']);
$orderRef = 'CMD-' . date('Y', strtotime($order['created_at'])) . '-' . sprintf('%05d', $order['id']);
$issuedAt = !empty($order['invoiced_at']) ? $order['invoiced_at'] : $order['created_at'];

$hasBilling = !empty($order['billing_address_line1']);
$billingName = $hasBilling && !empty($order['billing_name']) ? $order['billing_name'] : $order['client_name'];

$statusLabels = ['accepted' => 'Payée', 'in_progress' => 'Payée', 'delivered' => 'Payée', 'completed' => 'Payée'];
$statusLabel = $statusLabels[$order['status']] ?? 'Payée';

$companyName = $company['company_name'] ?? '' ?: 'Toile Marketplace';
$e = fn($v) => htmlspecialchars((string) $v);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 0; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #3a3a3a; margin: 0; }
    .page { padding: 40px 46px 30px 46px; }

    /* En-tête */
    .head { display: table; width: 100%; }
    .head .c { display: table-cell; vertical-align: top; }
    .head .c.right { text-align: right; }
    .logo { height: 62px; }
    .tagline { font-size: 8px; letter-spacing: 0.22em; color: #9a9a92; text-transform: uppercase; margin-top: 2px; }
    .doc-title { font-size: 34px; font-weight: bold; color: #2d2d2d; letter-spacing: 0.02em; line-height: 1; }
    .doc-num { font-size: 12px; color: #7a9e7e; font-weight: bold; margin-top: 8px; }
    .doc-dates { font-size: 10px; color: #6b7280; margin-top: 6px; line-height: 1.6; }
    .doc-dates strong { color: #3a3a3a; font-weight: normal; }

    .rule { border-top: 1px solid #e6e1d8; margin: 22px 0 24px; }

    /* Parties */
    .parties { display: table; width: 100%; margin-bottom: 26px; }
    .parties .c { display: table-cell; vertical-align: top; width: 50%; padding-right: 18px; }
    .lbl { font-size: 8.5px; letter-spacing: 0.14em; text-transform: uppercase; color: #7a9e7e; margin-bottom: 8px; }
    .who { font-size: 13px; font-weight: bold; color: #2d2d2d; margin-bottom: 4px; }
    .addr { font-size: 10px; color: #555; line-height: 1.65; }

    /* Encart commande */
    .order-box { background: #f6f5f1; border-radius: 12px; padding: 16px 20px; margin-bottom: 26px; }
    .order-ref { font-size: 12px; font-weight: bold; color: #7a9e7e; letter-spacing: 0.02em; margin-bottom: 12px; }
    .order-cols { display: table; width: 100%; }
    .order-cols .c { display: table-cell; vertical-align: top; width: 33%; padding-right: 12px; }
    .order-cols .k { font-size: 8px; letter-spacing: 0.12em; text-transform: uppercase; color: #a0998a; margin-bottom: 4px; }
    .order-cols .v { font-size: 11px; color: #3a3a3a; line-height: 1.45; }
    .order-cols .v strong { font-weight: bold; color: #2d2d2d; }
    .badge { display: inline-block; background: #e8f0ea; color: #5f7a63; font-size: 10px; font-weight: bold; padding: 4px 14px; border-radius: 999px; }

    /* Tableau des lignes */
    table.lines { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.lines th { font-size: 8.5px; letter-spacing: 0.1em; text-transform: uppercase; color: #7a9e7e; font-weight: bold; text-align: left; padding: 0 10px 10px; border-bottom: 1px solid #e6e1d8; }
    table.lines td { padding: 14px 10px; border-bottom: 1px solid #efeae1; font-size: 10.5px; vertical-align: top; }
    table.lines .r { text-align: right; }
    table.lines .desc strong { font-size: 11px; color: #2d2d2d; }
    table.lines .desc .sub { font-size: 9px; color: #9a9a92; margin-top: 2px; }

    /* Paiement + totaux */
    .bottom { display: table; width: 100%; margin-top: 18px; }
    .bottom .c { display: table-cell; vertical-align: top; }
    .bottom .left { width: 52%; padding-right: 20px; }
    .bottom .right { width: 48%; }

    .pay .k { font-size: 8.5px; letter-spacing: 0.12em; text-transform: uppercase; color: #7a9e7e; margin-bottom: 8px; }
    .pay table { width: 100%; font-size: 10px; color: #555; }
    .pay td { padding: 2px 0; }
    .pay td.pk { color: #9a9a92; width: 42%; }

    .tot table { width: 100%; }
    .tot td { padding: 6px 4px; font-size: 10.5px; }
    .tot td.k { color: #6b7280; }
    .tot td.v { text-align: right; color: #3a3a3a; }
    .tot tr.grand td { font-size: 15px; font-weight: bold; color: #5f7a63; background: #e8f0ea; padding: 12px 12px; }
    .tot tr.grand td.k { color: #5f7a63; border-radius: 6px 0 0 6px; }
    .tot tr.grand td.v { border-radius: 0 6px 6px 0; }
    .vat { font-size: 9px; color: #9a9a92; font-style: italic; margin-top: 8px; text-align: right; }

    /* Répartition (indicatif — notre modèle : commission prélevée sur l'artiste) */
    .split { background: #faf8f4; border: 1px solid #ece7dd; border-radius: 10px; padding: 12px 18px; margin-top: 22px; }
    .split .k { font-size: 8px; letter-spacing: 0.1em; text-transform: uppercase; color: #a0998a; margin-bottom: 8px; }
    .split table { width: 100%; font-size: 10px; }
    .split td { padding: 2px 0; }
    .split td.v { text-align: right; }
    .split tr.net td { font-weight: bold; color: #5f7a63; }

    /* Remerciement */
    .thanks { background: #f6f5f1; border-radius: 10px; padding: 14px 20px; margin-top: 22px; font-size: 10.5px; color: #4a4a4a; }
    .thanks .heart { color: #7a9e7e; font-size: 15px; }
    .thanks strong { color: #2d2d2d; }

    /* Pied de mentions légales */
    .legal { border-top: 1px solid #e6e1d8; margin-top: 26px; padding-top: 12px; display: table; width: 100%; font-size: 8px; color: #9a9a92; line-height: 1.6; }
    .legal .c { display: table-cell; vertical-align: top; width: 33%; padding-right: 12px; }
    .legal strong { color: #6b7280; font-weight: normal; }
</style>
</head>
<body>
    <div class="page">

        <div class="head">
            <div class="c">
                <?php if (file_exists($logoPath)): ?>
                    <img src="<?= $logoPath ?>" class="logo" alt="Toile">
                <?php endif; ?>
                <div class="tagline">Marketplace d'artistes</div>
            </div>
            <div class="c right">
                <div class="doc-title">FACTURE</div>
                <div class="doc-num">N° <?= $e($invoiceNumber) ?></div>
                <div class="doc-dates">
                    Date d'émission : <strong><?= \App\Core\FrenchDate::format('d MMMM y', $issuedAt) ?></strong><br>
                    Date de paiement : <strong><?= \App\Core\FrenchDate::format('d MMMM y', $issuedAt) ?></strong>
                </div>
            </div>
        </div>

        <div class="rule"></div>

        <div class="parties">
            <div class="c">
                <div class="lbl">Facturé à</div>
                <div class="who"><?= $e($billingName) ?></div>
                <div class="addr">
                    <?php if ($hasBilling): ?>
                        <?= $e($order['billing_address_line1']) ?><br>
                        <?php if (!empty($order['billing_address_line2'])): ?><?= $e($order['billing_address_line2']) ?><br><?php endif; ?>
                        <?= $e(trim(($order['billing_postal_code'] ?? '') . ' ' . ($order['billing_city'] ?? ''), ' ')) ?><?php if (!empty($order['billing_country'])): ?>, <?= $e($order['billing_country']) ?><?php endif; ?><br>
                    <?php else: ?>
                        <span style="color:#9a9a92;">Adresse de facturation non renseignée</span><br>
                    <?php endif; ?>
                    <?php if (!empty($order['client_email'])): ?><?= $e($order['client_email']) ?><?php endif; ?>
                </div>
            </div>
            <div class="c">
                <div class="lbl">Émis par</div>
                <div class="who"><?= $e($companyName) ?></div>
                <div class="addr">
                    <?php if (!empty($company['company_address'])): ?><?= nl2br($e($company['company_address'])) ?><br><?php endif; ?>
                    <?php if (!empty($company['contact_email'])): ?><?= $e($company['contact_email']) ?><br><?php endif; ?>
                    <?php if (!empty($company['company_siret'])): ?>SIRET : <?= $e($company['company_siret']) ?><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="order-box">
            <div class="order-ref">COMMANDE N° <?= $e($orderRef) ?></div>
            <div class="order-cols">
                <div class="c">
                    <div class="k">Artiste</div>
                    <div class="v"><strong><?= $e($order['shop_name']) ?></strong><?php if ($artist !== null): ?><br>@<?= $e($artist['username']) ?><?php endif; ?></div>
                </div>
                <div class="c">
                    <div class="k">Service</div>
                    <div class="v"><?= $e($order['service_title'] ?? $order['title']) ?></div>
                </div>
                <div class="c">
                    <div class="k">Statut</div>
                    <div class="v"><span class="badge"><?= $e($statusLabel) ?></span></div>
                </div>
            </div>
        </div>

        <table class="lines">
            <thead>
                <tr>
                    <th style="width:56%;">Désignation</th>
                    <th class="r" style="width:12%;">Qté</th>
                    <th class="r" style="width:16%;">Prix unit.</th>
                    <th class="r" style="width:16%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="desc">
                        <strong><?= $e($order['service_title'] ?? $order['title']) ?></strong>
                        <div class="sub">Réalisé par <?= $e($order['shop_name']) ?></div>
                    </td>
                    <td class="r">1</td>
                    <td class="r"><?= number_format($order['total_price'] / 100, 2, ',', ' ') ?> €</td>
                    <td class="r"><?= number_format($order['total_price'] / 100, 2, ',', ' ') ?> €</td>
                </tr>
            </tbody>
        </table>

        <div class="bottom">
            <div class="c left">
                <div class="pay">
                    <div class="k">Paiement</div>
                    <table>
                        <tr><td class="pk">Mode de paiement</td><td>Carte bancaire</td></tr>
                        <tr><td class="pk">Date de paiement</td><td><?= \App\Core\FrenchDate::format('d MMMM y', $issuedAt) ?></td></tr>
                        <?php if (!empty($order['stripe_payment_intent_id'])): ?>
                            <tr><td class="pk">Transaction</td><td>#<?= $e($order['stripe_payment_intent_id']) ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <div class="c right">
                <div class="tot">
                    <table>
                        <tr class="grand">
                            <td class="k">Total à payer</td>
                            <td class="v"><?= number_format($order['total_price'] / 100, 2, ',', ' ') ?> €</td>
                        </tr>
                    </table>
                    <div class="vat">TVA non applicable, art. 293 B du CGI — montant net de taxe.</div>
                </div>
            </div>
        </div>

        <div class="split">
            <div class="k">Répartition (à titre indicatif pour l'artiste)</div>
            <table>
                <tr><td>Montant réglé par le client</td><td class="v"><?= number_format($order['total_price'] / 100, 2, ',', ' ') ?> €</td></tr>
                <tr><td>Commission plateforme (<?= number_format((float) $order['commission_rate'], 2, ',', ' ') ?> %)</td><td class="v">- <?= number_format($order['commission_amount'] / 100, 2, ',', ' ') ?> €</td></tr>
                <tr class="net"><td>Net perçu par l'artiste</td><td class="v"><?= number_format($netAmount / 100, 2, ',', ' ') ?> €</td></tr>
            </table>
        </div>

        <div class="thanks">
            <span class="heart">&#9829;</span>&nbsp; <strong>Merci d'avoir soutenu un artiste indépendant.</strong>
            Cette facture est disponible dans ton espace.
        </div>

        <div class="legal">
            <div class="c">
                <strong><?= $e($companyName) ?></strong><br>
                <?php if (!empty($company['company_legal'])): ?><?= nl2br($e($company['company_legal'])) ?><br><?php endif; ?>
                <?php if (!empty($company['company_address'])): ?><?= nl2br($e($company['company_address'])) ?><?php endif; ?>
            </div>
            <div class="c">
                <?php if (!empty($company['company_siret'])): ?>SIRET : <?= $e($company['company_siret']) ?><br><?php endif; ?>
                <?php if (!empty($company['company_vat'])): ?>TVA : <?= $e($company['company_vat']) ?><?php endif; ?>
            </div>
            <div class="c">
                <?php if (!empty($company['contact_email'])): ?><?= $e($company['contact_email']) ?><br><?php endif; ?>
                Document généré automatiquement.
            </div>
        </div>

    </div>
</body>
</html>
