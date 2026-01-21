<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Exception\UserNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class MySQLTweetRepository implements TweetRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * @throws UserNotFoundException
     */
    public function add(Tweet $tweet): void
    {
        try {
            $this->entityManager->persist($tweet);
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            throw new UserNotFoundException($tweet->userId());
        }
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws TweetNotFoundException
     * @throws ORMException
     */
    public function getById(string $tweetId): Tweet
    {
        $tweet = $this->entityManager->find(Tweet::class, $tweetId);
        if (null === $tweet) {
            throw new TweetNotFoundException($tweetId);
        }

        return $tweet;
    }

    public function getAllTweets(int $limit, int $offset): array
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Tweet::class, 't')
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return iterator_to_array(new Paginator($query));
    }

    public function countTweets(): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(Tweet::class, 't')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
