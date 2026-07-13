<?php

namespace App\Core;

class FileUploader
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_SIZE = 2 * 1024 * 1024; // 2 Mo

    /**
     * Valide et déplace un fichier uploadé vers le dossier de destination.
     * Renvoie ['filename' => string|null, 'error' => string|null].
     *
     * @param array $file Une entrée de $_FILES (un seul fichier).
     * @param string $destinationFolder Chemin absolu du dossier de destination.
     * @param array|null $allowedTypes Types MIME autorisés (défaut : jpg/png/webp).
     * @param int|null $maxSize Taille max en octets (défaut : 2 Mo).
     */
    public static function upload(
        array $file,
        string $destinationFolder,
        ?array $allowedTypes = null,
        ?int $maxSize = null
    ): array {
        $allowedTypes = $allowedTypes ?? self::ALLOWED_TYPES;
        $maxSize = $maxSize ?? self::MAX_SIZE;

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes, true)) {
            return ['filename' => null, 'error' => 'Format de fichier non autorisé.'];
        }

        if ($file['size'] > $maxSize) {
            $maxMo = round($maxSize / (1024 * 1024), 1);
            return ['filename' => null, 'error' => "Le fichier dépasse la taille maximale de {$maxMo} Mo."];
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/x-icon', 'image/vnd.microsoft.icon' => 'ico',
            default => null,
        };

        if ($extension === null) {
            return ['filename' => null, 'error' => 'Format de fichier non pris en charge.'];
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = rtrim($destinationFolder, '/') . '/' . $filename;

        if (!is_dir($destinationFolder)) {
            mkdir($destinationFolder, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['filename' => null, 'error' => 'Erreur lors de l\'enregistrement du fichier.'];
        }

        return ['filename' => $filename, 'error' => null];
    }
}
