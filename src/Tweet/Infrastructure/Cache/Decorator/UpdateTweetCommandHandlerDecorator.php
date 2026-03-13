<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Cache\Decorator;

use Override;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommand;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommandHandlerInterface;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommandResult;
use Twitter\Tweet\Domain\Tweet\Exception\TweetAccessDeniedException;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;

final readonly class UpdateTweetCommandHandlerDecorator implements UpdateTweetCommandHandlerInterface
{
    public function __construct(
        private UpdateTweetCommandHandlerInterface $inner,
        private TagAwareCacheInterface $cacheTweets,
    ) {}

    /**
     * @throws TweetAccessDeniedException
     * @throws TweetNotFoundException
     */
    #[Override]
    public function handle(UpdateTweetCommand $command): UpdateTweetCommandResult
    {
        $result = $this->inner->handle($command);

        $this->cacheTweets->invalidateTags([
            sprintf(
                'user_tweets_%s',
                $command->userId,
            ),
        ]);

        return $result;
    }
}
