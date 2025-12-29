<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Twitter\IAM\Domain\User\Exception\UserAlreadyExistsException;
use Twitter\IAM\Domain\User\Model\User;
use Twitter\IAM\Domain\User\Model\UserRepository;

final readonly class MySQLUserRepository implements UserRepository
{
    public function __construct(private ManagerRegistry $registry) {}

    /**
     * @throws UserAlreadyExistsException
     */
    public function add(User $user): void
    {
        try {
            $manager = $this->registry->getManagerForClass(User::class);
            $manager->persist($user);
            $manager->flush();
        } catch (UniqueConstraintViolationException) {
            throw new UserAlreadyExistsException($user->email());
        }
    }
}
