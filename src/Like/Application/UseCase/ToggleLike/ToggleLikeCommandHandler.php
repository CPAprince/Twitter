<?php

declare(strict_types=1);

namespace Twitter\Like\Application\UseCase\ToggleLike;

use Twitter\Like\Domain\Like\Exception\LikeAlreadyExistsException;
use Twitter\Like\Domain\Like\Model\Like;
use Twitter\Like\Domain\Like\Model\LikeRepository;

final readonly class ToggleLikeCommandHandler
{
    public function __construct(
        private LikeRepository $likeRepository,
    ) {}

    /**
     * @throws LikeAlreadyExistsException
     */
    public function handle(ToggleLikeCommand $command): ToggleLikeCommandResult
    {
        $existingLike = $this->likeRepository->findOneByTweetAndUser(
            $command->tweetId,
            $command->userId,
        );

        if (!is_null($existingLike)) {
            $this->likeRepository->remove($existingLike);

            return new ToggleLikeCommandResult(false);
        }

        $newLike = Like::create($command->tweetId, $command->userId);
        $this->likeRepository->add($newLike);

        return new ToggleLikeCommandResult(true);
    }
}
