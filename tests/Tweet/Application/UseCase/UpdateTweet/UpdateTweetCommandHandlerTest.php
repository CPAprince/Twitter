<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Application\UseCase\UpdateTweet;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommand;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommandHandler;
use Twitter\Tweet\Domain\Tweet\Exception\TweetAccessDeniedException;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

#[Group('unit')]
#[CoversMethod(UpdateTweetCommandHandler::class, 'handle')]
final class UpdateTweetCommandHandlerTest extends TestCase
{
    /**
     * @throws TweetAccessDeniedException
     * @throws TweetNotFoundException
     */
    #[Test]
    public function updatesTweetWhenUserIsOwner(): void
    {
        $tweet = Tweet::create(
            '123e4567-e89b-12d3-a456-426614174000',
            'Old content',
        );

        $tweetId = $tweet->id();

        $repository = $this->createMock(TweetRepository::class);
        $repository
            ->expects(self::once())
            ->method('getById')
            ->with($tweetId)
            ->willReturn($tweet);

        $repository
            ->expects(self::once())
            ->method('flush');

        $handler = new UpdateTweetCommandHandler($repository);

        $command = new UpdateTweetCommand(
            '123e4567-e89b-12d3-a456-426614174000',
            $tweetId,
            'New content',
        );

        $result = $handler->handle($command);

        self::assertSame('New content', $result->content);
        self::assertSame('New content', $tweet->content());
        self::assertInstanceOf(DateTimeImmutable::class, $result->updatedAt);
    }

    /**
     * @throws TweetNotFoundException
     */
    #[Test]
    public function throwsAccessDeniedWhenUserIsNotOwner(): void
    {
        $tweet = Tweet::create(
            '123e4567-e89b-12d3-a456-426614174000',
            'Content',
        );

        $repository = $this->createMock(TweetRepository::class);
        $repository
            ->expects(self::once())
            ->method('getById')
            ->willReturn($tweet);

        $repository
            ->expects(self::never())
            ->method('flush');

        $handler = new UpdateTweetCommandHandler($repository);

        $command = new UpdateTweetCommand(
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            $tweet->id(),
            'New content',
        );

        $this->expectException(TweetAccessDeniedException::class);

        $handler->handle($command);
    }

    /**
     * @throws TweetAccessDeniedException
     */
    #[Test]
    public function propagatesTweetNotFoundException(): void
    {
        $repository = $this->createMock(TweetRepository::class);
        $repository
            ->expects(self::once())
            ->method('getById')
            ->willThrowException(
                new TweetNotFoundException('non-existent-id')
            );

        $repository
            ->expects(self::never())
            ->method('flush');

        $handler = new UpdateTweetCommandHandler($repository);

        $command = new UpdateTweetCommand(
            '123e4567-e89b-12d3-a456-426614174000',
            'non-existent-id',
            'Content',
        );

        $this->expectException(TweetNotFoundException::class);

        $handler->handle($command);
    }
}
