<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Report;

class ReportController
{
    private Report $reportModel;

    public function __construct(Renderer $renderer)
    {
        $this->reportModel = new Report();
    }

    // Signale une boutique ou un avis (POST /reports)
    public function store(): void
    {
        $reporterId = $_SESSION['user_id'];
        $type = $_POST['reportable_type'] ?? '';
        $reportableId = (int) ($_POST['reportable_id'] ?? 0);
        $reason = $_POST['reason'] ?? 'autre';
        $message = trim($_POST['message'] ?? '');

        // N'accepte de rediriger que vers un chemin interne relatif,
        // pour éviter une redirection ouverte vers un domaine externe.
        $redirect = $_POST['redirect'] ?? '/';
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/';
        }

        if (!in_array($type, ['shop', 'review'], true) || $reportableId <= 0) {
            http_response_code(400);
            echo 'Signalement invalide.';
            exit;
        }

        if (!array_key_exists($reason, Report::reasonLabels())) {
            $reason = 'autre';
        }

        // Un même utilisateur ne peut signaler deux fois le même contenu.
        if ($this->reportModel->findExisting($reporterId, $type, $reportableId) === null) {
            $this->reportModel->create([
                'reporter_id' => $reporterId,
                'reportable_type' => $type,
                'reportable_id' => $reportableId,
                'reason' => $reason,
                'message' => $message ?: null,
            ]);
        }

        header('Location: ' . $redirect);
        exit;
    }
}
