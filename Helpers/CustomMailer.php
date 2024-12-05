<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class CustomMailer
{
    protected $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp1.example.com;smtp2.example.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'user@example.com';
        $this->mailer->Password = 'secret';
        $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port = 587;

        // Sender
        $this->mailer->setFrom('from@example.com', 'Mailer');
    }

    public function sendEmail($to, $subject, $htmlContent)
    {
        try {
            // Recipients
            $this->mailer->addAddress($to);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlContent;

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
