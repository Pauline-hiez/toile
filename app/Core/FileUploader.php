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
     * @param array  $file              Une entrée de $_FILES (un seul fichier).
     * @param string $destinationFolder Chemin absolu du dossier de destination.
     */
    public static function upload(array $file, string $destinationFolder): array
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_TYPES, true)) {
            return ['filename' => null, 'error' => 'Format de fichier non autorisé (jpg, png, webp uniquement).'];
        }

        if ($file['size'] > self::MAX_SIZE) {
            return ['filename' => null, 'error' => 'Le fichier dépasse la taille maximale de 2 Mo.'];
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        };

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
