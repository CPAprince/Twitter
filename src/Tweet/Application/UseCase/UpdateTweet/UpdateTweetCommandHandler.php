<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\UpdateTweet;

use Assert\Assert;
use Assert\Assertion;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class UpdateTweetCommandHandler
{
    public function __construct(private TweetRepository $tweetRepository) {}

    public function handle(UpdateTweetCommand $command): UpdateTweetCommandResult
    {
        $tweet = $this->tweetRepository->getById($command->tweetId);

        Assert::that($command->userId)->same($tweet->userId());

        $tweet->updateContent($command->content);
        $this->tweetRepository->save($tweet);

        return new UpdateTweetCommandResult($tweet->content(), $tweet->updatedAt());
    }
}
