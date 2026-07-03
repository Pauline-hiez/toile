<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Envoie un email.
     *
     * @param string $to          Adresse du destinataire.
     * @param string $subject     Sujet de l'email.
     * @param string $htmlBody    Corps HTML de l'email.
     * @param string $type        Type d'email pour le log (ex: 'inscription').
     */
    public static function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $type = 'general'
    ): bool {
        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) $_ENV['MAIL_PORT'];
            $mail->CharSet = 'UTF-8';

            // Expéditeur et destinataire
            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($to);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();

            self::log($to, $type, $subject, 'sent');
            return true;
        } catch (Exception $e) {
            self::log($to, $type, $subject, 'failed', $mail->ErrorInfo);

            return false;
        }
    }

    private static function log(
        string $to,
        string $type,
        string $subject,
        string $status,
        ?string $errorMessage = null
    ): void {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                'INSERT INTO email_log (recipient_email, type, subject, status, error_message)
                VALUES (:email, :type, :subject, :status, :error)'
            );
            $stmt->execute([
                'email' => $to,
                'type' => $type,
                'subject' => $subject,
                'status' => $status,
                'error' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            // Si le log échour, ne rien faire
        }
    }

    /**
     * Génère le HTML d'un email à partir d'un template PHP.
     *
     * @param string $template Nom du template (sans extension),
     *                         dans app/Views/emails/.
     * @param array  $data     Variables disponibles dans le template.
     */
    public static function renderTemplate(string $template, array $data = []): string
    {
        $templateFile = __DIR__ . '/../../app/views/emails/' . $template . '.php';

        if (!file_exists($templateFile)) {
            throw new \RuntimeException("Template email introuvable : {$templateFile}");
        }

        extract($data);

        ob_start();
        require $templateFile;
        return ob_get_clean();
    }
}
