<?php

declare(strict_types=1);

namespace Twitter\Tests\Registration\Application\UseCase\RegisterUser;

use Assert\LazyAssertionException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Twitter\IAM\Domain\User\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Exception\UserAlreadyExistsException;
use Twitter\Profile\Domain\Profile\Exception\UserNotFoundException;
use Twitter\Registration\Application\Message\SendWelcomeEmailMessage;
use Twitter\Registration\Application\UseCase\RegisterUser\RegisterUserCommand;
use Twitter\Registration\Application\UseCase\RegisterUser\RegisterUserCommandHandler;

#[Group('component')]
#[CoversClass(RegisterUserCommandHandler::class)]
final class RegisterUserCommandHandlerTest extends TestCase
{
    private RegisterUserCommandHandler $handler;
    private EntityManagerInterface&MockObject $entityManager;
    private Connection&MockObject $connection;
    private MessageBusInterface&MockObject $messageBus;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new RegisterUserCommandHandler(
            $this->entityManager,
            $this->messageBus,
        );
    }

    #[Test]
    public function createsUserAndProfileInSingleTransaction(): void
    {
        $this->entityManager
            ->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection
            ->expects(self::once())
            ->method('beginTransaction');

        $this->connection
            ->expects(self::once())
            ->method('commit');

        $this->connection
            ->expects(self::never())
            ->method('rollBack');

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('persist');

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn (mixed $message): bool => $message instanceof SendWelcomeEmailMessage
                    && 'test@example.com' === $message->email
                    && 'John Doe' === $message->name,
            ))
            ->willReturn(new Envelope(new stdClass()));

        $result = $this->handler->handle(new RegisterUserCommand(
            email: 'test@example.com',
            password: 'Qwerty.123',
            name: 'John Doe',
            bio: 'Bio',
        ));

        self::assertNotEmpty($result->userId);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $result->userId,
        );
    }

    /**
     * @throws UserNotFoundException
     * @throws \Throwable
     * @throws InvalidPasswordException
     * @throws UserAlreadyExistsException
     * @throws Exception
     * @throws ExceptionInterface
     */
    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function failsWhenEmailIsInvalid(): void
    {
        $this->expectException(InvalidEmailException::class);

        $this->entityManager
            ->expects(self::never())
            ->method('getConnection');

        $this->messageBus
            ->expects(self::never())
            ->method('dispatch');

        $this->handler->handle(new RegisterUserCommand(
            email: 'invalid',
            password: 'Qwerty.123',
            name: 'John Doe',
            bio: null,
        ));
    }

    /**
     * @throws UserNotFoundException
     * @throws \Throwable
     * @throws InvalidEmailException
     * @throws Exception
     * @throws ExceptionInterface
     * @throws UserAlreadyExistsException
     */
    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function failsWhenPasswordIsInvalid(): void
    {
        $this->expectException(InvalidPasswordException::class);

        $this->entityManager
            ->expects(self::never())
            ->method('getConnection');

        $this->messageBus
            ->expects(self::never())
            ->method('dispatch');

        $this->handler->handle(new RegisterUserCommand(
            email: 'test@example.com',
            password: 'invalid',
            name: 'John Doe',
            bio: null,
        ));
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidPasswordException
     * @throws UserAlreadyExistsException
     * @throws \Throwable
     * @throws InvalidEmailException
     * @throws Exception
     * @throws ExceptionInterface
     */
    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function failsWhenProfileNameIsInvalid(): void
    {
        $this->expectException(LazyAssertionException::class);

        $this->entityManager
            ->expects(self::never())
            ->method('getConnection');

        $this->messageBus
            ->expects(self::never())
            ->method('dispatch');

        $this->handler->handle(new RegisterUserCommand(
            email: 'test@example.com',
            password: 'Qwerty.123',
            name: 'ab',
            bio: null,
        ));
    }

    /**
     * @throws UserNotFoundException
     * @throws \Throwable
     * @throws InvalidPasswordException
     * @throws InvalidEmailException
     * @throws ExceptionInterface
     * @throws Exception
     */
    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function rollsBackWhenUserAlreadyExists(): void
    {
        $this->expectException(UserAlreadyExistsException::class);

        $this->entityManager
            ->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection
            ->expects(self::once())
            ->method('beginTransaction');

        $this->connection
            ->expects(self::once())
            ->method('rollBack');

        $this->connection
            ->expects(self::never())
            ->method('commit');

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('persist');

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->willThrowException($this->createMock(UniqueConstraintViolationException::class));

        $this->messageBus
            ->expects(self::never())
            ->method('dispatch');

        $this->handler->handle(new RegisterUserCommand(
            email: 'test@example.com',
            password: 'Qwerty.123',
            name: 'John Doe',
            bio: null,
        ));
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidPasswordException
     * @throws UserAlreadyExistsException
     * @throws \Throwable
     * @throws InvalidEmailException
     * @throws ExceptionInterface
     * @throws Exception
     */
    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function propagatesUnexpectedFailuresAndRollsBack(): void
    {
        $this->expectException(RuntimeException::class);

        $this->entityManager
            ->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection
            ->expects(self::once())
            ->method('beginTransaction');

        $this->connection
            ->expects(self::once())
            ->method('rollBack');

        $this->connection
            ->expects(self::never())
            ->method('commit');

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('persist');

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->willThrowException(new RuntimeException());

        $this->messageBus
            ->expects(self::never())
            ->method('dispatch');

        $this->handler->handle(new RegisterUserCommand(
            email: 'test@example.com',
            password: 'Qwerty.123',
            name: 'John Doe',
            bio: null,
        ));
    }
}
