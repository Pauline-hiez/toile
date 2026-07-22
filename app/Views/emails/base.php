<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject ?? 'Toile') ?></title>
</head>

<body style="margin:0; padding:0; background:#f2efe4; font-family:Georgia, 'Times New Roman', serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2efe4;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:18px; border:1px solid #e6ddc8;">
                    <tr>
                        <td align="center" style="padding:32px 40px 8px;">
                            <img src="cid:email-logo" width="110" alt="Toile" style="display:block; width:110px; height:auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 40px 32px; color:#4a4636; font-size:15px; line-height:1.6;">
                            <?= $content ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 40px; border-top:1px solid #efe9dc; text-align:center; font-size:12px; color:#9a9483;">
                            © <?= date('Y') ?> Toile — La marketplace de commissions artistiques.<br>
                            Tu reçois cet email car tu es inscrit(e) sur Toile.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
