<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\REST\CreateTweet;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommand;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommandHandler;

#[Route('/api/tweets', name: 'api_create_tweet', methods: [Request::METHOD_POST])]
final readonly class CreateTweetController
{
    public function __construct(
        private Security $security,
        private CreateTweetCommandHandler $handler,
    ) {}

    /**
     * @throws AssertionFailedException
     */
    public function __invoke(#[MapRequestPayload] CreateTweetRequest $request): JsonResponse
    {
        $authUser = $this->security->getUser();
        Assertion::same($request->userId(), $authUser->getUserIdentifier());

        $command = new CreateTweetCommand($request->userId(), $request->content());
        $result = $this->handler->handle($command);

        return new JsonResponse([
            'tweetId' => $result->tweetId,
            'createdAt' => $result->createdAt->format(DATE_RFC3339),
            'updatedAt' => $result->updatedAt->format(DATE_RFC3339),
        ], Response::HTTP_CREATED);
    }
}
