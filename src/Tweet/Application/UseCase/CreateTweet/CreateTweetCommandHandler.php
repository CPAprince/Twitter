<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\CreateTweet;

use Override;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class CreateTweetCommandHandler implements CreateTweetCommandHandlerInterface
{
    public function __construct(private TweetRepository $tweetRepository) {}

    #[Override]
    public function handle(CreateTweetCommand $command): CreateTweetCommandResult
    {
        $tweet = Tweet::create($command->userId, $command->content);
        $this->tweetRepository->add($tweet);

        return new CreateTweetCommandResult(
            $tweet->id(),
            $tweet->createdAt(),
            $tweet->updatedAt(),
        );
    }
}
