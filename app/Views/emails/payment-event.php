<?php
/**
 * @var string $tone 'success' (paiement débité) ou 'refund' (remboursement) — pilote la couleur/le symbole du badge.
 */
$tone = $tone ?? 'success';
$badgeColor = $tone === 'refund' ? '#a89a82' : '#7a9e7e';
$badgeSymbol = $tone === 'refund' ? '↺' : '✓';

ob_start();
?>
<div style="text-align:center; margin-bottom:20px;">
    <div style="display:inline-block; width:72px; height:72px; line-height:72px; border-radius:50%; background:<?= $badgeColor ?>; color:#ffffff; font-size:32px; font-weight:bold;">
        <?= $badgeSymbol ?>
    </div>
</div>

<h2 style="font-family:Georgia, serif; font-size:22px; color:#a89a82; text-align:center; margin:0 0 16px;">
    <?= $tone === 'refund' ? 'Remboursement en cours' : 'Paiement confirmé' ?>
</h2>

<p style="text-align:center; margin:0 0 8px;">Bonjour <?= htmlspecialchars($username) ?>,</p>
<p style="text-align:center; margin:0 0 20px;"><?= htmlspecialchars($message) ?></p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f4ec; border-radius:12px; margin:0 0 24px;">
    <tr>
        <td style="padding:16px 20px; font-size:14px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="color:#9a9483; padding:4px 0;">Commande</td>
                    <td style="text-align:right; font-weight:bold; color:#4a4636; padding:4px 0;"><?= htmlspecialchars($orderTitle) ?></td>
                </tr>
                <tr>
                    <td style="color:#9a9483; padding:4px 0;">Montant</td>
                    <td style="text-align:right; font-weight:bold; color:#4a4636; padding:4px 0;"><?= number_format($amount / 100, 2) ?> €</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div style="text-align:center;">
    <a href="<?= htmlspecialchars($_ENV['APP_URL'] ?? 'http://toile.test') ?>/commandes/<?= (int) $orderId ?>" style="display:inline-block; padding:12px 32px; background:#7a9e7e; color:#ffffff; text-decoration:none; border-radius:999px; font-weight:bold; font-size:14px;">
        Voir ma commande
    </a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
