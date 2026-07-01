<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Notification;

class NotificationController
{
    private Renderer $renderer;
    private Notification $notificationModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->notificationModel = new Notification;
    }

    public function index(): void
    {
        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->findByUserId($userId);

        $this->notificationModel->markAllAsRead($userId);

        $this->renderer->render('notification/index', [
            'notifications' => $notifications,
            'pageTitle' => 'Notifications - Toile',
        ]);
    }
}
