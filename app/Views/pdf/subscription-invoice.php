<?php
/**
 * Facture d'abonnement (conforme franchise en base de TVA), même mise en
 * page que la facture de commande (voir order-invoice.php pour les notes
 * Dompdf). Relation : la plateforme (émetteur) facture l'artiste (client)
 * pour son abonnement.
 *
 * @var array $invoice  Voir SubscriptionInvoice::findByIdWithShop() (invoice_number, owner_*...).
 * @var array<string, string> $company  Réglages (Paramètres admin).
 */
$logoPath = dirname(__DIR__, 3) . '/public/assets/images/site/logo-invoice.png';
$invoiceNumber = $invoice['invoice_number'] ?: ('FAC-' . $invoice['id']);
$subRef = 'ABO-' . date('Y', strtotime($invoice['paid_at'])) . '-' . sprintf('%05d', $invoice['id']);
$hasOwnerAddress = !empty($invoice['owner_address_line1']);

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

    .parties { display: table; width: 100%; margin-bottom: 26px; }
    .parties .c { display: table-cell; vertical-align: top; width: 50%; padding-right: 18px; }
    .lbl { font-size: 8.5px; letter-spacing: 0.14em; text-transform: uppercase; color: #7a9e7e; margin-bottom: 8px; }
    .who { font-size: 13px; font-weight: bold; color: #2d2d2d; margin-bottom: 4px; }
    .addr { font-size: 10px; color: #555; line-height: 1.65; }

    .order-box { background: #f6f5f1; border-radius: 12px; padding: 16px 20px; margin-bottom: 26px; }
    .order-ref { font-size: 12px; font-weight: bold; color: #7a9e7e; letter-spacing: 0.02em; margin-bottom: 12px; }
    .order-cols { display: table; width: 100%; }
    .order-cols .c { display: table-cell; vertical-align: top; width: 33%; padding-right: 12px; }
    .order-cols .k { font-size: 8px; letter-spacing: 0.12em; text-transform: uppercase; color: #a0998a; margin-bottom: 4px; }
    .order-cols .v { font-size: 11px; color: #3a3a3a; line-height: 1.45; }
    .order-cols .v strong { font-weight: bold; color: #2d2d2d; }
    .badge { display: inline-block; background: #e8f0ea; color: #5f7a63; font-size: 10px; font-weight: bold; padding: 4px 14px; border-radius: 999px; }

    table.lines { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.lines th { font-size: 8.5px; letter-spacing: 0.1em; text-transform: uppercase; color: #7a9e7e; font-weight: bold; text-align: left; padding: 0 10px 10px; border-bottom: 1px solid #e6e1d8; }
    table.lines td { padding: 14px 10px; border-bottom: 1px solid #efeae1; font-size: 10.5px; vertical-align: top; }
    table.lines .r { text-align: right; }
    table.lines .desc strong { font-size: 11px; color: #2d2d2d; }
    table.lines .desc .sub { font-size: 9px; color: #9a9a92; margin-top: 2px; }

    .bottom { display: table; width: 100%; margin-top: 18px; }
    .bottom .c { display: table-cell; vertical-align: top; }
    .bottom .left { width: 52%; padding-right: 20px; }
    .bottom .right { width: 48%; }

    .pay .k { font-size: 8.5px; letter-spacing: 0.12em; text-transform: uppercase; color: #7a9e7e; margin-bottom: 8px; }
    .pay table { width: 100%; font-size: 10px; color: #555; }
    .pay td { padding: 2px 0; }
    .pay td.pk { color: #9a9a92; width: 42%; }

    .tot table { width: 100%; }
    .tot tr.grand td { font-size: 15px; font-weight: bold; color: #5f7a63; background: #e8f0ea; padding: 12px 12px; }
    .tot tr.grand td.k { color: #5f7a63; border-radius: 6px 0 0 6px; }
    .tot tr.grand td.v { text-align: right; border-radius: 0 6px 6px 0; }
    .vat { font-size: 9px; color: #9a9a92; font-style: italic; margin-top: 8px; text-align: right; }

    .thanks { background: #f6f5f1; border-radius: 10px; padding: 14px 20px; margin-top: 22px; font-size: 10.5px; color: #4a4a4a; }
    .thanks .heart { color: #7a9e7e; font-size: 15px; }
    .thanks strong { color: #2d2d2d; }

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
                    Date d'émission : <strong><?= \App\Core\FrenchDate::format('d MMMM y', $invoice['paid_at']) ?></strong><br>
                    Date de paiement : <strong><?= \App\Core\FrenchDate::format('d MMMM y', $invoice['paid_at']) ?></strong>
                </div>
            </div>
        </div>

        <div class="rule"></div>

        <div class="parties">
            <div class="c">
                <div class="lbl">Facturé à</div>
                <div class="who"><?= $e($invoice['shop_name']) ?></div>
                <div class="addr">
                    <?= $e($invoice['owner_username']) ?><br>
                    <?php if ($hasOwnerAddress): ?>
                        <?= $e($invoice['owner_address_line1']) ?><br>
                        <?php if (!empty($invoice['owner_address_line2'])): ?><?= $e($invoice['owner_address_line2']) ?><br><?php endif; ?>
                        <?= $e(trim(($invoice['owner_postal_code'] ?? '') . ' ' . ($invoice['owner_city'] ?? ''), ' ')) ?><?php if (!empty($invoice['owner_country'])): ?>, <?= $e($invoice['owner_country']) ?><?php endif; ?><br>
                    <?php endif; ?>
                    <?php if (!empty($invoice['owner_email'])): ?><?= $e($invoice['owner_email']) ?><?php endif; ?>
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
            <div class="order-ref">ABONNEMENT N° <?= $e($subRef) ?></div>
            <div class="order-cols">
                <div class="c">
                    <div class="k">Formule</div>
                    <div class="v"><strong><?= $e($invoice['plan_name']) ?></strong></div>
                </div>
                <div class="c">
                    <div class="k">Période facturée</div>
                    <div class="v"><?= \App\Core\FrenchDate::format('d MMM y', $invoice['period_start']) ?> → <?= \App\Core\FrenchDate::format('d MMM y', $invoice['period_end']) ?></div>
                </div>
                <div class="c">
                    <div class="k">Statut</div>
                    <div class="v"><span class="badge">Payée</span></div>
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
                        <strong>Abonnement <?= $e($invoice['plan_name']) ?></strong>
                        <div class="sub">1 mois — abonnement mensuel de la plateforme</div>
                    </td>
                    <td class="r">1</td>
                    <td class="r"><?= number_format($invoice['amount'] / 100, 2, ',', ' ') ?> €</td>
                    <td class="r"><?= number_format($invoice['amount'] / 100, 2, ',', ' ') ?> €</td>
                </tr>
            </tbody>
        </table>

        <div class="bottom">
            <div class="c left">
                <div class="pay">
                    <div class="k">Paiement</div>
                    <table>
                        <tr><td class="pk">Mode de paiement</td><td>Carte bancaire</td></tr>
                        <tr><td class="pk">Date de paiement</td><td><?= \App\Core\FrenchDate::format('d MMMM y', $invoice['paid_at']) ?></td></tr>
                        <tr><td class="pk">Référence Stripe</td><td><?= $e($invoice['stripe_invoice_id']) ?></td></tr>
                    </table>
                </div>
            </div>
            <div class="c right">
                <div class="tot">
                    <table>
                        <tr class="grand">
                            <td class="k">Total réglé</td>
                            <td class="v"><?= number_format($invoice['amount'] / 100, 2, ',', ' ') ?> €</td>
                        </tr>
                    </table>
                    <div class="vat">TVA non applicable, art. 293 B du CGI — montant net de taxe.</div>
                </div>
            </div>
        </div>

        <div class="thanks">
            <span class="heart">&#9829;</span>&nbsp; <strong>Merci pour ta confiance.</strong>
            Cette facture est disponible dans ton espace artiste.
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
