<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Application\UseCase\GetUserTweets;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQuery;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQueryHandler;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

#[Group('unit')]
#[CoversMethod(GetUserTweetsQueryHandler::class, 'handle')]
final class GetUserTweetsQueryHandlerTest extends TestCase
{
    private GetUserTweetsQueryHandler $handler;
    private MockObject|TweetRepository $tweetRepository;

    protected function setUp(): void
    {
        $this->tweetRepository = $this->createMock(TweetRepository::class);
        $this->handler = new GetUserTweetsQueryHandler($this->tweetRepository);
    }

    #[Test]
    public function returnsEmptyListWhenNoTweetsExist(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';

        $this->tweetRepository
            ->expects(self::once())
            ->method('getAllTweets')
            ->willReturn([]);

        $query = new GetUserTweetsQuery($userId, limit: 10, page: 1);
        $result = $this->handler->handle($query);

        self::assertSame([], $result->tweets);
    }

    /* NOT VALID Test, because no data pushes to DB where UserId filtration in MySQL */
    /*  #[Test]
      public function filtersTweetsByUserId(): void
      {
          $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
          $otherUserId = '019b5f41-0e5b-7f65-8b7a-0f9c0b3b3c11';

          $userTweet1 = Tweet::create($userId, 'First tweet');
          $userTweet2 = Tweet::create($userId, 'Second tweet');
          $otherUserTweet = Tweet::create($otherUserId, 'Other user tweet');

          $this->tweetRepository
              ->expects(self::once())
              ->method('getAllTweets')
              ->willReturn([$userTweet1, $otherUserTweet, $userTweet2]);

          $query = new GetUserTweetsQuery($userId, limit: 10, page: 1);
          $result = $this->handler->handle($query);

          self::assertCount(2, $result->tweets);
          self::assertSame($userTweet1->id(), $result->tweets[0]->id());
          self::assertSame($userTweet2->id(), $result->tweets[1]->id());
      }*/

    /* NOT VALID Test, because no data pushes to DB where UserId filtration in MySQL */
    /*  #[Test]
      public function returnsEmptyListWhenNoTweetsMatchUserId(): void
      {
          $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
          $otherUserId = '019b5f41-0e5b-7f65-8b7a-0f9c0b3b3c11';

          $otherUserTweet1 = Tweet::create($otherUserId, 'Other user tweet 1');
          $otherUserTweet2 = Tweet::create($otherUserId, 'Other user tweet 2');

          $this->tweetRepository
              ->expects(self::once())
              ->method('getAllTweets')
              ->willReturn([$otherUserTweet1, $otherUserTweet2]);

          $query = new GetUserTweetsQuery($userId, limit: 10, page: 0);
          $result = $this->handler->handle($query);

          self::assertSame([], $result->tweets);
      }*/

    /* NOT VALID Test, because no data pushes to DB where UserId filtration in MySQL */
    /* #[Test]
     public function appliesPaginationCorrectly(): void
     {
         $userId = '019b5f3f-d110-7908-9177-5df439942a8b';

         $tweets = [];
         for ($i = 0; $i < 5; ++$i) {
             $tweets[] = Tweet::create($userId, "Tweet {$i}");
         }

         $this->tweetRepository
             ->expects(self::once())
             ->method('getAllTweets')
             ->willReturn($tweets);

         $query = new GetUserTweetsQuery($userId, limit: 2, page: 1);
         $result = $this->handler->handle($query);

         self::assertCount(2, $result->tweets);
         self::assertSame($tweets[2]->id(), $result->tweets[0]->id());
         self::assertSame($tweets[3]->id(), $result->tweets[1]->id());
     }*/

    /* NOT VALID Test, because no data pushes to DB where UserId filtration in MySQL */
    /* #[Test]
     public function returnsEmptyArrayWhenPaginationExceedsAvailableTweets(): void
     {
         $userId = '019b5f3f-d110-7908-9177-5df439942a8b';

         $tweet1 = Tweet::create($userId, 'Tweet 1');
         $tweet2 = Tweet::create($userId, 'Tweet 2');

         $this->tweetRepository
             ->expects(self::once())
             ->method('getAllTweets')
             ->willReturn([$tweet1, $tweet2]);

         // Page 1 with limit 2 means chunkLength = 2, requesting tweets starting at index 2
         // But we only have 2 tweets (indices 0 and 1), so result should be empty
         $query = new GetUserTweetsQuery($userId, limit: 2, page: 1);
         $result = $this->handler->handle($query);

         // Should return empty array since we're requesting beyond available tweets
         self::assertCount(0, $result->tweets);
     }*/

    #[Test]
    public function returnsAllTweetsWhenLimitIsZero(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';

        $tweet1 = Tweet::create($userId, 'Tweet 1');
        $tweet2 = Tweet::create($userId, 'Tweet 2');
        $tweet3 = Tweet::create($userId, 'Tweet 3');

        $this->tweetRepository
            ->expects(self::once())
            ->method('getAllTweets')
            ->willReturn([$tweet1, $tweet2, $tweet3]);

        $query = new GetUserTweetsQuery($userId, limit: 0, page: 0);
        $result = $this->handler->handle($query);

        self::assertCount(3, $result->tweets);
    }

    #[Test]
    public function returnsAllTweetsWhenPageIsNegative(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';

        $tweet1 = Tweet::create($userId, 'Tweet 1');
        $tweet2 = Tweet::create($userId, 'Tweet 2');

        $this->tweetRepository
            ->expects(self::once())
            ->method('getAllTweets')
            ->willReturn([$tweet1, $tweet2]);

        $query = new GetUserTweetsQuery($userId, limit: 10, page: -1);
        $result = $this->handler->handle($query);

        self::assertCount(2, $result->tweets);
    }
}
