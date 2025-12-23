<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Twitter\IAM\Domain\User\Model\Exception\UserAlreadyExistsException;
use Twitter\IAM\Domain\User\Model\User;
use Twitter\IAM\Domain\User\Model\UserRepository;

final readonly class MySQLUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws UserAlreadyExistsException
     */
    public function add(User $user): void
    {
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            throw new UserAlreadyExistsException($user->email());
        }
    }

    public function existsByEmail(string $email): bool
    {
        return 1 === (int) $this->entityManager
            ->createQueryBuilder()
            ->select('count(u)')
            ->from(User::class, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
