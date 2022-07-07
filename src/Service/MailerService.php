<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerService
{

    private $mailer;
    private $from = 'admin@paraeljinene.tn';
    private $name = 'Para El Jinene';

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendTemplatedEmail(string $to, string $subject, string $username, string $templateEmail): void // the day before the whichday
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->from, $this->name))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($templateEmail)
            ->context([
                'username' => $username,
            ]);
        $this->mailer->send($email);
    }
}