<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
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

    public function getById(string $tweetId): Tweet
    {
        /** @var Tweet|null $tweet */
        $tweet = $this->entityManager->find(Tweet::class, $tweetId);

        if (null === $tweet) {
            throw new TweetNotFoundException($tweetId);
        }

        return $tweet;
    }
}
