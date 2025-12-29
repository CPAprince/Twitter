<?php

declare(strict_types=1);

namespace Twitter\Profile\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\EntityIdentityCollisionException;
use Twitter\Profile\Domain\Profile\Exception\ProfileAlreadyExistsException;
use Twitter\Profile\Domain\Profile\Exception\UserNotFoundException;
use Twitter\Profile\Domain\Profile\Model\Profile;
use Twitter\Profile\Domain\Profile\Model\ProfileRepository;

final readonly class MySQLProfileRepository implements ProfileRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * @throws UserNotFoundException
     * @throws ProfileAlreadyExistsException
     */
    public function add(Profile $profile): void
    {
        try {
            $this->entityManager->persist($profile);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException|EntityIdentityCollisionException) {
            throw new ProfileAlreadyExistsException($profile->userId());
        } catch (ForeignKeyConstraintViolationException) {
            throw new UserNotFoundException($profile->userId());
        }
    }
}
