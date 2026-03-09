<?php

declare(strict_types=1);

namespace Twitter\Registration\Application\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendWelcomeEmailHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private string $senderAddress,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(SendWelcomeEmailMessage $message): void
    {
        $email = new TemplatedEmail()
            ->from($this->senderAddress)
            ->to($message->email)
            ->subject('Welcome to Twitter!')
            ->htmlTemplate('email/welcome.html.twig')
            ->context([
                'name' => $message->name,
            ]);

        $this->mailer->send($email);
    }
}
