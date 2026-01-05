<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\REST\UpdateTweet;

use Assert\Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommand;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommandHandler;

#[Route('/api/tweets/{tweetId}', name: 'api_update_tweet', methods: [Request::METHOD_PATCH])]
final readonly class UpdateTweetController
{
    public function __construct(private UpdateTweetCommandHandler $commandHandler) {}

    public function __invoke(
        string $tweetId,
        #[MapRequestPayload] UpdateTweetRequest $request,
    ): Response {
        Assert::that($tweetId)->uuid();

        $command = new UpdateTweetCommand($tweetId, $request->content());
        $result = $this->commandHandler->handle($command);

        return new JsonResponse([
            'content' => $result->content,
            'updatedAt' => $result->updatedAt->format(DATE_RFC3339),
        ], Response::HTTP_OK);
    }
}
