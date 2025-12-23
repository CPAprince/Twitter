<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Application\CreateUser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Uid\Uuid;
use Twitter\IAM\Application\CreateUser\CreateUserCommand;
use Twitter\IAM\Application\CreateUser\CreateUserCommandHandler;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\Exception\UserAlreadyExistsException;
use Twitter\IAM\Domain\User\Model\UserRepository;

#[Group('unit')]
#[CoversClass(CreateUserCommandHandler::class)]
class CreateUserCommandHandlerTest extends TestCase
{
    private UserRepository&MockObject $userRepository;
    private CreateUserCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->handler = new CreateUserCommandHandler($this->userRepository);
    }

    protected function tearDown(): void
    {
        unset($this->userRepository, $this->handler);

        parent::tearDown();
    }

    /**
     * @throws InvalidEmailException
     * @throws InvalidPasswordException
     */
    #[Test]
    public function handleReturnsSuccessWhenUserIsCreated(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('add');

        $result = $this->handler->handle(
            new CreateUserCommand(
                'test@example.com',
                'Qwerty.123',
            ),
        );

        $this->assertTrue(Uuid::isValid($result->userId));
    }

    /**
     * @throws InvalidPasswordException
     */
    #[Test]
    public function handleFailsWhenEmailIsInvalid(): void
    {
        $this->expectException(InvalidEmailException::class);

        $this->userRepository->expects(self::never())->method('add');

        $this->handler->handle(
            new CreateUserCommand(
                'test@example',
                'Qwerty.123',
            ),
        );
    }

    /**
     * @throws InvalidEmailException
     * @throws InvalidPasswordException
     */
    #[Test]
    public function handleFailsWhenUserAlreadyExists(): void
    {
        $email = 'test@example.com';

        $this->expectException(UserAlreadyExistsException::class);

        $this->userRepository
            ->expects(self::once())
            ->method('add')
            ->willThrowException(new UserAlreadyExistsException($email));

        $this->handler->handle(
            new CreateUserCommand(
                $email,
                'Qwerty.123',
            ),
        );
    }

    /**
     * @throws InvalidEmailException
     */
    #[Test]
    public function handleFailsWhenPasswordIsInvalid(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->userRepository->expects(self::never())->method('add');

        $this->handler->handle(
            new CreateUserCommand(
                'test@example.com',
                'qwerty1',
            ),
        );
    }

    /**
     * @throws InvalidEmailException
     * @throws InvalidPasswordException
     */
    #[Test]
    public function handleSaveFailsOnUnexpectedException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->userRepository
            ->expects(self::once())
            ->method('add')
            ->willThrowException(new RuntimeException());

        $this->handler->handle(
            new CreateUserCommand(
                'test@example.com',
                'Qwerty.123',
            ),
        );
    }
}
