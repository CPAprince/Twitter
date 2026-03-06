<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Moderation;

use Twitter\Tweet\Application\Moderation\ModerationConfig;
use Twitter\Tweet\Application\Moderation\ModerationQueueInterface;

final readonly class RedisModerationQueue implements ModerationQueueInterface
{
    public function __construct(
        private \Redis $redis,
        private ModerationConfig $config,
    ) {
    }

    public function enqueue(string $tweetId): void
    {
        $tweetId = trim($tweetId);

        if ($tweetId === '') {
            throw new \InvalidArgumentException('tweetId must not be empty.');
        }

        $this->redis->rPush($this->config->redisQueueKey, $tweetId);
    }

    public function peek(int $limit): array
    {
        if ($limit <= 0) {
            throw new \InvalidArgumentException('limit must be greater than 0.');
        }

        $items = $this->redis->lRange(
            $this->config->redisQueueKey,
            0,
            $limit - 1
        );

        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(
            array_map(
                static fn (mixed $item): string => is_string($item) ? trim($item) : '',
                $items
            ),
            static fn (string $item): bool => $item !== ''
        ));
    }

    public function remove(array $tweetIds): void
    {
        if ($tweetIds === []) {
            return;
        }

        $queueKey = $this->config->redisQueueKey;
        $queueItems = $this->redis->lRange($queueKey, 0, -1);

        if (!is_array($queueItems) || $queueItems === []) {
            return;
        }

        $tweetIdsToRemove = array_values(array_filter(
            array_map(
                static fn (mixed $item): string => is_string($item) ? trim($item) : '',
                $tweetIds
            ),
            static fn (string $item): bool => $item !== ''
        ));

        if ($tweetIdsToRemove === []) {
            return;
        }

        $removeSet = array_fill_keys($tweetIdsToRemove, true);

        $remaining = [];
        $removedCounts = [];

        foreach ($tweetIdsToRemove as $tweetId) {
            $removedCounts[$tweetId] = 0;
        }

        foreach ($queueItems as $item) {
            $value = is_string($item) ? trim($item) : '';

            if ($value === '') {
                continue;
            }

            if (isset($removeSet[$value]) && $removedCounts[$value] === 0) {
                $removedCounts[$value]++;
                continue;
            }

            $remaining[] = $value;
        }

        $this->redis->multi();

        $this->redis->del($queueKey);

        if ($remaining !== []) {
            $this->redis->rPush($queueKey, ...$remaining);
        }

        $this->redis->exec();
    }

    public function size(): int
    {
        return $this->redis->lLen($this->config->redisQueueKey);
    }
}
