<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception; //for catching errors

class MailController extends Controller
{
    //
    private $mail;

    public function __construct()
    {
        // Create a new instance of PHPMailer
        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com'; // Specify main and backup SMTP servers
        $this->mail->SMTPAuth   = true;                // Enable SMTP authentication
        $this->mail->Username   = env('MAIL_USERNAME'); // SMTP username
        $this->mail->Password   = env('MAIL_PASSWORD');    // SMTP password
        $this->mail->SMTPSecure = 'tls'; // Enable TLS encryption
        $this->mail->Port       = 587;                   // TCP port to connect to
    }

    public function sendEmail($to, $subject, $body)
    {
        try {
            // Recipients
            $this->mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $this->mail->addAddress($to); // Add a recipient

            // Content
            $this->mail->isHTML(true); // Set email format to HTML
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;

            // Send the email
            $this->mail->send();

            return [
                'code' => 'success',
                'message' => 'Email sent successfully.'
            ];
        } catch (\Exception $error) {
            return [
                'code' => 'error',
                'message' => $error->getMessage()
            ];
        }
    }
}
