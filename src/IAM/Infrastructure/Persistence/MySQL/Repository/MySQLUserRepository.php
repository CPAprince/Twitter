<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Twitter\IAM\Domain\User\Model\User;
use Twitter\IAM\Domain\User\Model\UserRepository;

final readonly class MySQLUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws ORMInvalidArgumentException
     */
    public function add(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
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
