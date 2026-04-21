<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Infrastructure\Service;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twitter\Tweet\Infrastructure\Service\LikeBroadcaster;

#[Group('unit')]
#[CoversClass(LikeBroadcaster::class)]
final class LikeBroadcasterTest extends TestCase
{
    private HubInterface&MockObject $hub;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->hub = $this->createMock(HubInterface::class);
        $this->logger = $this->createStub(LoggerInterface::class);
    }

    #[Test]
    public function broadcastPublishesUpdateWithCorrectPayload(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $likesCount = 5;

        $this->hub
            ->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(function (Update $update) use ($tweetId, $userId, $likesCount): bool {
                    self::assertSame(['https://example.com/tweets/likes'], $update->getTopics());

                    $payload = json_decode($update->getData(), true);
                    self::assertIsArray($payload);
                    self::assertSame($tweetId, $payload['tweetId'] ?? null);
                    self::assertSame($likesCount, $payload['likesCount'] ?? null);
                    self::assertSame($userId, $payload['triggeredBy'] ?? null);

                    return true;
                }),
            )
            ->willReturn('update-id');

        $broadcaster = new LikeBroadcaster($this->hub, 'https://example.com:443', $this->logger);
        $broadcaster->broadcast($tweetId, $userId, $likesCount);
    }

    #[Test]
    public function broadcastLogsWarningWhenHubPublishFails(): void
    {
        $this->hub
            ->expects(self::once())
            ->method('publish')
            ->willThrowException(new RuntimeException('hub down'));

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                'Failed to broadcast like update',
                self::callback(static function (array $context): bool {
                    return ($context['tweetId'] ?? null) === '019b5f3f-d110-7908-9177-5df439942a8b'
                        && ($context['error'] ?? null) === 'hub down';
                }),
            );

        $broadcaster = new LikeBroadcaster($this->hub, 'https://example.com', $this->logger);
        $broadcaster->broadcast('019b5f3f-d110-7908-9177-5df439942a8b', '550e8400-e29b-41d4-a716-446655440000', 1);
    }

    #[Test]
    #[DataProvider('topicNormalizationProvider')]
    public function broadcastNormalizesUrlByStrippingDefaultPorts(string $topicBaseUrl, string $expectedTopic): void
    {
        $this->hub
            ->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(function (Update $update) use ($expectedTopic): bool {
                    self::assertSame([$expectedTopic], $update->getTopics());

                    return true;
                }),
            )
            ->willReturn('update-id');

        $broadcaster = new LikeBroadcaster($this->hub, $topicBaseUrl, $this->logger);
        $broadcaster->broadcast('019b5f3f-d110-7908-9177-5df439942a8b', '550e8400-e29b-41d4-a716-446655440000', 1);
    }

    public static function topicNormalizationProvider(): Generator
    {
        yield 'https default port is stripped' => ['https://example.com:443', 'https://example.com/tweets/likes'];
        yield 'http default port is stripped' => ['http://example.com:80', 'http://example.com/tweets/likes'];
        yield 'custom https port is kept' => ['https://example.com:8080', 'https://example.com:8080/tweets/likes'];
        yield 'no port stays as-is' => ['https://example.com', 'https://example.com/tweets/likes'];
    }
}
