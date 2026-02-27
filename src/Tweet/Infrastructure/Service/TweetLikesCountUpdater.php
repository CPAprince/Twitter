<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Service;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

final readonly class TweetLikesCountUpdater implements TweetLikesCountUpdaterInterface
{
    public function __construct(
        private Connection $connection,
        private ?LoggerInterface $logger = null,
    ) {}

    /**
     * Atomically update likes_count and return the new value.
     * Returns null if the tweet does not exist.
     */
    public function updateCount(string $tweetId, int $delta): ?int
    {
        $binaryId = pack('H*', str_replace('-', '', $tweetId));

        $affected = $this->connection->executeStatement(
            'UPDATE tweets SET likes_count = GREATEST(0, CAST(likes_count AS SIGNED) + :delta) WHERE id = :id',
            ['delta' => $delta, 'id' => $binaryId],
        );

        if (0 === $affected) {
            $this->logger?->warning('Tweet not found for likes count update', ['tweetId' => $tweetId]);

            return null;
        }

        $newCount = $this->connection->fetchOne(
            'SELECT likes_count FROM tweets WHERE id = :id',
            ['id' => $binaryId],
        );

        return (int) $newCount;
    }
}
