<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\REST\GetTweet;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\Tweet\Application\UseCase\GetTweet\GetTweetCommand;
use Twitter\Tweet\Application\UseCase\GetTweet\GetTweetCommandHandler;

#[Route('/api/tweets/{tweetId}', name: 'tweets_get', methods: ['GET'])]
final readonly class GetTweetController
{
    public function __construct(private GetTweetCommandHandler $handler) {}

    public function __invoke(string $tweetId): JsonResponse
    {
        $dto = $this->handler->handle(new GetTweetCommand($tweetId));

        return new JsonResponse([
            'id' => $dto->id,
            'content' => $dto->content,
            'createdAt' => $dto->createdAt->format(DATE_RFC3339),
            'updatedAt' => $dto->updatedAt->format(DATE_RFC3339),
            'author' => [
                'id' => $dto->authorId,
                'name' => $dto->authorName,
            ],
        ], Response::HTTP_OK);
    }
}
