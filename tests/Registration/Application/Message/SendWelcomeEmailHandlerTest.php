<?php

declare(strict_types=1);

namespace Twitter\Tests\Registration\Application\Message;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twitter\Registration\Application\Message\SendWelcomeEmailHandler;
use Twitter\Registration\Application\Message\SendWelcomeEmailMessage;

#[Group('unit')]
#[CoversClass(SendWelcomeEmailHandler::class)]
final class SendWelcomeEmailHandlerTest extends TestCase
{
    private MailerInterface&MockObject $mailer;
    private SendWelcomeEmailHandler $handler;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);

        $this->handler = new SendWelcomeEmailHandler(
            mailer: $this->mailer,
            senderAddress: 'noreply@twitter.local',
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Test]
    public function sendsWelcomeEmailToRegisteredUser(): void
    {
        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(
                static fn (mixed $email): bool => $email instanceof Email
                    && in_array('jane@example.com', array_map(
                        static fn (Address $a): string => $a->getAddress(),
                        $email->getTo(),
                    ), true)
                    && 'noreply@twitter.local' === $email->getFrom()[0]->getAddress(),
            ));

        ($this->handler)(new SendWelcomeEmailMessage(
            email: 'jane@example.com',
            name: 'Jane Doe',
        ));
    }
}
