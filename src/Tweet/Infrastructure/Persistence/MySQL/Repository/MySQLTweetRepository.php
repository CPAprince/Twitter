<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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

    public function getAllTweets( int $limit=100, int $page=1, string $UserId = ""): array
    {

        $limit = $limit<=0 ? 100 : min(100,$limit);
        $offset = ($page-1)*$limit;
        $offset = $offset < 0 ? 0 : $offset;

        /** @var list<Tweet> $tweets */

        if ($UserId === "") {
            $tweets = $this->entityManager
                ->getRepository(Tweet::class)
                ->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        } else {

            $binaryUserId = pack("H*", str_replace('-', '', $UserId));
            $tweets = $this->entityManager
                ->getRepository(Tweet::class)->findBy(['userId' => $binaryUserId], ['createdAt' => 'DESC'], $limit, $offset);

        }


        return $tweets;
    }
}
