<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\REST\CreateTweet;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;

#[Route('/api/tweets', name: 'api_create_tweet', methods: ['POST'])]
final readonly class CreateTweetController
{
    public function __invoke(#[MapRequestPayload] CreateTweetRequest $request): JsonResponse
    {
        return new JsonResponse([
            'tweetId' => Tweet::create($request->userId(), $request->content())->id(),
        ], Response::HTTP_CREATED);
    }
}
