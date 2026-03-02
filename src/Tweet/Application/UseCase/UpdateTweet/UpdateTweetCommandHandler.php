<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\UpdateTweet;

use Override;
use Twitter\Tweet\Domain\Tweet\Exception\TweetAccessDeniedException;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class UpdateTweetCommandHandler implements UpdateTweetCommandHandlerInterface
{
    public function __construct(private TweetRepository $tweetRepository) {}

    /**
     * @throws TweetAccessDeniedException
     * @throws TweetNotFoundException
     */
    #[Override]
    public function handle(UpdateTweetCommand $command): UpdateTweetCommandResult
    {
        $tweet = $this->tweetRepository->getById($command->tweetId);

        if ($command->userId !== $tweet->userId()) {
            throw new TweetAccessDeniedException($command->userId);
        }

        $tweet->updateContent($command->content);
        $this->tweetRepository->flush();

        return new UpdateTweetCommandResult($tweet->content(), $tweet->updatedAt());
    }
}
