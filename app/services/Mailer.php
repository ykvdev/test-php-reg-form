<?php declare(strict_types=1);

namespace app\services;

use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

class Mailer
{
    /** @var array */
    private $mailerConfig;

    /** @var SmtpMailer */
    private $mailer;

    /** @var Container */
    private $services;

    public function __construct(array $mailerConfig, Container $services)
    {
        $this->mailerConfig = $mailerConfig;

        $this->services = $services;

        $this->mailer = new SmtpMailer([
            'host' => $mailerConfig['smtp_host'],
            'username' => $mailerConfig['smtp_username'],
            'password' => $mailerConfig['smtp_password'],
            'secure' => $mailerConfig['smtp_secure'],
        ]);
    }

    public function send(string $toName, string $toEmail, string $subject, string $viewAlias, array $viewVars = []) : void
    {
        $htmlBody = $this->services->viewRenderer()->render($viewAlias, $viewVars);

        $mail = new Message();
        $mail->setFrom($this->mailerConfig['from'])
            ->addTo("{$toName} <$toEmail>")
            ->setSubject($subject)
            ->setBody(strip_tags($htmlBody))
            ->setHtmlBody($htmlBody);

        $this->mailer->send($mail);
    }
}