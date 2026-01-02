<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\REST\CreateTweet;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommand;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommandHandler;

#[Route('/api/tweets', name: 'api_create_tweet', methods: ['POST'])]
final readonly class CreateTweetController
{
    public function __construct(private CreateTweetCommandHandler $handler) {}

    public function __invoke(#[MapRequestPayload] CreateTweetRequest $request): JsonResponse
    {
        $command = new CreateTweetCommand($request->userId(), $request->content());
        $result = $this->handler->handle($command);

        return new JsonResponse(['tweetId' => $result->tweetId], Response::HTTP_CREATED);
    }
}
