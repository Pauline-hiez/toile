<?php

namespace App\Core;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Wrapper Dompdf minimal, réutilisé par InvoiceController pour les deux
 * types de factures (commande, abonnement) — centralise la config (police
 * par défaut avec accents, format papier) pour ne pas la répéter à chaque
 * appel.
 */
class PdfService
{
    /**
     * Génère le PDF à partir d'un HTML déjà rendu et le renvoie en
     * téléchargement direct (Content-Disposition: attachment).
     */
    public static function download(string $html, string $filename): void
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        // Chroot par défaut = le dossier de dompdf lui-même, ce qui bloque
        // silencieusement toute image chargée depuis un chemin local du
        // projet (ex: le logo dans les gabarits pdf/*.php) — sans erreur,
        // juste le texte alt affiché à la place. On l'étend à la racine du
        // projet pour autoriser ces images locales.
        $options->set('chroot', [realpath(dirname(__DIR__, 2))]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => true]);
    }
}
