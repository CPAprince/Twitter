<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Cache\Decorator;

use Override;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommand;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommandHandlerInterface;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommandResult;

final readonly class CreateTweetCommandHandlerDecorator implements CreateTweetCommandHandlerInterface
{
    public function __construct(
        private CreateTweetCommandHandlerInterface $inner,
        private TagAwareCacheInterface $cacheTweets,
    ) {}

    #[Override]
    public function handle(CreateTweetCommand $command): CreateTweetCommandResult
    {
        $result = $this->inner->handle($command);

        $this->cacheTweets->invalidateTags([
            sprintf('user_tweets_%s',
                $command->userId,
            ),
        ]);

        return $result;
    }
}
