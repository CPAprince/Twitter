<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Application\UseCase\GetTweet;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Profile\Domain\Profile\Model\Profile;
use Twitter\Profile\Domain\Profile\Model\ProfileRepository;
use Twitter\Tweet\Application\UseCase\GetTweet\GetTweetCommand;
use Twitter\Tweet\Application\UseCase\GetTweet\GetTweetCommandHandler;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

#[Group('unit')]
#[CoversClass(GetTweetCommandHandler::class)]
final class GetTweetCommandHandlerTest extends TestCase
{
    private TweetRepository&MockObject $tweetRepository;
    private ProfileRepository&MockObject $profileRepository;
    private GetTweetCommandHandler $handler;

    protected function setUp(): void
    {
        $this->tweetRepository = $this->createMock(TweetRepository::class);
        $this->profileRepository = $this->createMock(ProfileRepository::class);

        $this->handler = new GetTweetCommandHandler(
            $this->tweetRepository,
            $this->profileRepository,
        );
    }

    #[Test]
    public function itReturnsTweetWhenExists(): void
    {
        $authorId = '22222222-2222-2222-2222-222222222222';

        $tweet = Tweet::create($authorId, 'Hello from unit test!');
        $tweetId = $tweet->id();
        $profile = Profile::create($authorId, 'Test User', 'Bio');

        $this->tweetRepository
            ->expects(self::once())
            ->method('getById')
            ->with($tweetId)
            ->willReturn($tweet);

        $this->profileRepository
            ->expects(self::once())
            ->method('getByUserId')
            ->with($authorId)
            ->willReturn($profile);

        $result = $this->handler->handle(new GetTweetCommand($tweetId));

        self::assertSame($tweet->id(), $result->id);
        self::assertSame('Hello from unit test!', $result->content);
        self::assertSame($authorId, $result->authorId);
        self::assertSame('Test User', $result->authorName);

        self::assertInstanceOf(DateTimeImmutable::class, $result->createdAt);
        self::assertInstanceOf(DateTimeImmutable::class, $result->updatedAt);

        self::assertNotEmpty($result->createdAt->format(DATE_RFC3339));
        self::assertNotEmpty($result->updatedAt->format(DATE_RFC3339));
    }

    #[Test]
    public function itThrowsWhenTweetDoesNotExist(): void
    {
        $tweetId = '11111111-1111-1111-1111-111111111111';

        $this->tweetRepository
            ->expects(self::once())
            ->method('getById')
            ->with($tweetId)
            ->willThrowException(new TweetNotFoundException($tweetId));

        $this->profileRepository
            ->expects(self::never())
            ->method('getByUserId');

        $this->expectException(TweetNotFoundException::class);

        $this->handler->handle(new GetTweetCommand($tweetId));
    }
}
