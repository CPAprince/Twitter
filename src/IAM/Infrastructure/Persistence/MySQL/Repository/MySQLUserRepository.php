<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Twitter\IAM\Domain\User\Model\Email;
use Twitter\IAM\Domain\User\Model\User;
use Twitter\IAM\Domain\User\Model\UserRepository;

final class MySQLUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ORMInvalidArgumentException
     */
    public function add(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function existsByEmail(Email $email): bool
    {
        return 1 === $this->entityManager->createQueryBuilder()
                ->select('count(u)')
                ->from(User::class, 'u')
                ->where('u.email = :email')
                ->setParameter('email', $email->toString())
                ->getQuery()
                ->getSingleScalarResult();
    }
}
