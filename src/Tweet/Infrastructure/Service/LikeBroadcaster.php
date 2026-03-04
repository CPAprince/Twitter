<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Throwable;

final readonly class LikeBroadcaster implements LikeBroadcasterInterface
{
    public function __construct(
        private HubInterface $hub,
        private string $topicBaseUrl,
        private ?LoggerInterface $logger = null,
    ) {}

    public function broadcast(string $tweetId, string $userId, int $likesCount): void
    {
        $parsedUrl = parse_url($this->topicBaseUrl);
        $scheme = $parsedUrl['scheme'] ?? 'https';
        $host = $parsedUrl['host'] ?? 'localhost';
        $port = $parsedUrl['port'] ?? null;

        if (('https' === $scheme && 443 === $port) || ('http' === $scheme && 80 === $port)) {
            $port = null;
        }

        $normalizedBase = $scheme.'://'.$host.(null !== $port ? ':'.$port : '');
        $topic = $normalizedBase.'/tweets/likes';

        $update = new Update(
            topics: [$topic],
            data: json_encode([
                'tweetId' => $tweetId,
                'likesCount' => $likesCount,
                'triggeredBy' => $userId,
            ], JSON_THROW_ON_ERROR),
        );

        try {
            $this->hub->publish($update);
        } catch (Throwable $e) {
            $this->logger?->warning('Failed to broadcast like update', [
                'tweetId' => $tweetId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
